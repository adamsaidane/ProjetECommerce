<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findPaginated(array $criteria = [], int $page = 1, int $limit = 12, ?string $search = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c');

        foreach ($criteria as $field => $value) {
            $qb->andWhere("p.$field = :$field")
                ->setParameter($field, $value);
        }

        if ($search) {
            $qb->andWhere('p.title LIKE :search OR p.author LIKE :search OR p.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return $qb->orderBy('p.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countByCriteria(array $criteria = [], ?string $search = null): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)');

        foreach ($criteria as $field => $value) {
            $qb->andWhere("p.$field = :$field")
                ->setParameter($field, $value);
        }

        if ($search) {
            $qb->andWhere('p.title LIKE :search OR p.author LIKE :search OR p.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findByCategory(string $categorySlug, int $page = 1, int $limit = 12): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->where('c.slug = :slug')
            ->andWhere('p.isActive = true')
            ->setParameter('slug', $categorySlug)
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countByCategory(string $categorySlug): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->leftJoin('p.category', 'c')
            ->where('c.slug = :slug')
            ->andWhere('p.isActive = true')
            ->setParameter('slug', $categorySlug)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function search(string $query, int $page = 1, int $limit = 12): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->where('p.title LIKE :query OR p.author LIKE :query OR p.description LIKE :query')
            ->andWhere('p.isActive = true')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countSearch(string $query): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.title LIKE :query OR p.author LIKE :query OR p.description LIKE :query')
            ->andWhere('p.isActive = true')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getSingleScalarResult();
    }

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
