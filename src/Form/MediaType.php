<?php

namespace App\Form;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * @extends AbstractType<Media>
 */
class MediaType extends AbstractType
{
    /**
     * @param string        $maxSize   Taille maximale du fichier (ex: "2M", "500K")
     * @param array<string> $mimeTypes Types MIME autorisés (ex: ["image/jpeg", "image/png"])
     */
    public function __construct(
        #[Autowire('%app.media.max_size%')]
        private readonly string $maxSize,
        #[Autowire('%app.media.mime_types%')]
        private readonly array $mimeTypes,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $formats = array_map(
            static fn (string $mime): string => strtoupper(substr((string) strrchr($mime, '/'), 1)),
            $this->mimeTypes
        );

        $builder
            ->add('file', FileType::class, [
                'label' => 'Image',
                'constraints' => [
                    new NotNull(message: 'Veuillez sélectionner une image.'),
                    new Image(
                        maxSize: $this->maxSize,
                        mimeTypes: $this->mimeTypes,
                        mimeTypesMessage: 'Formats acceptés : '.implode(', ', $formats).'.',
                        maxSizeMessage: 'Le fichier est trop lourd ({{ size }} {{ suffix }}). Maximum : {{ limit }} {{ suffix }}.',
                        detectCorrupted: true,
                        corruptedMessage: 'Le fichier image est corrompu.',
                    ),
                ],
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'constraints' => [
                    new NotNull(message: 'Veuillez saisir un titre.'),
                ],
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
            'is_admin' => false,
            'translation_domain' => false,
        ]);
    }
}
