<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Retourne tous les invités = utilisateurs SANS ROLE_ADMIN.
     * Compatible toutes BDD, aucun SQL spécial.
     */
    public function findAllGuests(): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.medias', 'm')
            ->addSelect('m')
            ->andWhere('u.roles NOT LIKE :admin')
            ->setParameter('admin', '%ROLE_ADMIN%')
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne l’administratrice (Ina).
     */
    public function findAdmin(): ?User
    {
        foreach ($this->findAll() as $user) {
            if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                return $user;
            }
        }

        return null;
    }

    public function findGuestWithMedias(int $id): ?User
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.medias', 'm')
            ->addSelect('m')
            ->andWhere('u.id = :id')
            ->andWhere('u.roles NOT LIKE :admin')
            ->setParameter('id', $id)
            ->setParameter('admin', '%ROLE_ADMIN%')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
