<?php

namespace App\Repository;

use App\Entity\Media;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

/**
 * Dépôt de données pour l'entité Media.
 *
 * Fournit les méthodes de requête Doctrine pour récupérer des médias depuis la base de données.
 *
 * @extends ServiceEntityRepository<Media>
 *
 * @method Media|null find($id, $lockMode = null, $lockVersion = null)
 * @method Media|null findOneBy(array $criteria, array $orderBy = null)
 * @method Media[]    findAll()
 * @method Media[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry Le registre de gestionnaires Doctrine
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    /**
     * Retourne tous les médias d'un utilisateur, triés par identifiant croissant.
     *
     * @param User $user L'utilisateur dont on veut récupérer les médias
     * @return Media[] La liste des médias triés par id
     */
    public function findByUserOrderedById(User $user): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :user')
            ->setParameter('user', $user)
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
