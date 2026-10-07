<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Service\PanierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/panier')]
final class PanierController extends AbstractController
{
    // Afficher le panier
    #[Route('', name: 'app_panier')]
    public function index(PanierService $panier): Response
    {
        return $this->render('panier/index.html.twig', [
            'lignes' => $panier->getLignes(),
            'total'  => $panier->getTotal(),
        ]);
    }

    // Ajouter un produit (avec ses options éventuelles)
    #[Route('/ajouter/{id}', name: 'app_panier_ajouter', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function ajouter(Produit $produit, Request $request, PanierService $panier): Response
    {
        if (!$this->isCsrfTokenValid('panier', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
        }

        // Les options arrivent sous la forme : ['Taille' => 'M', 'Couleur' => 'Noir']
        $choix = [];
        foreach ($request->request->all('options') as $nom => $valeur) {
            $choix[] = $nom . ' : ' . $valeur;
        }
        $option = $choix ? mb_substr(implode(', ', $choix), 0, 150) : null;

        $panier->ajouter($produit->getId(), $option);

        return $this->repondre($request, $panier, $produit->getNom() . ' a été ajouté au panier.');
    }

    // Supprimer une ligne du panier
    #[Route('/supprimer/{cle}', name: 'app_panier_supprimer', methods: ['POST'])]
    public function supprimer(string $cle, Request $request, PanierService $panier): Response
    {
        if (!$this->isCsrfTokenValid('panier', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
        }

        $panier->supprimer($cle);

        return $this->repondre($request, $panier, 'Article retiré du panier.');
    }

    /**
     * Répond en JSON si la demande vient du JavaScript (AJAX),
     * sinon redirige normalement vers la page panier.
     */
    private function repondre(Request $request, PanierService $panier, string $message): Response
    {
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'message' => $message,
                'nombre'  => $panier->getNombre(),
                'total'   => number_format($panier->getTotal(), 2, ',', ' '),
            ]);
        }

        $this->addFlash('success', $message);

        return $this->redirectToRoute('app_panier');
    }
}