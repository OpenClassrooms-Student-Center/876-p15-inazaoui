<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\Album;
use App\Form\AlbumType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class AlbumTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        parent::getExtensions();
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'name' => 'Album Test',
        ];

        $album = new Album();

        $form = $this->factory->create(AlbumType::class, $album);
        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());

        self::assertSame('Album Test', $album->getName());
    }

    public function testSubmitInvalidData(): void
    {
        $formData = [
            'name' => '', // invalide
        ];

        $album = new Album();

        $form = $this->factory->create(AlbumType::class, $album);
        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());
    }
}
