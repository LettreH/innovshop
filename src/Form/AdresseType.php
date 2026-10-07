<?php

namespace App\Form;

use App\Entity\Adresse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Formulaire de saisie d'une adresse de livraison.
 */
class AdresseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rue', TextType::class, [
                'label' => 'Numéro et rue',
                'constraints' => [
                    new Assert\NotBlank(message: 'Indiquez votre numéro et votre rue.'),
                    new Assert\Length(max: 255),
                ],
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code postal',
                'constraints' => [
                    new Assert\NotBlank(message: 'Indiquez votre code postal.'),
                    new Assert\Regex(
                        pattern: '/^\d{5}$/',
                        message: 'Le code postal doit contenir 5 chiffres.'
                    ),
                ],
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'constraints' => [
                    new Assert\NotBlank(message: 'Indiquez votre ville.'),
                    new Assert\Length(max: 100),
                ],
            ])
            ->add('pays', TextType::class, [
                'label' => 'Pays',
                'constraints' => [
                    new Assert\NotBlank(message: 'Indiquez votre pays.'),
                    new Assert\Length(max: 100),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Adresse::class,
        ]);
    }
}