<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Formulaire de création d'un compte invité.
 *
 * Contient les champs nom, e-mail, description et mot de passe en clair.
 * Le mot de passe n'est pas mappé directement sur l'entité ; il est haché
 * manuellement dans le contrôleur avant la persistance.
 */
class GuestType extends AbstractType
{
    /**
     * Construit le formulaire avec les champs de l'invité.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire
     * @param array<string, mixed> $options Les options du formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom'])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Le mot de passe est obligatoire.']),
                    new Length(['min' => 6, 'minMessage' => 'Le mot de passe doit faire au moins 6 caractères.']),
                ]
            ]);
    }

    /**
     * Configure les options par défaut du formulaire.
     * Lie le formulaire à l'entité User.
     *
     * @param OptionsResolver $resolver Le résolveur d'options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}