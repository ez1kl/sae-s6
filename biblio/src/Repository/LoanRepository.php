<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\Loan;
use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Loan>
 */
class LoanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Loan::class);
    }

    /**
     * @return Loan[]
     */
    public function findByMember(Member $member): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.book', 'b')
            ->addSelect('b')
            ->andWhere('l.member = :member')
            ->setParameter('member', $member)
            ->orderBy('l.loanDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveByBookId(int $bookId): ?Loan
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.book = :bookId')
            ->andWhere('l.returnDate IS NULL')
            ->setParameter('bookId', $bookId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveLoanByBook(Book $book): ?Loan
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.book = :book')
            ->andWhere('l.returnDate IS NULL')
            ->setParameter('book', $book)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countActiveByMember(Member $member): int
    {
        return (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.member = :member')
            ->andWhere('l.returnDate IS NULL')
            ->setParameter('member', $member)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Loan[]
     */
    public function findActiveLoans(): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.book', 'b')->addSelect('b')
            ->leftJoin('l.member', 'm')->addSelect('m')
            ->andWhere('l.returnDate IS NULL')
            ->orderBy('l.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Loan[]
     */
    public function findOverdueLoans(): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.book', 'b')->addSelect('b')
            ->leftJoin('l.member', 'm')->addSelect('m')
            ->andWhere('l.returnDate IS NULL')
            ->andWhere('l.dueDate < :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('l.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countActiveLoans(): int
    {
        return (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.returnDate IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countOverdueLoans(): int
    {
        return (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.returnDate IS NULL')
            ->andWhere('l.dueDate < :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getMonthlyLoanStats(int $months = 12): array
    {
        $since = (new \DateTime())->modify("-{$months} months");

        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT DATE_FORMAT(loan_date, '%Y-%m') AS month, COUNT(*) AS count
                FROM loan
                WHERE loan_date >= :since
                GROUP BY month
                ORDER BY month ASC";

        return $conn->executeQuery($sql, ['since' => $since->format('Y-m-d')])->fetchAllAssociative();
    }

    public function getLoansByCategory(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT c.name AS category, COUNT(l.id) AS count
                FROM loan l
                INNER JOIN book b ON l.book_id = b.id
                INNER JOIN book_category bc ON b.id = bc.book_id
                INNER JOIN category c ON bc.category_id = c.id
                GROUP BY c.name
                ORDER BY count DESC";

        return $conn->executeQuery($sql)->fetchAllAssociative();
    }

    /**
     * @return Loan[]
     */
    public function findActiveLoansByMember(Member $member): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.book', 'b')->addSelect('b')
            ->andWhere('l.member = :member')
            ->andWhere('l.returnDate IS NULL')
            ->setParameter('member', $member)
            ->orderBy('l.loanDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Loan[]
     */
    public function findCompletedLoansByMember(Member $member): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.book', 'b')->addSelect('b')
            ->andWhere('l.member = :member')
            ->andWhere('l.returnDate IS NOT NULL')
            ->setParameter('member', $member)
            ->orderBy('l.returnDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Loan[]
     */
    public function searchActiveLoans(string $search): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.book', 'b')->addSelect('b')
            ->leftJoin('l.member', 'm')->addSelect('m')
            ->andWhere('l.returnDate IS NULL')
            ->andWhere('b.title LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('l.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Loan[]
     */
    public function searchActiveLoansByMember(string $search): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.book', 'b')->addSelect('b')
            ->leftJoin('l.member', 'm')->addSelect('m')
            ->andWhere('l.returnDate IS NULL')
            ->andWhere('m.lastName LIKE :search OR m.firstName LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('l.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Loan[]
     */
    public function searchActiveLoansByAuthor(string $search): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.book', 'b')->addSelect('b')
            ->leftJoin('l.member', 'm')->addSelect('m')
            ->leftJoin('b.author', 'a')->addSelect('a')
            ->andWhere('l.returnDate IS NULL')
            ->andWhere('a.lastName LIKE :search OR a.firstName LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('l.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
