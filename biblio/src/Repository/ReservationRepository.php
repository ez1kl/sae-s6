<?php

namespace App\Repository;

use App\Domain\LibraryRules;
use App\Entity\Reservation;
use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    private function expiryDate(): \DateTimeInterface
    {
        return LibraryRules::reservationExpiryDate();
    }

    /**
     * Supprime les réservations expirées (> 7 jours).
     */
    public function deleteExpired(): int
    {
        return (int) $this->createQueryBuilder('r')
            ->delete()
            ->andWhere('r.createdAt < :expiry')
            ->setParameter('expiry', $this->expiryDate())
            ->getQuery()
            ->execute();
    }

    /**
     * @return Reservation[]
     */
    public function findByMember(Member $member): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.book', 'b')
            ->addSelect('b')
            ->andWhere('r.member = :member')
            ->andWhere('r.createdAt >= :expiry')
            ->setParameter('member', $member)
            ->setParameter('expiry', $this->expiryDate())
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByBookId(int $bookId): ?Reservation
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.book = :bookId')
            ->andWhere('r.createdAt >= :expiry')
            ->setParameter('bookId', $bookId)
            ->setParameter('expiry', $this->expiryDate())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countByMember(Member $member): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.member = :member')
            ->andWhere('r.createdAt >= :expiry')
            ->setParameter('member', $member)
            ->setParameter('expiry', $this->expiryDate())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Reservation[]
     */
    public function findByMemberId(int $memberId): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.book', 'b')->addSelect('b')
            ->andWhere('r.member = :memberId')
            ->andWhere('r.createdAt >= :expiry')
            ->setParameter('memberId', $memberId)
            ->setParameter('expiry', $this->expiryDate())
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
