<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Form\AdresseType;
use App\Repository\CommandeRepository;
use App\Security\Voter\AdresseVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Gestion des adresses de livraison dans l'espace client.
 */
#[Route('/compte/adresses')]
#[IsGranted('ROLE_USER')]
final class AdresseController extends AbstractController
{
    // Liste des adresses du client
    #[Route('', name: 'app_compte_adresses')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        $adresses = $this->getUser()->getAdresses();

        // On repère les adresses déjà utilisées dans une commande
        $verrouillees = [];
        foreach ($adresses as $adresse) {
            if ($this->estUtilisee($adresse, $commandeRepository)) {
                $verrouillees[] = $adresse->getId();
            }
        }

        return $this->render('compte/adresses.html.twig', [
            'adresses'     => $adresses,
            'verrouillees' => $verrouillees,
        ]);
    }

    // Ajouter une adresse
    #[Route('/nouvelle', name: 'app_compte_adresse_nouvelle')]
    public function nouvelle(Request $request, EntityManagerInterface $em): Response
    {
        $adresse = new Adresse();
        $adresse->setPays('France');

        $form = $this->createForm(AdresseType::class, $adresse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adresse->setUtilisateur($this->getUser());
            $em->persist($adresse);
            $em->flush();

            $this->addFlash('success', 'Adresse ajoutée.');
            return $this->redirectToRoute('app_compte_adresses');
        }

        return $this->render('compte/adresse_form.html.twig', [
            'form'  => $form,
            'titre' => 'Nouvelle adresse',
        ]);
    }

    // Modifier une adresse (le Voter vérifie que c'est bien la sienne)
    #[Route('/{id}/modifier', name: 'app_compte_adresse_modifier', requirements: ['id' => '\d+'])]
    #[IsGranted(AdresseVoter::GERER, subject: 'adresse')]
    public function modifier(
        Adresse $adresse,
        Request $request,
        EntityManagerInterface $em,
        CommandeRepository $commandeRepository
    ): Response {
        if ($this->estUtilisee($adresse, $commandeRepository)) {
            $this->addFlash('warning', 'Cette adresse est liée à une commande : elle ne peut plus être modifiée. Ajoutez une nouvelle adresse.');
            return $this->redirectToRoute('app_compte_adresses');
        }

        $form = $this->createForm(AdresseType::class, $adresse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Adresse modifiée.');
            return $this->redirectToRoute('app_compte_adresses');
        }

        return $this->render('compte/adresse_form.html.twig', [
            'form'  => $form,
            'titre' => 'Modifier mon adresse',
        ]);
    }

    // Supprimer une adresse (le Voter vérifie que c'est bien la sienne)
    #[Route('/{id}/supprimer', name: 'app_compte_adresse_supprimer', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted(AdresseVoter::GERER, subject: 'adresse')]
    public function supprimer(
        Adresse $adresse,
        Request $request,
        EntityManagerInterface $em,
        CommandeRepository $commandeRepository
    ): Response {
        if (!$this->isCsrfTokenValid('supprimer-adresse-' . $adresse->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
        }

        if ($this->estUtilisee($adresse, $commandeRepository)) {
            $this->addFlash('warning', 'Cette adresse est liée à une commande : elle ne peut pas être supprimée.');
            return $this->redirectToRoute('app_compte_adresses');
        }

        $em->remove($adresse);
        $em->flush();

        $this->addFlash('success', 'Adresse supprimée.');
        return $this->redirectToRoute('app_compte_adresses');
    }

    /**
     * Vrai si au moins une commande utilise cette adresse.
     */
    private function estUtilisee(Adresse $adresse, CommandeRepository $commandeRepository): bool
    {
        return $commandeRepository->count(['adresse' => $adresse]) > 0;
    }
}
