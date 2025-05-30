<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findPaginated(int $page = 1, int $limit = 20, ?string $search = null, string $sortBy = 'createdAt', string $sortDir = 'DESC'): array
    {
        $query = $this->createQueryBuilder('c');

        // Add search condition
        if ($search) {
            $query->andWhere('c.name LIKE :search OR c.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Add sorting
        if (property_exists(Category::class, $sortBy)) {
            $query->orderBy('c.' . $sortBy, $sortDir);
        } else {
            $query->orderBy('c.createdAt', 'DESC');
        }

        // Add pagination
        $query->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }

    public function countBySearch(?string $search = null): int
    {
        $query = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)');

        // Add search condition
        if ($search) {
            $query->andWhere('c.name LIKE :search OR c.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return (int) $query->getQuery()->getSingleScalarResult();
    }
}
