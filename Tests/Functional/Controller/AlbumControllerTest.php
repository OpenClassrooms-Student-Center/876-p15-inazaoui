<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\Album;
use App\Tests\Support\TestUserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class AlbumControllerTest extends WebTestCase
{
    private function getEntityManager($client): EntityManagerInterface
    {
        return $client->getContainer()->get(EntityManagerInterface::class);
    }

    private function getRouter($client): RouterInterface
    {
        return $client->getContainer()->get(RouterInterface::class);
    }

    private function truncateTables(EntityManagerInterface $em): void
    {
        $connection = $em->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $connection->executeStatement($platform->getTruncateTableSQL('album', true));
        $connection->executeStatement($platform->getTruncateTableSQL('user', true));
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function loginAsAdmin($client, EntityManagerInterface $em): void
    {
        $admin = TestUserFactory::getOrCreateIna($em);
        $client->loginUser($admin);
    }

    public function testUpdateAlbum(): void
    {
        $client = static::createClient();
        $em = $this->getEntityManager($client);
        $router = $this->getRouter($client);

        $this->truncateTables($em);
        $this->loginAsAdmin($client, $em);

        $admin = TestUserFactory::getOrCreateIna($em);

        $album = (new Album())
            ->setName('Album original')
            ->setUser($admin);

        $em->persist($album);
        $em->flush();

        $crawler = $client->request(
            'GET',
            $router->generate('admin_album_edit', ['id' => $album->getId()])
        );

        $form = $crawler->filter('form')->form([
            'album[name]' => 'Album modifié',
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/admin/album');

        $em->clear();
        $updated = $em->getRepository(Album::class)->find($album->getId());

        self::assertSame('Album modifié', $updated->getName());
    }

    public function testDeleteAlbum(): void
    {
        $client = static::createClient();
        $em = $this->getEntityManager($client);

        $this->truncateTables($em);
        $this->loginAsAdmin($client, $em);

        $admin = TestUserFactory::getOrCreateIna($em);

        $album = (new Album())
            ->setName('Album à supprimer')
            ->setUser($admin);

        $em->persist($album);
        $em->flush();

        $crawler = $client->request('GET', '/admin/album');
        $button = $crawler->filter(
            sprintf('button[data-delete-url="/admin/album/delete/%d"]', $album->getId())
        );

        $client->request('POST', $button->attr('data-delete-url'), [
            '_token' => $button->attr('data-delete-token'),
        ]);

        self::assertResponseRedirects('/admin/album');
        self::assertNull($em->getRepository(Album::class)->find($album->getId()));
    }
}
