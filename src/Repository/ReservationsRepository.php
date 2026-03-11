<?php

namespace App\Repository;

use App\Entity\Reservations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservations>
 */
class ReservationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservations::class);
    }

    /**
     * Delete reservations that are older than 7 days.
     * Returns the number of deleted reservations.
     */
    public function deleteExpiredReservations(): int
    {
        $limitDate = new \DateTime();
        $limitDate->modify('-7 days');
        $limitDate->setTime(0, 0, 0);

        return $this->createQueryBuilder('r')
            ->delete()
            ->where('r.dateResa < :limitDate')
            ->setParameter('limitDate', $limitDate)
            ->getQuery()
            ->execute();
    }
}
