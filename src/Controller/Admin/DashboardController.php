<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Adresse du back office : /admin
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]

// 2e protection : seul un ROLE_ADMIN peut entrer
// (la 1re est la regle ^/admin dans security.yaml)
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    // La page d'accueil du back office
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    // Le titre affiche en haut a gauche
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('InnovShop - Administration');
    }

    // Le menu de gauche
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        yield MenuItem::section('Boutique');
        yield MenuItem::linkTo(CategorieCrudController::class, 'Categories', 'fa fa-tags');
        yield MenuItem::linkTo(ProduitCrudController::class, 'Produits', 'fa fa-rocket');

        yield MenuItem::section('Ventes');
        yield MenuItem::linkTo(CommandeCrudController::class, 'Commandes', 'fa fa-box');

        yield MenuItem::section('Clients');
        yield MenuItem::linkTo(UtilisateurCrudController::class, 'Utilisateurs', 'fa fa-users');

        yield MenuItem::section();
        yield MenuItem::linkToRoute('Retour a la boutique', 'fa fa-store', 'app_home');
    }
}
