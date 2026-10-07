<?php

namespace App\DataFixtures;

use App\Entity\Adresse;
use App\Entity\Categorie;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\OptionProduit;
use App\Entity\Produit;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    // Symfony nous donne l'outil qui hache les mots de passe
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // =========================================================
        // 1) LES CATEGORIES
        // =========================================================
        $categories = [];

        $listeCategories = [
            'Voyage temporel'  => 'Explorez les époques passées.',
            'Mobilité'         => 'Déplacez-vous autrement.',
            'Communication'    => 'Comprenez et soyez compris partout.',
            'Domotique'        => 'Une maison qui travaille pour vous.',
            'Énergie'          => 'Une énergie propre et inépuisable.',
            'Vision augmentée' => 'Voyez plus loin que vos yeux.',
        ];

        foreach ($listeCategories as $nom => $description) {
            $categorie = new Categorie();
            $categorie->setNom($nom);
            $categorie->setDescription($description);
            $manager->persist($categorie);

            $categories[$nom] = $categorie;
        }

        // =========================================================
        // 2) LES PRODUITS
        // [nom, categorie, prix, image, a la une, ajoute il y a X jours, description, options]
        // =========================================================
        $listeProduits = [
            ['Chrono-Cabine T1', 'Voyage temporel', '89999.00', 'chrono-cabine-t1.png', true, 30,
             'Cabine individuelle permettant un aller-retour vers l\'époque de votre choix. Retour garanti avant le dîner.',
             [['Époque', 'Antiquité'], ['Époque', 'Moyen Âge'], ['Époque', 'Renaissance']]],

            ['Téléporteur de poche Zip', 'Mobilité', '12499.00', 'teleporteur-zip.png', true, 25,
             'Déplacement instantané d\'un point à un autre. Tient dans une poche de veste.',
             [['Portée', '10 km'], ['Portée', '100 km'], ['Portée', 'Illimitée']]],

            ['Veste anti-gravité FloatX', 'Mobilité', '3200.00', 'veste-floatx.png', true, 20,
             'Veste légère permettant de flotter jusqu\'à trois mètres du sol.',
             [['Taille', 'S'], ['Taille', 'M'], ['Taille', 'L'], ['Couleur', 'Gris'], ['Couleur', 'Noir']]],

            ['Traducteur universel BabelPin', 'Communication', '449.00', 'traducteur-babelpin.png', false, 15,
             'Broche discrète qui traduit en direct plus de 6 000 langues.',
             [['Couleur', 'Argent'], ['Couleur', 'Doré']]],

            ['Batterie à fusion PowerStar', 'Énergie', '799.00', 'batterie-powerstar.png', false, 10,
             'Une seule charge suffit pour dix ans d\'utilisation.',
             [['Couleur', 'Noir'], ['Couleur', 'Blanc']]],

            ['Hologramme domestique Aura', 'Domotique', '1890.00', 'hologramme-aura.png', false, 3,
             'Projette un assistant holographique dans votre salon.',
             [['Format', 'Compact'], ['Format', 'Grand format']]],

            ['Drone majordome Jarvis Mini', 'Domotique', '2490.00', 'drone-jarvis-mini.png', false, 2,
             'Petit drone autonome qui range, sert et surveille la maison.',
             [['Couleur', 'Blanc'], ['Couleur', 'Anthracite']]],

            ['Lunettes RA VisionOne', 'Vision augmentée', '999.00', 'lunettes-visionone.png', false, 1,
             'Affiche informations et itinéraires directement dans votre champ de vision.',
             [['Monture', 'Noir'], ['Monture', 'Écaille']]],
        ];

        $produits = [];

        foreach ($listeProduits as [$nom, $nomCategorie, $prix, $image, $alaUne, $jours, $description, $options]) {
            $produit = new Produit();
            $produit->setNom($nom);
            $produit->setCategorie($categories[$nomCategorie]);
            $produit->setPrix($prix);
            $produit->setImage($image);
            $produit->setAlaUne($alaUne);
            $produit->setDateAjout(new \DateTimeImmutable('-' . $jours . ' days'));
            $produit->setDescription($description);
            $manager->persist($produit);

            foreach ($options as [$nomOption, $valeur]) {
                $option = new OptionProduit();
                $option->setNom($nomOption);
                $option->setValeur($valeur);
                $option->setProduit($produit);
                $manager->persist($option);
            }

            $produits[$nom] = $produit;
        }

        // =========================================================
        // 3) LES UTILISATEURS (mots de passe haches !)
        // =========================================================
        $admin = new Utilisateur();
        $admin->setEmail('admin@innovshop.fr');
        $admin->setPrenom('Sophie');
        $admin->setNom('Leroy');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'Admin2026'));
        $manager->persist($admin);

        $client = new Utilisateur();
        $client->setEmail('client@innovshop.fr');
        $client->setPrenom('Julie');
        $client->setNom('Martin');
        $client->setRoles([]);
        $client->setPassword($this->hasher->hashPassword($client, 'Client2026'));
        $manager->persist($client);

        // =========================================================
        // 4) UNE ADRESSE ET UNE COMMANDE DE TEST
        // =========================================================
        $adresse = new Adresse();
        $adresse->setRue('12 rue du Futur');
        $adresse->setCodePostal('68100');
        $adresse->setVille('Mulhouse');
        $adresse->setPays('France');
        $adresse->setUtilisateur($client);
        $manager->persist($adresse);

        $commande = new Commande();
        $commande->setNumero('CMD-2026-0001');
        $commande->setDateCommande(new \DateTimeImmutable('-5 days'));
        $commande->setStatut('expediee');
        $commande->setMontantTotal('3649.00');
        $commande->setUtilisateur($client);
        $commande->setAdresse($adresse);
        $manager->persist($commande);

        // La "photo" des produits achetes (nom + prix recopies)
        $achats = [
            ['Veste anti-gravité FloatX', 'Taille : M'],
            ['Traducteur universel BabelPin', 'Couleur : Doré'],
        ];

        foreach ($achats as [$nomProduit, $optionChoisie]) {
            $produit = $produits[$nomProduit];

            $ligne = new LigneCommande();
            $ligne->setCommande($commande);
            $ligne->setProduit($produit);
            $ligne->setNomProduit($produit->getNom());
            $ligne->setPrixUnitaire($produit->getPrix());
            $ligne->setOptionChoisie($optionChoisie);
            $manager->persist($ligne);
        }

        // =========================================================
        // 5) ON ENREGISTRE TOUT D'UN COUP
        // =========================================================
        $manager->flush();
    }
}