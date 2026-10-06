<?php

namespace App\Controller\Admin;

use App\Entity\Produit;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProduitCrudController extends AbstractCrudController
{
    // Ce CRUD gere l'entite Produit
    public static function getEntityFqcn(): string
    {
        return Produit::class;
    }

    // Les titres de la page, en francais
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Produit')
            ->setEntityLabelInPlural('Produits')
            ->setDefaultSort(['dateAjout' => 'DESC']);
    }

    // Les champs affiches, un par un
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield TextField::new('nom', 'Nom');

        yield AssociationField::new('categorie', 'Categorie');

        yield MoneyField::new('prix', 'Prix')
            ->setCurrency('EUR')
            ->setStoredAsCents(false);

        yield ImageField::new('image', 'Image')
            ->setBasePath('uploads/produits')
            ->setUploadDir('public/uploads/produits')
            ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
            ->setRequired(false);

        yield TextareaField::new('description', 'Description')->hideOnIndex();

        yield BooleanField::new('alaUne', 'A la une');

        yield DateTimeField::new('dateAjout', "Date d'ajout")->hideOnForm();
    }
}