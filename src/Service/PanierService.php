<?php

namespace App\Service;

use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Gère le panier stocké en session.
 * Pas de quantité : chaque ajout crée une nouvelle ligne.
 */
class PanierService
{
    public function __construct(
        private RequestStack $requestStack,
        private ProduitRepository $produitRepository,
    ) {
    }

    // Ajoute une ligne au panier
    public function ajouter(int $produitId, ?string $option = null): void
    {
        $panier = $this->getSession()->get('panier', []);
        $panier[uniqid()] = ['produitId' => $produitId, 'option' => $option];
        $this->getSession()->set('panier', $panier);
    }

    // Supprime une ligne grâce à sa clé
    public function supprimer(string $cle): void
    {
        $panier = $this->getSession()->get('panier', []);
        unset($panier[$cle]);
        $this->getSession()->set('panier', $panier);
    }

    // Vide tout le panier (après une commande)
    public function vider(): void
    {
        $this->getSession()->remove('panier');
    }

    // Renvoie les lignes avec les vrais objets Produit
    public function getLignes(): array
    {
        $lignes = [];
        foreach ($this->getSession()->get('panier', []) as $cle => $ligne) {
            $produit = $this->produitRepository->find($ligne['produitId']);
            if ($produit) {
                $lignes[] = [
                    'cle'     => $cle,
                    'produit' => $produit,
                    'option'  => $ligne['option'],
                ];
            }
        }
        return $lignes;
    }

    // Additionne le prix de chaque ligne
    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->getLignes() as $ligne) {
            $total += (float) $ligne['produit']->getPrix();
        }
        return $total;
    }

    // Nombre de lignes (pour le compteur 🛒 du menu)
    public function getNombre(): int
    {
        return count($this->getSession()->get('panier', []));
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }
}
