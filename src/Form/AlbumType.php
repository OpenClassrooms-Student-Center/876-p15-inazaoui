<?php

namespace App\Form;

use App\Entity\Album;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire de création et de modification d'un album.
 *
 * Contient un unique champ texte pour le nom de l'album.
 */
class AlbumType extends AbstractType
{
    /**
     * Construit le formulaire avec le champ nom de l'album.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire
     * @param array<string, mixed> $options Les options du formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'label' => 'Nom',
        ]);
    }

    /**
     * Configure les options par défaut du formulaire.
     * Lie le formulaire à l'entité Album.
     *
     * @param OptionsResolver $resolver Le résolveur d'options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Album::class,
        ]);
    }
}
