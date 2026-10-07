<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProduitController extends AbstractController
{
    #[Route('/catalogue', name: 'app_catalogue')]
    public function index(
        Request $request,
        ProduitRepository $produitRepository,
        CategorieRepository $categorieRepository
    ): Response {
        // 1. On lit ce que le visiteur a tapé dans l'URL (?q=...&categorie=...)
        $motCle      = trim($request->query->getString('q'));
        $categorieId = (int) $request->query->get('categorie');
        // 2. On demande au Repository les produits correspondants
        $produits = $produitRepository->rechercher($motCle, $categorieId);

        // 3. On envoie tout au template
        return $this->render('produit/index.html.twig', [
            'produits'         => $produits,
            'categories'       => $categorieRepository->findBy([], ['nom' => 'ASC']),
            'motCle'           => $motCle,
            'categorieActive'  => $categorieId,
        ]);
    }

    #[Route('/produit/{id}', name: 'app_produit_show', requirements: ['id' => '\d+'])]
    public function show(int $id, ProduitRepository $produitRepository): Response
    {
        $produit = $produitRepository->find($id);

        if (!$produit) {
            throw $this->createNotFoundException('Ce produit n\'existe pas.');
        }

        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }
}