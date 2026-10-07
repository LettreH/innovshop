<?php

namespace App\Controller\Admin;

use App\Entity\Commande;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CommandeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Commande::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commande')
            ->setEntityLabelInPlural('Commandes')
            ->setDefaultSort(['dateCommande' => 'DESC']);
    }

    // Les boutons autorises : on interdit "creer" et "supprimer"
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        // Champs en lecture seule : visibles, mais pas modifiables
        yield TextField::new('numero', 'Numero')
            ->setFormTypeOption('disabled', true);

        yield DateTimeField::new('dateCommande', 'Date')
            ->setFormTypeOption('disabled', true);

        yield AssociationField::new('utilisateur', 'Client')
            ->setFormTypeOption('disabled', true);

        yield AssociationField::new('adresse', 'Livraison')
            ->setFormTypeOption('disabled', true)
            ->hideOnIndex();

        yield AssociationField::new('lignes', 'Articles')
            ->onlyOnIndex();

        yield MoneyField::new('montantTotal', 'Total')
            ->setCurrency('EUR')
            ->setStoredAsCents(false)
            ->setFormTypeOption('disabled', true);

        // Le SEUL champ modifiable : le statut
        yield ChoiceField::new('statut', 'Statut')
            ->setChoices([
                'En attente' => 'en_attente',
                'Validée' => 'validee',
                'Expediee'   => 'expediee',
                'Livree'     => 'livree',
                'Annulee'    => 'annulee',
            ])
            ->renderAsBadges([
                'en_attente' => 'warning',
                'validee' => 'primary',
                'expediee'   => 'info',
                'livree'     => 'success',
                'annulee'    => 'danger',
            ]);
    }
}