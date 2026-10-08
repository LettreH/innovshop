<?php

namespace App\Tests\Entity;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires du regroupement des lignes de commande (quantités).
 */
class CommandeTest extends TestCase
{
    public function testLignesIdentiquesSontRegroupees(): void
    {
        $commande = new Commande();
        $commande->addLigne($this->creerLigne('Veste FloatX', 'Taille : M', '3200.00'));
        $commande->addLigne($this->creerLigne('Veste FloatX', 'Taille : M', '3200.00'));
        $commande->addLigne($this->creerLigne('BabelPin', null, '449.00'));

        $groupes = $commande->getLignesGroupees();

        $this->assertCount(2, $groupes);
        $this->assertSame('Veste FloatX', $groupes[0]['nom']);
        $this->assertSame(2, $groupes[0]['quantite']);
        $this->assertSame(3200.0, $groupes[0]['prix']);
        $this->assertSame(1, $groupes[1]['quantite']);
    }

    public function testOptionsDifferentesNeSontPasRegroupees(): void
    {
        $commande = new Commande();
        $commande->addLigne($this->creerLigne('Veste FloatX', 'Taille : M', '3200.00'));
        $commande->addLigne($this->creerLigne('Veste FloatX', 'Taille : L', '3200.00'));

        $groupes = $commande->getLignesGroupees();

        $this->assertCount(2, $groupes);
        $this->assertSame(1, $groupes[0]['quantite']);
        $this->assertSame(1, $groupes[1]['quantite']);
    }

    public function testCommandeVideNaAucuneLigne(): void
    {
        $this->assertSame([], (new Commande())->getLignesGroupees());
    }

    private function creerLigne(string $nom, ?string $option, string $prix): LigneCommande
    {
        return (new LigneCommande())
            ->setNomProduit($nom)
            ->setOptionChoisie($option)
            ->setPrixUnitaire($prix);
    }
}