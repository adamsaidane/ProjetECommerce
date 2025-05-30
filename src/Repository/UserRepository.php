<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
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

    /**
     * Find users with pagination, search and filters
     */
    public function findPaginated(array $criteria, int $page, int $limit, ?string $search = null, string $sortBy = 'createdAt', string $sortDir = 'DESC'): array
    {
        $qb = $this->createQueryBuilder('u');

        // Apply search
        if ($search) {
            $qb->andWhere('u.firstName LIKE :search OR u.lastName LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Apply role filter
        if (isset($criteria['roles'])) {
            $qb->andWhere('u.roles LIKE :role')
                ->setParameter('role', '%' . $criteria['roles'] . '%');
        }

        // Apply sorting
        $qb->orderBy('u.' . $sortBy, $sortDir);

        // Apply pagination
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Count users with filters and search
     */
    public function countByCriteria(array $criteria, ?string $search = null): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)');

        // Apply search
        if ($search) {
            $qb->andWhere('u.firstName LIKE :search OR u.lastName LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Apply role filter
        if (isset($criteria['roles'])) {
            $qb->andWhere('u.roles LIKE :role')
                ->setParameter('role', '%' . $criteria['roles'] . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
