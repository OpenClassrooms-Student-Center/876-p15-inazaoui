<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\Media;
use App\Form\MediaType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;

class MediaTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        return [
            new ValidatorExtension(Validation::createValidator()),
        ];
    }

    public function testFormBaseFieldsExist(): void
    {
        $form = $this->factory->create(MediaType::class);

        self::assertTrue($form->has('file'));
        self::assertTrue($form->has('title'));
        self::assertTrue($form->has('description'));

        // champs admin absents en test unitaire
        self::assertFalse($form->has('user'));
        self::assertFalse($form->has('album'));
    }

    public function testFormEditModeMakesFileOptional(): void
    {
        $form = $this->factory->create(MediaType::class, null, [
            'is_edit' => true,
        ]);

        self::assertFalse(
            $form->get('file')->getConfig()->getOption('required')
        );
    }

    public function testSubmitValidData(): void
    {
        $media = new Media();

        $form = $this->factory->create(MediaType::class, $media);
        $form->submit([
            'title' => 'Image test',
            'description' => 'Description test',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertSame('Image test', $media->getTitle());
        self::assertSame('Description test', $media->getDescription());
    }
}
