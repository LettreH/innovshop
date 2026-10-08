# 🚀 InnovShop — Le futur, livré chez vous

Boutique en ligne d'**objets futuristes** (voyage temporel, mobilité, domotique, énergie…) développée avec **Symfony 7.4** dans le cadre de la formation **Développeur Web et Web Mobile** (Global Digital University, Mulhouse).

> Projet réalisé pour une cliente fictive, **Madame Leroy**, qui souhaitait une plateforme e-commerce complète : catalogue, panier, commande en plusieurs étapes, espace client et back-office.

![Lighthouse](https://img.shields.io/badge/Lighthouse-100%20%2F%20100%20%2F%20100%20%2F%20100-brightgreen)
![Symfony](https://img.shields.io/badge/Symfony-7.4%20LTS-black)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4)
![Tests](https://img.shields.io/badge/PHPUnit-17%20tests-success)

---

## 🌍 Démo en ligne

**➡️ https://hermann.alwaysdata.net/innovshop/**

| Rôle | Email | Mot de passe |
|---|---|---|
| Client de démonstration | `client@innovshop.fr` | `Client2026` |
| Administrateur | *communiqué séparément au jury* | — |

> 🔒 Les identifiants administrateur ne sont volontairement **pas publiés** dans ce dépôt public : n'importe qui pourrait sinon modifier ou supprimer les produits du site en ligne.

> 📧 Les emails envoyés par le site en ligne sont interceptés par une boîte de test **Mailtrap** (les comptes de démonstration ont des adresses fictives).

---

## ✨ Fonctionnalités

### Côté visiteur / client
- **Accueil** : 3 produits « à la une » et les 3 dernières nouveautés
- **Catalogue** avec **recherche par mot-clé** et **filtre par catégorie**
- **Fiche produit** détaillée avec choix des **options** (taille, couleur…)
- **Panier** en session, accessible en permanence (compteur 🛒), mis à jour **en temps réel (AJAX)** — chaque ajout crée une nouvelle ligne
- **Inscription / connexion** sécurisées
- **Commande en 3 étapes** (réservée aux clients connectés) : vérification → adresse de livraison → confirmation
- **Email de confirmation** avec récapitulatif
- **Espace client** :
  - tableau de bord, historique et détail des commandes avec **suivi visuel du statut**
  - **facture PDF** téléchargeable
  - modification du profil (dont téléphone), des **adresses** et du **mot de passe**
- **Email automatique** à chaque changement de statut de commande

### Côté administrateur (EasyAdmin)
- Gestion des **catégories** et des **produits** (ajout, modification, suppression, **upload d'images**, mise « à la une »)
- Gestion des **commandes** et de leur **statut** (en attente → validée → expédiée → livrée / annulée)
- Gestion des **clients** (rôles, historique des commandes)

---

## 🛠️ Technologies

| Domaine | Outils |
|---|---|
| Back-end | **PHP 8.2+**, **Symfony 7.4 LTS** |
| Base de données | **MariaDB**, **Doctrine ORM** (entités, repositories, migrations) |
| Front-end | **Twig**, **Bootstrap 5.3** (responsive, mobile first), JavaScript (AJAX avec `fetch`) |
| Assets | **AssetMapper** + **MinifyBundle** (versionnement, cache, minification) |
| Back-office | **EasyAdmin 5** |
| Emails | **Symfony Mailer** (templates Twig) |
| PDF | **Dompdf** |
| Tests | **PHPUnit** (tests unitaires et fonctionnels) |
| Qualité | **PHP CS Fixer** (norme **PSR-12**), `lint:*`, `doctrine:schema:validate` |
| Hébergement | **Alwaysdata** (Apache, PHP 8.4, MariaDB 11.4) |
| Gestion de projet | **Trello** (Kanban), backlog de user stories |

---

## 🏗️ Architecture (MVC)

```
src/
├── Controller/          → C : reçoit la requête, appelle le modèle, choisit la vue
│   ├── Admin/           → Back-office EasyAdmin (CRUD)
│   ├── HomeController.php, ProduitController.php
│   ├── PanierController.php, CommandeController.php
│   └── CompteController.php, AdresseController.php
├── Entity/              → M : les objets métier (Produit, Commande, Utilisateur…)
├── Repository/          → M : les requêtes vers la base (QueryBuilder)
├── Form/                → Formulaires et règles de validation
├── Service/             → Logique métier réutilisable (PanierService, FactureService)
├── Security/Voter/      → Règles d'accès fines (CommandeVoter, AdresseVoter)
└── EventListener/       → Email automatique au changement de statut
templates/               → V : les vues Twig (pages, morceaux réutilisables `_*.html.twig`, emails, facture)
tests/                   → Tests PHPUnit (Entity/ et Controller/)
migrations/              → Historique de la structure de la base
```

### Diagramme de classes

![Diagramme de classes](docs/diagramme_classe_innovshop.svg)

| Entité | Rôle |
|---|---|
| `Categorie` 1 — * `Produit` | Un produit appartient à une catégorie |
| `Produit` ◆— * `OptionProduit` | Options proposées (taille, couleur…) |
| `Utilisateur` 1 — * `Adresse` | Adresses de livraison du client |
| `Utilisateur` 1 — * `Commande` | Historique des commandes |
| `Commande` ◆— * `LigneCommande` | Articles commandés (nom et prix **copiés** au moment de l'achat) |

> Le **panier** n'est pas une table : il est stocké dans la **session** du visiteur (`PanierService`).

---

## 🔒 Sécurité

| Risque | Protection mise en place |
|---|---|
| Injection SQL | Doctrine + requêtes paramétrées (`setParameter`) |
| XSS | Échappement automatique de Twig, `textContent` en JavaScript |
| CSRF | Jetons CSRF sur **tous** les formulaires (y compris AJAX) |
| Mots de passe | Hachage **bcrypt** (coût 13) via le password hasher de Symfony |
| Accès non autorisés | `access_control`, `#[IsGranted]`, rôles hiérarchisés |
| Accès aux données d'un autre client (IDOR) | **Voters** (`CommandeVoter`, `AdresseVoter`) |
| Fuite d'informations | Pages d'erreur 404 / 403 / 500 personnalisées, secrets uniquement dans `.env.local` (jamais commité) |
| Données invalides | Contraintes de validation `Assert` côté serveur |

---

## ⚡ Optimisation

- Correction d'un problème **N+1** (jointures `leftJoin` + `addSelect`) : catalogue passé de **10 à 2 requêtes SQL**
- **Index** sur les colonnes de tri/filtre (`date_ajout`, `ala_une`, `statut`) et index **unique** sur le numéro de commande
- Assets **minifiés** et **versionnés** (cache navigateur longue durée)
- Images en **lazy loading**, meta description, `robots.txt`
- **Lighthouse (production) : 100 / 100 / 100 / 100** (performances, accessibilité, bonnes pratiques, SEO)

---

## 💻 Installation en local

### Prérequis
PHP **8.2+**, Composer, Symfony CLI, MariaDB (ou MySQL), Git.

### Étapes

```bash
# 1. Récupérer le projet
git clone https://github.com/LettreH/innovshop.git
cd innovshop

# 2. Installer les dépendances
composer install

# 3. Configurer la base de données dans un fichier .env.local (non commité)
#    DATABASE_URL="mysql://UTILISATEUR:MOT_DE_PASSE@127.0.0.1:3306/innovshop?serverVersion=10.11.14-MariaDB&charset=utf8mb4"

# 4. Créer la base, les tables et les données de démonstration
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load

# 5. Lancer le serveur
symfony server:start
```

➡️ Le site est disponible sur **http://127.0.0.1:8000**

Comptes de démonstration créés par les fixtures : `admin@innovshop.fr` / `Admin2026` (administrateur) et `client@innovshop.fr` / `Client2026` (client).

> En local, les emails ne sont pas envoyés (`MAILER_DSN=null://null`) : ils sont visibles dans le **Profiler Symfony** (onglet *E-mails*).

---

## 🧪 Tests

```bash
# Préparer la base de test (une seule fois) — fichier .env.test.local avec la même DATABASE_URL
php bin/console --env=test doctrine:database:create
php bin/console --env=test doctrine:schema:create
php bin/console --env=test doctrine:fixtures:load --no-interaction

# Lancer tous les tests
php vendor/bin/phpunit
```

Résultat : **OK (17 tests, 35 assertions)**

| Type | Fichier | Ce qui est vérifié |
|---|---|---|
| Unitaire | `tests/Entity/ProduitTest.php` | Valeurs par défaut, regroupement des options |
| Unitaire | `tests/Entity/CommandeTest.php` | Calcul des quantités (regroupement des lignes) |
| Fonctionnel | `tests/Controller/PagesPubliquesTest.php` | Accueil, catalogue, recherche, 404, ajout panier refusé en GET (405) |
| Fonctionnel | `tests/Controller/SecuriteAccesTest.php` | Redirection vers la connexion, admin interdit au client (403), Voter |

---

## 🚀 Déploiement (Alwaysdata)

1. **Site** de type PHP avec le **répertoire racine sur `public/`** (seul ce dossier est accessible depuis Internet)
2. Sur le serveur, en SSH :

```bash
git clone https://github.com/LettreH/innovshop.git innovshop
cd innovshop
nano .env.local      # APP_ENV=prod, APP_DEBUG=0, APP_SECRET, DATABASE_URL, MAILER_DSN, MAILER_FROM
composer install --no-dev --optimize-autoloader
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console minify:install
php bin/console asset-map:compile
php bin/console cache:clear
```

### Mise à jour du site en ligne

```bash
cd ~/innovshop
git pull
composer install --no-dev --optimize-autoloader
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console asset-map:compile
php bin/console cache:clear
```

---

## 📋 Bonnes pratiques Symfony appliquées

- Configuration par **variables d'environnement** (secrets dans `.env.local`, jamais commités)
- **Attributs PHP** pour les routes, la validation et la sécurité (`#[Route]`, `#[Assert\…]`, `#[IsGranted]`)
- **Injection de dépendances** (aucun `new` pour les services)
- **Controllers légers**, logique métier dans des **Services**
- **Formulaires** dans des classes dédiées (`…Type`)
- **Voters** pour les règles d'accès
- **Migrations** Doctrine pour faire évoluer la base
- **Twig** : héritage de templates et morceaux réutilisables
- Code conforme **PSR-12**

---

## 📁 Livrables du projet

- 📋 Backlog (user stories) et tableau **Kanban Trello**
- 🧩 Diagramme de classes : [`docs/diagramme_classe_innovshop.svg`](docs/diagramme_classe_innovshop.svg)
- 💻 Code source : ce dépôt
- 🌍 Site en ligne : https://hermann.alwaysdata.net/innovshop/

---

## 👤 Auteur

**Hermann** — Formation *Développeur Web et Web Mobile*, Global Digital University (Mulhouse)
GitHub : [@LettreH](https://github.com/LettreH)

> Les produits présentés sont **imaginaires** et la boutique est **fictive** : aucune vente réelle n'est effectuée.
