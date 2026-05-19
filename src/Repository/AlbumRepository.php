<?php

namespace App\Repository;

use App\Entity\Album;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Dépôt de données pour l'entité Album.
 *
 * Fournit les méthodes de requête Doctrine pour récupérer des albums depuis la base de données.
 *
 * @extends ServiceEntityRepository<Album>
 *
 * @method Album|null find($id, $lockMode = null, $lockVersion = null)
 * @method Album|null findOneBy(array $criteria, array $orderBy = null)
 * @method Album[]    findAll()
 * @method Album[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlbumRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry Le registre de gestionnaires Doctrine
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Album::class);
    }
}
