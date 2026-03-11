<?php

namespace App\Repository;

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

    /**
     * @return Reservation[]
     */
    public function findByMember(Member $member): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.book', 'b')
            ->addSelect('b')
            ->andWhere('r.member = :member')
            ->setParameter('member', $member)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByBookId(int $bookId): ?Reservation
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.book = :bookId')
            ->setParameter('bookId', $bookId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
