<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Entity\User;
use App\Tests\Support\TestUserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserAjaxTest extends WebTestCase
{
    public function testUsersDataEndpointReturnsDatatableJsonWithStatut(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        // Connexion admin
        $admin = TestUserFactory::getOrCreateIna($em);
        $client->loginUser($admin);

        // Création d’un invité actif
        $email = 'guest_'.uniqid('', true).'@test.fr';

        $guest = new User();
        $guest->setEmail($email);
        $guest->setRoles(['ROLE_USER']); // IMPORTANT : rôle attendu par l’admin
        $guest->setPassword('test');
        $guest->setUserActif(true);

        $em->persist($guest);
        $em->flush();

        // Forcer DataTables à retourner cet utilisateur
        $client->xmlHttpRequest('POST', '/admin/users/data', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => $email], // clé de la stabilité
            'order' => [['column' => 0, 'dir' => 'asc']],
            'columns' => [
                ['data' => 'id'],
                ['data' => 'email'],
                ['data' => 'prenom'],
                ['data' => 'nom'],
                ['data' => 'statut'],
                ['data' => 'actions'],
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');

        $payload = json_decode(
            $client->getResponse()->getContent(),
            true,
            flags: JSON_THROW_ON_ERROR
        );

        self::assertArrayHasKey('data', $payload);
        self::assertIsArray($payload['data']);

        // Vérification stricte : l’utilisateur recherché DOIT être présent
        self::assertCount(
            1,
            $payload['data'],
            'La recherche DataTables aurait dû retourner exactement un utilisateur.'
        );

        $row = $payload['data'][0];

        self::assertSame($email, $row['email']);
        self::assertStringContainsString(
            'Actif',
            (string) $row['statut']
        );
    }
}
