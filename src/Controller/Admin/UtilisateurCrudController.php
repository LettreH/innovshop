<?php

namespace App\Controller\Admin;

use App\Entity\Utilisateur;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UtilisateurCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Utilisateur::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs');
    }

    // Pas de creation (les clients s'inscrivent eux-memes)
    // Pas de suppression (on garderait des commandes sans client)
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield EmailField::new('email', 'Email');

        yield TextField::new('prenom', 'Prenom');

        yield TextField::new('nom', 'Nom');

        // Les roles : on coche "Administrateur" pour donner le badge rouge
        yield ChoiceField::new('roles', 'Roles')
            ->setChoices([
                'Client'         => 'ROLE_USER',
                'Administrateur' => 'ROLE_ADMIN',
            ])
            ->allowMultipleChoices()
            ->renderExpanded()
            ->renderAsBadges([
                'ROLE_USER'  => 'secondary',
                'ROLE_ADMIN' => 'danger',
            ]);

        // Dans la liste : le NOMBRE de commandes
        yield AssociationField::new('commandes', 'Nb commandes')
            ->onlyOnIndex();

        // Sur la page "Voir" : la LISTE des numeros de commande
        yield ArrayField::new('commandes', 'Historique des commandes')
            ->onlyOnDetail();

        // ATTENTION : aucun champ "password" ici, volontairement !
    }
}
