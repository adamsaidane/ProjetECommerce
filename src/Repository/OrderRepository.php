<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * Find orders with pagination, search and filters
     */
    public function findPaginated(array $criteria, int $page, int $limit, ?string $search = null, string $sortBy = 'createdAt', string $sortDir = 'DESC'): array
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.user', 'u')
            ->addSelect('u');

        // Apply search
        if ($search) {
            $qb->andWhere('o.orderNumber LIKE :search OR u.firstName LIKE :search OR u.lastName LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Apply status filter
        if (isset($criteria['status'])) {
            $qb->andWhere('o.status = :status')
                ->setParameter('status', $criteria['status']);
        }

        // Apply sorting
        $qb->orderBy('o.' . $sortBy, $sortDir);

        // Apply pagination
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Count orders with filters and search
     */
    public function countByCriteria(array $criteria, ?string $search = null): int
    {
        $qb = $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->leftJoin('o.user', 'u');

        // Apply search
        if ($search) {
            $qb->andWhere('o.orderNumber LIKE :search OR u.firstName LIKE :search OR u.lastName LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Apply status filter
        if (isset($criteria['status'])) {
            $qb->andWhere('o.status = :status')
                ->setParameter('status', $criteria['status']);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Find low stock products
     */
    public function findLowStock(int $threshold = 5): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.stock <= :threshold')
            ->andWhere('p.isActive = true')
            ->setParameter('threshold', $threshold)
            ->orderBy('p.stock', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
