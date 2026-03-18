<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\Loan;
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
        ?array $categoryIds = null,
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

        if ($categoryIds !== null && $categoryIds !== []) {
            $qb->andWhere('c.id IN (:categoryIds)')
                ->setParameter('categoryIds', array_values($categoryIds));
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

    /**
     * Pagination par livre : on récupère les IDs des livres et on les passe à la méthode findByIdsWithAuthorAndCategories
     */
    public function findPaginated(int $page, int $limit): array
    {
        $idQb = $this->createQueryBuilder('b')
            ->select('b.id')
            ->orderBy('b.title', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        $ids = array_map('intval', array_column($idQb->getQuery()->getScalarResult(), 'id'));
        if ($ids === []) {
            return [];
        }

        return $this->findByIdsWithAuthorAndCategories($ids);
    }

    /**
     * @param int[] $ids
     *
     * @return Book[]
     */
    public function findByIdsWithAuthorAndCategories(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return $this->createQueryBuilder('b')
            ->leftJoin('b.author', 'a')
            ->addSelect('a')
            ->leftJoin('b.categories', 'c')
            ->addSelect('c')
            ->where('b.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('b.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array<int, array{id: int, title: string, author: string, available: bool}>
     */
    public function findLoanSuggestions(string $query, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('b')
            ->select('b.id AS id, b.title AS title, a.firstName AS authorFirstName, a.lastName AS authorLastName, activeLoan.id AS activeLoanId')
            ->leftJoin('b.author', 'a')
            ->leftJoin(Loan::class, 'activeLoan', 'WITH', 'activeLoan.book = b AND activeLoan.returnDate IS NULL');

        if ($query !== '') {
            $qb->andWhere('b.title LIKE :query')
                ->setParameter('query', '%' . $query . '%')
                ->orderBy('CASE WHEN activeLoan.id IS NULL THEN 0 ELSE 1 END', 'ASC')
                ->addOrderBy('b.title', 'ASC');
        } else {
            $qb->andWhere('activeLoan.id IS NULL')
                ->orderBy('b.title', 'ASC');
        }

        $rows = $qb
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return array_map(static function (array $row): array {
            $author = trim((string) ($row['authorFirstName'] ?? '') . ' ' . (string) ($row['authorLastName'] ?? ''));

            return [
                'id' => (int) $row['id'],
                'title' => (string) $row['title'],
                'author' => $author,
                'available' => ($row['activeLoanId'] ?? null) === null,
            ];
        }, $rows);
    }
}
