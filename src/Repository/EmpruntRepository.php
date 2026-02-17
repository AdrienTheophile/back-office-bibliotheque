<?php

namespace App\Repository;

use App\Entity\Emprunt;
use App\Entity\Livre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Emprunt>
 */
class EmpruntRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Emprunt::class);
    }

    /**
     * Compte le nombre d'emprunts en cours pour un adhérent.
     * Un emprunt est "en cours" si la date de retour est dans le futur.
     */
    public function countEmpruntByAdherent(\App\Entity\Adherent $adherent): int
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(e.idEmp)')
            ->where('e.adherent = :adherent')
            ->andWhere('e.dateRetour >= :today')
            ->setParameter('adherent', $adherent)
            ->setParameter('today', new \DateTime('today'))
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Retourne les IDs des livres actuellement empruntés 
     * On ne peut pas les umpruntés s'il ne sont pas disponibles 
     */
    public function findLivreNotAvailable(): array
    {
        $results = $this->createQueryBuilder('e')
            ->select('IDENTITY(e.livre) as livreId')
            ->where('e.dateRetour >= :today')
            ->setParameter('today', new \DateTime('today'))
            ->getQuery()
            ->getScalarResult();

        return array_column($results, 'livreId');
    }

    /**
     * Retourne la liste des livres disponibles.
     * @return Livre[]
     */
    public function findAvailableLivres(): array
    {
        $unavailableIds = $this->findLivreNotAvailable();
        
        $qb = $this->getEntityManager()->getRepository(Livre::class)->createQueryBuilder('l');
        
        if (!empty($unavailableIds)) {
            $qb->where($qb->expr()->notIn('l.idLivre', ':unavailableIds'))
               ->setParameter('unavailableIds', $unavailableIds);
        }
        
        return $qb->orderBy('l.titre', 'ASC')
                  ->getQuery()
                  ->getResult();
    }
}
