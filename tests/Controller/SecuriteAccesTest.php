<?php

namespace App\Tests\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels des protections d'accès (anonyme, client, admin, Voter).
 */
class SecuriteAccesTest extends WebTestCase
{
    public function testEspaceClientRedirigeVersConnexionSiAnonyme(): void
    {
        $client = static::createClient();
        $client->request('GET', '/compte');

        $this->assertResponseRedirects('/login');
    }

    public function testClientAccedeASonEspace(): void
    {
        $client = static::createClient();
        $this->connecter($client, 'client@innovshop.fr');

        $client->request('GET', '/compte');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Bonjour');
    }

    public function testClientNePeutPasOuvrirLAdministration(): void
    {
        $client = static::createClient();
        $this->connecter($client, 'client@innovshop.fr');

        $client->request('GET', '/admin');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminPeutOuvrirLAdministration(): void
    {
        $client = static::createClient();
        $this->connecter($client, 'admin@innovshop.fr');

        $client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
    }

    public function testClientVoitSaPropreCommande(): void
    {
        $client = static::createClient();
        $this->connecter($client, 'client@innovshop.fr');

        $client->request('GET', '/compte/commande/CMD-2026-0001');

        $this->assertResponseIsSuccessful();
    }

    public function testVoterBloqueLaCommandeDUnAutreClient(): void
    {
        $client = static::createClient();

        // On crée un autre client, qui n'a passé aucune commande
        $em = static::getContainer()->get('doctrine')->getManager();
        $autre = (new Utilisateur())
            ->setEmail('autre' . uniqid() . '@test.fr')
            ->setPassword('inutile-pour-ce-test')
            ->setPrenom('Marc')
            ->setNom('Durand');
        $em->persist($autre);
        $em->flush();

        $client->loginUser($autre);
        $client->request('GET', '/compte/commande/CMD-2026-0001');

        // Le CommandeVoter doit refuser : ce n'est pas sa commande
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Connecte le robot avec un compte des fixtures.
     */
    private function connecter(KernelBrowser $client, string $email): void
    {
        $utilisateur = static::getContainer()
            ->get(UtilisateurRepository::class)
            ->findOneBy(['email' => $email]);

        $client->loginUser($utilisateur);
    }
}