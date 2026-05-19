<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * Dépôt de données pour l'entité User.
 *
 * Fournit les méthodes de requête Doctrine pour récupérer des utilisateurs depuis la base de données.
 * Implémente PasswordUpgraderInterface pour la mise à jour automatique du hachage des mots de passe.
 *
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    /**
     * @param ManagerRegistry $registry Le registre de gestionnaires Doctrine
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Met à jour (re-hache) le mot de passe de l'utilisateur lors de la connexion si nécessaire.
     * Appelé automatiquement par Symfony lorsque l'algorithme de hachage a changé.
     *
     * @param PasswordAuthenticatedUserInterface $user              L'utilisateur dont le mot de passe doit être mis à jour
     * @param string                             $newHashedPassword Le nouveau mot de passe haché
     * @throws UnsupportedUserException Si l'utilisateur n'est pas une instance de User
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

    /**
     * Retourne tous les invités actifs (non bloqués), triés par nom.
     *
     * @return User[] La liste des invités actifs
     */
    public function findActiveGuests(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.admin = :admin')
            ->andWhere('u.blocked = :blocked')
            ->setParameter('admin', false)
            ->setParameter('blocked', false)
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne tous les invités (bloqués ou non), triés par nom.
     *
     * @return User[] La liste de tous les invités
     */
    public function findAllGuests(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.admin = :admin')
            ->setParameter('admin', false)
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne tous les invités actifs (non bloqués) avec leurs médias pré-chargés, triés par nom.
     * Le LEFT JOIN évite les requêtes N+1 lors de l'affichage des médias de chaque invité.
     *
     * @return User[] La liste des invités actifs avec leurs médias hydratés
     */
    public function findActiveGuestsWithMediaCount(): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.medias', 'm')
            ->addSelect('m')
            ->andWhere('u.admin = :admin')
            ->andWhere('u.blocked = :blocked')
            ->setParameter('admin', false)
            ->setParameter('blocked', false)
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
