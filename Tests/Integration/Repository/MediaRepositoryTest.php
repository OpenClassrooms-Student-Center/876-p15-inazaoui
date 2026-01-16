<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Media;
use App\Entity\User;
use App\Repository\MediaRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MediaRepositoryTest extends KernelTestCase
{
    private MediaRepository $mediaRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->mediaRepository = static::getContainer()
            ->get(MediaRepository::class);
    }

    public function testFindByUser(): void
    {
        $em = static::getContainer()->get('doctrine')->getManager();
        $passwordHasher = static::getContainer()
            ->get(UserPasswordHasherInterface::class);

        // User de test
        $user = new User();
        $user->setEmail('repo_'.uniqid('', true).'@test.com');
        $user->setRoles(['ROLE_USER']);
        $user->setUserActif(true);
        $user->setPassword(
            $passwordHasher->hashPassword($user, 'password')
        );

        $em->persist($user);

        // Media associé unique pour ce test
        $media = new Media();
        $media->setTitle('Media repo test');
        $media->setDescription('Media repo test');
        $media->setUser($user);

        $em->persist($media);
        $em->flush();

        // Act
        $result = $this->mediaRepository->findBy(compact('user'));

        // Assert ROBUSTE : le média créé doit être présent
        $titles = array_map(
            static fn (Media $m) => $m->getTitle(),
            $result
        );

        $this->assertContains('Media repo test', $titles);
    }

    public function testFindAllReturnsArray(): void
    {
        $result = $this->mediaRepository->findAll();

        $this->assertIsArray($result);
    }

    public function testFindByUserDoesNotCrash(): void
    {
        self::bootKernel();

        $repo = static::getContainer()
            ->get('doctrine')
            ->getRepository(Media::class);

        $result = $repo->findBy([]);

        self::assertIsArray($result);
    }

}
