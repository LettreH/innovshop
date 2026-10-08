<?php

namespace App\Tests\Entity;

use App\Entity\OptionProduit;
use App\Entity\Produit;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de l'entité Produit (sans base de données).
 */
class ProduitTest extends TestCase
{
    public function testValeursParDefautALaCreation(): void
    {
        $produit = new Produit();

        $this->assertFalse($produit->isAlaUne());
        $this->assertInstanceOf(\DateTimeImmutable::class, $produit->getDateAjout());
        $this->assertCount(0, $produit->getOptions());
    }

    public function testOptionsGroupeesParNom(): void
    {
        $produit = new Produit();
        $produit->addOption($this->creerOption('Taille', 'S'));
        $produit->addOption($this->creerOption('Taille', 'M'));
        $produit->addOption($this->creerOption('Couleur', 'Noir'));

        $this->assertSame(
            ['Taille' => ['S', 'M'], 'Couleur' => ['Noir']],
            $produit->getOptionsGroupees()
        );
    }

    public function testProduitSansOptionDonneUnTableauVide(): void
    {
        $produit = new Produit();

        $this->assertSame([], $produit->getOptionsGroupees());
    }

    private function creerOption(string $nom, string $valeur): OptionProduit
    {
        return (new OptionProduit())->setNom($nom)->setValeur($valeur);
    }
}