<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Image (obligatoire à l’ajout, facultative à l’édition)
            ->add('file', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => !$options['is_edit'],
                'help' => $options['is_edit']
                    ? 'Laissez vide pour conserver l’image actuelle'
                    : null,
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: ['image/jpeg', 'image/png'],
                        mimeTypesMessage: 'Veuillez sélectionner une image JPG ou PNG valide'
                    ),
                ],
            ])

            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])

            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                ],
                'help' => 'Description facultative du média',
            ]);

        // Champs réservés à l’admin
        if (true === $options['is_admin']) {
            $builder
                ->add('user', EntityType::class, [
                    'label' => 'Utilisateur',
                    'class' => User::class,
                    'required' => false,
                    'choice_label' => 'email',
                    'placeholder' => '— Sélectionner un utilisateur —',
                ])
                ->add('album', EntityType::class, [
                    'label' => 'Album',
                    'class' => Album::class,
                    'required' => false,
                    'choice_label' => 'name',
                    'placeholder' => '— Aucun album —',
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
            'is_admin' => false,
            'is_edit' => false,
        ]);

        $resolver->setAllowedTypes('is_admin', 'bool');
        $resolver->setAllowedTypes('is_edit', 'bool');
    }
}
