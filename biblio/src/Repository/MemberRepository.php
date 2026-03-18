<?php

namespace App\Repository;

use App\Entity\Member;
use App\Entity\Loan;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Member>
 */
class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    /**
     * @return Member[]
     */
    public function searchByName(string $query): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.user', 'u')->addSelect('u')
            ->andWhere('m.lastName LIKE :q OR m.firstName LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->orderBy('m.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Member[]
     */
    public function findFiltered(?string $search = null, ?string $status = null, int $page = 1, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.user', 'u')->addSelect('u');

        if ($search !== null && $search !== '') {
            $qb->andWhere('m.lastName LIKE :search OR m.firstName LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($status === 'suspended') {
            $qb->andWhere('m.suspended = true');
        } elseif ($status === 'active') {
            $qb->andWhere('m.suspended = false');
        }

        $qb->orderBy('m.lastName', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function countFiltered(?string $search = null, ?string $status = null): int
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->leftJoin('m.user', 'u');

        if ($search !== null && $search !== '') {
            $qb->andWhere('m.lastName LIKE :search OR m.firstName LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($status === 'suspended') {
            $qb->andWhere('m.suspended = true');
        } elseif ($status === 'active') {
            $qb->andWhere('m.suspended = false');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countActive(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.suspended = false')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Returns paginated members with reservation/loan counters for librarian views.
     *
     * @return array<int, array{member: Member, reservationCount: int, activeLoanCount: int, overdueLoanCount: int}>
     */
    public function findPaginatedWithStats(?string $search, int $page, int $limit, \DateTimeInterface $now): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.user', 'u')
            ->addSelect('u')
            ->addSelect('(SELECT COUNT(r1.id) FROM ' . Reservation::class . ' r1 WHERE r1.member = m) AS reservationCount')
            ->addSelect('(SELECT COUNT(l1.id) FROM ' . Loan::class . ' l1 WHERE l1.member = m AND l1.returnDate IS NULL) AS activeLoanCount')
            ->addSelect('(SELECT COUNT(l2.id) FROM ' . Loan::class . ' l2 WHERE l2.member = m AND l2.returnDate IS NULL AND l2.dueDate < :now) AS overdueLoanCount')
            ->setParameter('now', $now);

        if ($search !== null && $search !== '') {
            $qb->andWhere('m.lastName LIKE :search OR m.firstName LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $rows = $qb
            ->orderBy('m.lastName', 'ASC')
            ->addOrderBy('m.firstName', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_map(static function (array $row): array {
            return [
                'member' => $row[0],
                'reservationCount' => (int) ($row['reservationCount'] ?? 0),
                'activeLoanCount' => (int) ($row['activeLoanCount'] ?? 0),
                'overdueLoanCount' => (int) ($row['overdueLoanCount'] ?? 0),
            ];
        }, $rows);
    }

    public function countBySearch(?string $search): int
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)');

        if ($search !== null && $search !== '') {
            $qb->andWhere('m.lastName LIKE :search OR m.firstName LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return array<int, array{id: int, fullName: string}>
     */
    public function searchSuggestions(string $query, int $limit = 8): array
    {
        $members = $this->createQueryBuilder('m')
            ->select('m.id, m.firstName, m.lastName')
            ->andWhere('m.lastName LIKE :q OR m.firstName LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->orderBy('m.lastName', 'ASC')
            ->addOrderBy('m.firstName', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return array_map(static fn (array $member): array => [
            'id' => (int) $member['id'],
            'fullName' => trim(($member['firstName'] ?? '') . ' ' . ($member['lastName'] ?? '')),
        ], $members);
    }

    /**
     * @return Member[]
     */
    public function findLoanSuggestions(string $query, int $limit = 10): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.user', 'u')->addSelect('u')
            ->andWhere('m.lastName LIKE :q OR m.firstName LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->orderBy('m.lastName', 'ASC')
            ->addOrderBy('m.firstName', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
