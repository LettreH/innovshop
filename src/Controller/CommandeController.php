<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Form\AdresseType;
use App\Repository\CommandeRepository;
use App\Service\PanierService;
use App\Security\Voter\AdresseVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/commande')]
#[IsGranted('ROLE_USER')]
final class CommandeController extends AbstractController
{
    // ===== ÉTAPE 1 : vérification du panier =====
    #[Route('/verification', name: 'app_commande_verification')]
    public function verification(PanierService $panier): Response
    {
        if ($panier->getNombre() === 0) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_panier');
        }

        return $this->render('commande/verification.html.twig', [
            'lignes' => $panier->getLignes(),
            'total'  => $panier->getTotal(),
        ]);
    }

    // ===== ÉTAPE 2 : adresse de livraison =====
    #[Route('/livraison', name: 'app_commande_livraison')]
    public function livraison(Request $request, PanierService $panier, EntityManagerInterface $em): Response
    {
        if ($panier->getNombre() === 0) {
            return $this->redirectToRoute('app_panier');
        }

        $adresse = new Adresse();
        $adresse->setPays('France');

        $form = $this->createForm(AdresseType::class, $adresse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adresse->setUtilisateur($this->getUser());
            $em->persist($adresse);
            $em->flush();

            return $this->redirectToRoute('app_commande_confirmation', ['id' => $adresse->getId()]);
        }

        return $this->render('commande/livraison.html.twig', [
            'form'     => $form,
            'adresses' => $this->getUser()->getAdresses(),
        ]);
    }

    // ===== ÉTAPE 3 : récapitulatif avant validation =====
    #[Route('/confirmation/{id}', name: 'app_commande_confirmation', requirements: ['id' => '\d+'])]
    #[IsGranted(AdresseVoter::GERER, subject: 'adresse')]
    public function confirmation(Adresse $adresse, PanierService $panier): Response
    {

        if ($panier->getNombre() === 0) {
            return $this->redirectToRoute('app_panier');
        }

        return $this->render('commande/confirmation.html.twig', [
            'lignes'  => $panier->getLignes(),
            'total'   => $panier->getTotal(),
            'adresse' => $adresse,
        ]);
    }

    // ===== VALIDATION : enregistrement en base + email =====
    #[Route('/valider/{id}', name: 'app_commande_valider', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted(AdresseVoter::GERER, subject: 'adresse')]
    public function valider(
        Adresse $adresse,
        Request $request,
        PanierService $panier,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {

        if (!$this->isCsrfTokenValid('valider-commande', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
        }

        $lignes = $panier->getLignes();
        if (!$lignes) {
            return $this->redirectToRoute('app_panier');
        }

        // 1. La commande
        $commande = new Commande();
        $commande->setNumero('CMD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))));
        $commande->setDateCommande(new \DateTimeImmutable());
        $commande->setStatut('en_attente');
        $commande->setMontantTotal(number_format($panier->getTotal(), 2, '.', ''));
        $commande->setUtilisateur($this->getUser());
        $commande->setAdresse($adresse);

        // 2. Une ligne de commande par article du panier
        foreach ($lignes as $ligne) {
            $produit = $ligne['produit'];

            $ligneCommande = new LigneCommande();
            $ligneCommande->setNomProduit($produit->getNom());
            $ligneCommande->setPrixUnitaire($produit->getPrix());
            $ligneCommande->setOptionChoisie($ligne['option']);
            $ligneCommande->setProduit($produit);

            $commande->addLigne($ligneCommande);
            $em->persist($ligneCommande);
        }

        // 3. Enregistrement en base, puis on vide le panier
        $em->persist($commande);
        $em->flush();
        $panier->vider();

        // 4. Email de confirmation
        $email = (new TemplatedEmail())
            ->from(new Address('commandes@innovshop.fr', 'InnovShop'))
            ->to($this->getUser()->getEmail())
            ->subject('Confirmation de votre commande ' . $commande->getNumero())
            ->htmlTemplate('emails/confirmation_commande.html.twig')
            ->context(['commande' => $commande]);

        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            // La commande est enregistrée même si l'email échoue
            $this->addFlash('warning', 'Votre commande est enregistrée, mais l\'email n\'a pas pu être envoyé.');
        }

        return $this->redirectToRoute('app_commande_merci', ['numero' => $commande->getNumero()]);
    }

    // ===== PAGE DE REMERCIEMENT =====
    #[Route('/merci/{numero}', name: 'app_commande_merci')]
    public function merci(string $numero, CommandeRepository $commandeRepository): Response
    {
        $commande = $commandeRepository->findOneBy([
            'numero'      => $numero,
            'utilisateur' => $this->getUser(),
        ]);

        if (!$commande) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        return $this->render('commande/merci.html.twig', [
            'commande' => $commande,
        ]);
    }
}
