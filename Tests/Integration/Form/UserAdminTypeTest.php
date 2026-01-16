<?php

declare(strict_types=1);

namespace App\Tests\Integration\Form;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\Form\Test\TypeTestCase;

class UserAdminTypeTest extends TypeTestCase
{
    public function testSubmitValidUser(): void
    {
        $formData = [
            'email' => 'invite@test.fr',
            'userActif' => true,
        ];

        $user = new User();
        $form = $this->factory->create(UserType::class, $user);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame('invite@test.fr', $user->getEmail());
        self::assertTrue($user->isUserActif());
    }
}
