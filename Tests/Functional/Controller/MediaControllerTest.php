<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\Media;
use App\Tests\Support\TestUserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MediaControllerTest extends WebTestCase
{
    private function loginIna(): KernelBrowser
    {
        $client = static::createClient();

        $em = static::getContainer()->get('doctrine')->getManager();
        $user = TestUserFactory::getOrCreateIna($em);

        $client->loginUser($user);

        // IMPORTANT : crée une session HTTP réelle
        $client->request('GET', '/admin/media');

        return $client;
    }

    public function testMediaIndexRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin/media');

        self::assertResponseRedirects('/login');
    }

    public function testMediaIndexAsAdmin(): void
    {
        $client = $this->loginIna();

        $client->request('GET', '/admin/media');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('table');
    }

    public function testDeleteMedia(): void
    {
        $client = $this->loginIna();

        $em = static::getContainer()->get('doctrine')->getManager();
        $user = TestUserFactory::getOrCreateIna($em);

        // 1️⃣ Création du média
        $media = new Media();
        $media->setTitle('Test delete');
        $media->setPath('uploads/test.jpg');
        $media->setDescription('Description test');
        $media->setUser($user);

        $em->persist($media);
        $em->flush();

        $mediaId = $media->getId();

        // Sécurité : vérifier qu'il existe bien avant suppression
        self::assertNotNull(
            $em->getRepository(Media::class)->find($mediaId)
        );

        // 2️⃣ Appel POST réel (sans CSRF en env=test)
        $client->request(
            'POST',
            '/admin/media/delete/'.$mediaId
        );

        // 3️⃣ Redirection attendue
        self::assertResponseRedirects('/admin/media');
        $em->clear();
        self::assertNull(
            $em->getRepository(Media::class)->find($media->getId())
        );

        // self::assertResponseIsSuccessful();

        // 4️⃣ Vérification suppression
        $em->clear();
        $deleted = $em->getRepository(Media::class)->find($mediaId);

        self::assertNull($deleted);
    }
}
