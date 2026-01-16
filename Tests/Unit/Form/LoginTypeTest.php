<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Form\LoginType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;

class LoginTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $validator = Validation::createValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }

    public function testFormFieldsExist(): void
    {
        $form = $this->factory->create(LoginType::class);

        self::assertTrue($form->has('email'));
        self::assertTrue($form->has('password'));
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $form = $this->factory->create(LoginType::class);
        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
    }
}
