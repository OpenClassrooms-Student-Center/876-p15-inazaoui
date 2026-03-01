<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserPlainePasswordTest extends TestCase
{
    public function testSetPlainPasswordStoreValueAndGetterReturnsIt(): void
    {
        $user = new User();
       
        $user->setPlainPassword('Password123@');

        $this->assertSame('Password123@', $user->getPlainPassword());
    }

}
