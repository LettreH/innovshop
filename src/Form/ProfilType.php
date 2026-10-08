<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Formulaire de modification du profil client.
 * L'email n'est pas modifiable : c'est l'identifiant de connexion.
 */
class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'empty_data' => '',
                'constraints' => [
                    new Assert\NotBlank(message: 'Le prénom est obligatoire.'),
                    new Assert\Length(max: 100),
                ],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'empty_data' => '',
                'constraints' => [
                    new Assert\NotBlank(message: 'Le nom est obligatoire.'),
                    new Assert\Length(max: 100),
                ],
            ])
            ->add('telephone', TelType::class, [
                'label'    => 'Téléphone',
                'required' => false,
                'help'     => 'Facultatif. Exemple : 06 12 34 56 78',
                'constraints' => [
                    new Assert\Regex(
                        pattern: '/^0[1-9](\s?\d{2}){4}$/',
                        message: 'Numéro invalide. Exemple : 06 12 34 56 78'
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
