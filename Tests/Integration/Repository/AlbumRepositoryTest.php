<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Album;
use App\Repository\AlbumRepository;
use App\Tests\Support\TestUserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AlbumRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private AlbumRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = $this->em->getRepository(Album::class);

        $this->truncateTable();
    }

    private function truncateTable(): void
    {
        $conn = $this->em->getConnection();
        $platform = $conn->getDatabasePlatform();

        $conn->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $conn->executeStatement($platform->getTruncateTableSQL('album', true));
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function testPersistAndFindAlbum(): void
    {
        $user = TestUserFactory::getOrCreateIna($this->em);

        $album = (new Album())
            ->setName('Album Test')
            ->setUser($user);

        $this->em->persist($album);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findOneBy(['name' => 'Album Test']);

        self::assertNotNull($found);
        self::assertSame('Album Test', $found->getName());
        self::assertSame($user->getId(), $found->getUser()->getId());
    }

    public function testFindAllReturnsArray(): void
    {
        $user = TestUserFactory::getOrCreateIna($this->em);

        $album1 = (new Album())->setName('Album 1')->setUser($user);
        $album2 = (new Album())->setName('Album 2')->setUser($user);

        $this->em->persist($album1);
        $this->em->persist($album2);
        $this->em->flush();

        $albums = $this->repository->findAll();

        self::assertIsArray($albums);
        self::assertCount(2, $albums);
    }

    public function testCountAlbums(): void
    {
        $user = TestUserFactory::getOrCreateIna($this->em);

        $album = new Album()
            ->setName('Album Count')
            ->setUser($user);

        $this->em->persist($album);
        $this->em->flush();

        $count = $this->repository->count([]);

        self::assertSame(1, $count);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
    }

    public function testRepositoryIsInstantiable(): void
    {
        self::bootKernel();

        $repo = static::getContainer()
            ->get('doctrine')
            ->getRepository(Album::class);

        self::assertInstanceOf(AlbumRepository::class, $repo);
    }

}
