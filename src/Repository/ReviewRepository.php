<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function findPaginated(array $criteria = [], int $page = 1, int $limit = 20, ?string $search = null, string $sortBy = 'createdAt', string $sortDir = 'DESC'): array
    {
        $query = $this->createQueryBuilder('r')
            ->leftJoin('r.product', 'p')
            ->leftJoin('r.user', 'u')
            ->addSelect('p')
            ->addSelect('u');

        // Add criteria
        foreach ($criteria as $field => $value) {
            $query->andWhere("r.$field = :$field")
                ->setParameter($field, $value);
        }

        // Add search condition
        if ($search) {
            $query->andWhere('p.title LIKE :search OR u.firstName LIKE :search OR u.lastName LIKE :search OR r.comment LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Add sorting
        if (property_exists(Review::class, $sortBy)) {
            $query->orderBy('r.' . $sortBy, $sortDir);
        } else {
            $query->orderBy('r.createdAt', 'DESC');
        }

        // Add pagination
        $query->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }

    public function countByCriteria(array $criteria = [], ?string $search = null): int
    {
        $query = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->leftJoin('r.product', 'p')
            ->leftJoin('r.user', 'u');

        // Add criteria
        foreach ($criteria as $field => $value) {
            $query->andWhere("r.$field = :$field")
                ->setParameter($field, $value);
        }

        // Add search condition
        if ($search) {
            $query->andWhere('p.title LIKE :search OR u.firstName LIKE :search OR u.lastName LIKE :search OR r.comment LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    public function getAverageRating(): float
    {
        $query = $this->createQueryBuilder('r')
            ->select('AVG(r.rating)')
            ->getQuery();

        $result = $query->getSingleScalarResult();

        return $result ? round((float)$result, 1) : 0;
    }
}
