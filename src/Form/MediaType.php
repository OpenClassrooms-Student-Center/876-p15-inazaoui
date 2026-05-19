<?php

namespace App\Form;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

/**
 * Formulaire de création d'un média (photo).
 *
 * Contient les champs fichier image et titre, avec validation du type MIME et de la taille.
 * En mode administrateur (option `is_admin`), des champs supplémentaires permettent
 * d'associer le média à un utilisateur et à un album.
 */
class MediaType extends AbstractType
{
    /**
     * Construit le formulaire avec les champs du média.
     * Ajoute les champs utilisateur et album si l'option `is_admin` est activée.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire
     * @param array<string, mixed> $options Les options du formulaire (inclut `is_admin`)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Image',
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Le fichier est trop volumineux ({{ size }} {{ suffix }}). La taille maximale autorisée est {{ limit }} {{ suffix }}.',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Le fichier doit être une image valide (JPEG, PNG, GIF ou WEBP).',
                    ])
                ]
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
        ;

        if ($options['is_admin']) {
            $builder
                ->add('user', EntityType::class, [
                    'label' => 'Utilisateur',
                    'required' => false,
                    'class' => User::class,
                    'choice_label' => 'name',
                ])
                ->add('album', EntityType::class, [
                    'label' => 'Album',
                    'required' => false,
                    'class' => Album::class,
                    'choice_label' => 'name',
                ])
            ;
        }
    }

    /**
     * Configure les options par défaut du formulaire.
     * Lie le formulaire à l'entité Media et déclare l'option `is_admin`.
     *
     * @param OptionsResolver $resolver Le résolveur d'options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
            'is_admin' => false,
        ]);
    }
}
