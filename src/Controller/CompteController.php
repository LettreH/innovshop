<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\ChangerMotDePasseType;
use App\Form\ProfilType;
use App\Repository\CommandeRepository;
use App\Security\Voter\CommandeVoter;
use App\Service\FactureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/compte')]
#[IsGranted('ROLE_USER')]
final class CompteController extends AbstractController
{
    // Tableau de bord de l'espace client
    #[Route('', name: 'app_compte')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        return $this->render('compte/index.html.twig', [
            'commandes' => $this->mesCommandes($commandeRepository),
        ]);
    }

    // Historique complet des commandes
    #[Route('/commandes', name: 'app_compte_commandes')]
    public function commandes(CommandeRepository $commandeRepository): Response
    {
        return $this->render('compte/commandes.html.twig', [
            'commandes' => $this->mesCommandes($commandeRepository),
        ]);
    }

    // Détail d'une commande (le Voter vérifie que c'est bien la sienne)
    #[Route('/commande/{numero}', name: 'app_compte_commande')]
    #[IsGranted(CommandeVoter::VOIR, subject: 'commande')]
    public function commande(
        #[MapEntity(mapping: ['numero' => 'numero'])] Commande $commande
    ): Response {
        return $this->render('compte/commande.html.twig', [
            'commande' => $commande,
        ]);
    }

    // Téléchargement de la facture PDF (même règle que le détail)
    #[Route('/commande/{numero}/facture', name: 'app_compte_facture')]
    #[IsGranted(CommandeVoter::VOIR, subject: 'commande')]
    public function facture(
        #[MapEntity(mapping: ['numero' => 'numero'])] Commande $commande,
        FactureService $factureService
    ): Response {
        if ($commande->getStatut() === 'annulee') {
            $this->addFlash('warning', 'Aucune facture pour une commande annulée.');
            return $this->redirectToRoute('app_compte_commande', ['numero' => $commande->getNumero()]);
        }

        $pdf = $factureService->genererPdf($commande);

        return new Response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="facture-' . $commande->getNumero() . '.pdf"',
        ]);
    }

    // Modifier son profil (prénom, nom, téléphone)
    #[Route('/profil', name: 'app_compte_profil')]
    public function profil(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProfilType::class, $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Vos informations ont été mises à jour.');

            return $this->redirectToRoute('app_compte_profil');
        }

        return $this->render('compte/profil.html.twig', [
            'form' => $form,
        ]);
    }

    // Changer son mot de passe
    #[Route('/mot-de-passe', name: 'app_compte_mot_de_passe')]
    public function motDePasse(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        $form = $this->createForm(ChangerMotDePasseType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur = $this->getUser();
            $nouveau = $form->get('nouveauMotDePasse')->getData();

            // On ne stocke JAMAIS le mot de passe en clair : on le hache
            $utilisateur->setPassword($hasher->hashPassword($utilisateur, $nouveau));
            $em->flush();

            $this->addFlash('success', 'Votre mot de passe a été modifié.');
            return $this->redirectToRoute('app_compte');
        }

        return $this->render('compte/mot_de_passe.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Les commandes du client connecté, de la plus récente à la plus ancienne.
     */
    private function mesCommandes(CommandeRepository $commandeRepository): array
    {
        return $commandeRepository->findBy(
            ['utilisateur' => $this->getUser()],
            ['dateCommande' => 'DESC']
        );
    }
}
