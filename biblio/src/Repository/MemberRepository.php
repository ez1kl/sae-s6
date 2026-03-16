<?php

namespace App\Repository;

use App\Entity\Member;
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
}
