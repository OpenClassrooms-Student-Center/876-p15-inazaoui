<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Album;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AlbumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Album::class);
    }

    /**
     * Albums avec leur propriétaire et leurs médias.
     */
    public function findAllWithMediaAndUser(): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.media', 'm')
            ->addSelect('m')
            ->leftJoin('a.user', 'u')
            ->addSelect('u')
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Albums d’un utilisateur donné.
     */
    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->setParameter('user', $userId)
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Albums avec médias visibles (ex : utilisateur actif).
     */
    public function findAlbumsWithVisibleMedias(): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.media', 'm')
            ->addSelect('m')
            ->leftJoin('m.user', 'u')
            ->addSelect('u')
            ->andWhere('u.userActif = true')
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
