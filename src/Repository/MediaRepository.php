<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Media;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Media>
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    /**
     * Médias visibles (utilisateur actif uniquement).
     *
     * @param array<string, mixed> $criteria
     *
     * @return list<Media>
     */
    public function findVisibleMedias(
        array $criteria = [],
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        $qb = $this->createQueryBuilder('m')
            ->innerJoin('m.user', 'u')
            ->andWhere('u.userActif = true')
            ->addOrderBy('m.id', 'ASC');

        // critères dynamiques (ex: user courant)
        foreach ($criteria as $field => $value) {
            $qb->andWhere(sprintf('m.%s = :%s', $field, $field))
                ->setParameter($field, $value);
        }

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        /** @var list<Media> $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * Compteur de médias visibles.
     *
     * @param array<string, mixed> $criteria
     */
    public function countVisibleMedias(array $criteria = []): int
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->innerJoin('m.user', 'u')
            ->andWhere('u.userActif = true');

        foreach ($criteria as $field => $value) {
            $qb->andWhere(sprintf('m.%s = :%s', $field, $field))
                ->setParameter($field, $value);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
