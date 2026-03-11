<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function createSearchQueryBuilder(
        ?string $title = null,
        ?int $authorId = null,
        ?int $categoryId = null,
        ?string $language = null,
        ?int $yearFrom = null,
        ?int $yearTo = null,
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.author', 'a')
            ->addSelect('a')
            ->leftJoin('b.categories', 'c')
            ->addSelect('c');

        if ($title !== null && $title !== '') {
            $qb->andWhere('b.title LIKE :title')
                ->setParameter('title', '%' . $title . '%');
        }

        if ($authorId !== null) {
            $qb->andWhere('a.id = :authorId')
                ->setParameter('authorId', $authorId);
        }

        if ($categoryId !== null) {
            $qb->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if ($language !== null && $language !== '') {
            $qb->andWhere('b.language = :language')
                ->setParameter('language', $language);
        }

        if ($yearFrom !== null) {
            $qb->andWhere('b.releaseYear >= :yearFrom')
                ->setParameter('yearFrom', $yearFrom);
        }

        if ($yearTo !== null) {
            $qb->andWhere('b.releaseYear <= :yearTo')
                ->setParameter('yearTo', $yearTo);
        }

        $qb->orderBy('b.title', 'ASC');

        return $qb;
    }

    public function findPaginated(int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.author', 'a')
            ->addSelect('a')
            ->leftJoin('b.categories', 'c')
            ->addSelect('c')
            ->orderBy('b.title', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
