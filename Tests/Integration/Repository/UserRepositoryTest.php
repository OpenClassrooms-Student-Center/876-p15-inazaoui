<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Media;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private UserRepository $repository;

    #[\Override]
    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::getContainer()
            ->get(EntityManagerInterface::class);

        $this->repository = $this->entityManager
            ->getRepository(User::class);
    }

    public function testFindAllGuestsReturnsOnlyGuests(): void
    {
        // Arrange
        $guest = new User();
        $guest->setEmail('guest_unique@test.com');
        $guest->setNom('GuestUnique');
        $guest->setPrenom('User');
        $guest->setRoles(['ROLE_USER']);
        $guest->setPassword('password');

        $admin = new User();
        $admin->setEmail('admin_unique@test.com');
        $admin->setNom('AdminUnique');
        $admin->setPrenom('User');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword('password');

        $this->entityManager->persist($guest);
        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        $this->entityManager->clear();

        // Act
        $guests = $this->repository->findAllGuests();

        // Assert 1 — aucun admin ne doit être présent
        foreach ($guests as $user) {
            $this->assertNotContains('ROLE_ADMIN', $user->getRoles());
        }

        // Assert 2 — l’invité créé est bien présent
        $guestEmails = array_map(
            static fn (User $u) => $u->getEmail(),
            $guests
        );

        $this->assertContains('guest_unique@test.com', $guestEmails);
    }

    public function testFindAdminReturnsAdminUser(): void
    {
        $admin = new User();
        $admin->setEmail('admin_find@test.com');
        $admin->setNom('AdminFind');
        $admin->setPrenom('Ina');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword('password');

        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $result = $this->repository->findAdmin();

        $this->assertInstanceOf(User::class, $result);
        $this->assertContains('ROLE_ADMIN', $result->getRoles());
    }

    public function testFindGuestWithMediasReturnsGuestWithMedias(): void
    {
        $guest = new User();
        $guest->setEmail('guest_media_'.uniqid('', true).'@test.com');
        $guest->setNom('GuestMedia');
        $guest->setPrenom('User');
        $guest->setRoles(['ROLE_USER']);
        $guest->setPassword('password');

        $media = new Media();
        $media->setTitle('Photo test');
        $media->setPath('photo.jpg');
        $media->setUser($guest);

        $this->entityManager->persist($guest);
        $this->entityManager->persist($media);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $result = $this->repository->findGuestWithMedias($guest->getId());

        $this->assertInstanceOf(User::class, $result);

        $titles = array_map(
            static fn (Media $m) => $m->getTitle(),
            $result->getMedias()->toArray()
        );

        $this->assertContains('Photo test', $titles);
    }

    public function testFindGuestWithMediasReturnsNullForAdmin(): void
    {
        $admin = new User();
        $admin->setEmail('admin_media@test.com');
        $admin->setNom('AdminMedia');
        $admin->setPrenom('User');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword('password');

        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $result = $this->repository
            ->findGuestWithMedias($admin->getId());

        $this->assertNull($result);
    }

    #[\Override]
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }


    public function testFindAllGuestsReturnsArray(): void
    {
        self::bootKernel();

        $repo = static::getContainer()
            ->get('doctrine')
            ->getRepository(User::class);

        $result = $repo->findAllGuests();

        self::assertIsArray($result);
    }

}
