<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Formulaire de changement de mot de passe.
 * Aucun champ n'est relié directement à l'entité (pas de data_class).
 */
class ChangerMotDePasseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ancienMotDePasse', PasswordType::class, [
                'label' => 'Mot de passe actuel',
                'constraints' => [
                    new UserPassword(message: 'Le mot de passe actuel est incorrect.'),
                ],
            ])
            ->add('nouveauMotDePasse', RepeatedType::class, [
                'type'            => PasswordType::class,
                'invalid_message' => 'Les deux mots de passe ne sont pas identiques.',
                'first_options'   => [
                    'label' => 'Nouveau mot de passe',
                    'help'  => '8 caractères minimum, avec au moins une majuscule et un chiffre.',
                ],
                'second_options'  => [
                    'label' => 'Confirmer le nouveau mot de passe',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Choisissez un nouveau mot de passe.'),
                    new Assert\Length(min: 8, max: 4096, minMessage: 'Au moins {{ limit }} caractères.'),
                    new Assert\Regex(
                        pattern: '/^(?=.*[A-Z])(?=.*\d).+$/',
                        message: 'Le mot de passe doit contenir au moins une majuscule et un chiffre.'
                    ),
                ],
            ])
        ;
    }
}
