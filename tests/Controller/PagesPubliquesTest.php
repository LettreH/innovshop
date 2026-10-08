<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels des pages accessibles à tous les visiteurs.
 */
class PagesPubliquesTest extends WebTestCase
{
    public function testAccueilSAffiche(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Bienvenue chez InnovShop');
    }

    public function testCatalogueAfficheLesProduits(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/catalogue');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Notre catalogue');
        $this->assertGreaterThanOrEqual(8, $crawler->filter('.card')->count());
    }

    public function testRechercheFiltreLesProduits(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/catalogue?q=BabelPin');

        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('.card')->count());
    }

    public function testProduitInexistantRenvoie404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/produit/999999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testAjoutAuPanierRefuseEnGet(): void
    {
        $client = static::createClient();
        $client->request('GET', '/panier/ajouter/1');

        // 405 = méthode non autorisée : l'ajout au panier exige un POST
        $this->assertResponseStatusCodeSame(405);
    }
}