<?php

namespace App\Repository;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findForActiveGuestsWithMediaCount(int $limit, int $offset): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT u.id,u.name,COUNT(m.id) AS mediaCount FROM \"user\" u
        LEFT JOIN media m ON m.user_id = u.id
        WHERE u.is_active = TRUE
        AND NOT (u.roles::jsonb @> :admin::jsonb)
        GROUP BY u.id, u.name
        ORDER BY u.id ASC
        LIMIT :limit 
        OFFSET :offset";

        return $conn->fetchAllAssociative(
        $sql,
        [
            'admin'  => '["ROLE_ADMIN"]',
            'limit'  => $limit,
            'offset' => $offset,
        ],
        [
            'admin'  => ParameterType::STRING,
            'limit'  => ParameterType::INTEGER,
            'offset' => ParameterType::INTEGER,
        ]
    );
    }

    public function findGuests(int $limit, int $offset, bool $onlyActive = false): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $ids = $connection->fetchFirstColumn(
            'SELECT id FROM "user" u WHERE NOT ((u.roles::jsonb) @> :admin::jsonb)',
            ['admin' => '["ROLE_ADMIN"]']
        );

        $qb = $this->createQueryBuilder('u')
            ->andWhere('u.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }


    public function findValidInvitation(string $token, DateTimeImmutable $now): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.invitationToken = :token')
            ->andWhere('u.invitationExpiredAt IS NOT NULL')
            ->andWhere('u.invitationExpiredAt >= :now')
            ->andWhere('u.isActive = false')
            ->setParameter('token', $token)
            ->setParameter('now', $now)
            ->getQuery()
            ->getOneOrNullResult();
    }

    
}
