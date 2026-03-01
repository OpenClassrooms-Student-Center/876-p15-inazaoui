<?php

namespace App\Tests\Functional\Security;

use PHPUnit\Framework\Attributes\DataProvider;
use App\Tests\BaseWebTestCase;

class LoginTest extends BaseWebTestCase
{
    // test page rendered correctly
    public function testLoginPageShouldRender(): void
    {
        $this->get('/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Connexion');
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="_csrf_token"]');
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }

    // test wrong username and password and blocked guest
    #[DataProvider('provideInvalidLogins')]
    public function testLoginFails(string $username, string $password, string $expectedErrorContains):void 
    {
        $this->get('/login');

        $this->assertResponseIsSuccessful();
        
        $this->client->submitForm('Connexion', [ 
            '_username' => $username,
            '_password' => $password,
        ]);

        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();

        $this->assertAnySelectorTextContains('.alert-danger', $expectedErrorContains);
    }
    
    public static function provideInvalidLogins():iterable
    {
        yield 'wrong password' => [
            'activeGuest@test.com',
            'wrongPassword',
            'Identifiants invalides',
        ];

        yield 'unknown user' => [
            'unknownUser@test.com',
            'password123@',
            'Identifiants invalides',
        ];

        yield 'blocked user' => [
            'blockedGuest@test.com',
            'password123@',
            "Votre compte est bloqué ou non-activé. Veuillez contacter l'administrateur.",
        ];
    }

    
}
