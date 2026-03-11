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
            ->andWhere('e.dateRetourReel IS NULL')
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Retourne les IDs des livres non disponibles pour un adhérent spécifique.
     * Un livre est non disponible si:
     * - Il est actuellement emprunté par quelqu'un.
     * - Il est actuellement réservé par quelqu'un d'autre que l'adhérent fourni.
     */
    public function findLivreNotAvailable(?\App\Entity\Adherent $adherent = null): array
    {
        // 1. Livres empruntés
        $empruntes = $this->createQueryBuilder('e')
            ->select('IDENTITY(e.livre) as livreId')
            ->where('e.dateRetourReel IS NULL')
            ->getQuery()
            ->getScalarResult();
            
        $empruntesIds = array_column($empruntes, 'livreId');

        // 2. Livres réservés (par une autre personne)
        $qbResa = $this->getEntityManager()->getRepository(\App\Entity\Reservations::class)->createQueryBuilder('r')
            ->select('IDENTITY(r.livre) as livreId');
            
        if ($adherent) {
            $qbResa->where('r.adherent != :adherent')
                   ->setParameter('adherent', $adherent);
        }
        
        $reserves = $qbResa->getQuery()->getScalarResult();
        $reservesIds = array_column($reserves, 'livreId');

        return array_unique(array_merge($empruntesIds, $reservesIds));
    }

    /**
     * Retourne la liste des livres disponibles pour un adhérent.
     * @return Livre[]
     */
    public function findAvailableLivres(?\App\Entity\Adherent $adherent = null): array
    {
        $unavailableIds = $this->findLivreNotAvailable($adherent);
        
        $qb = $this->getEntityManager()->getRepository(Livre::class)->createQueryBuilder('l');
        
        if (!empty($unavailableIds)) {
            $qb->where($qb->expr()->notIn('l.idLivre', ':unavailableIds'))
               ->setParameter('unavailableIds', $unavailableIds);
        }
        
        return $qb->orderBy('l.titre', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Compte le nombre d'emprunts en cours (non rendus).
     */
    public function countEmpruntsEnCours(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.idEmp)')
            ->where('e.dateRetourReel IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte le nombre d'emprunts terminés (rendus).
     */
    public function countEmpruntsTermines(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.idEmp)')
            ->where('e.dateRetourReel IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte le nombre d'emprunts en retard (non rendus et date de retour dépassée).
     */
    public function countEmpruntsEnRetard(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.idEmp)')
            ->where('e.dateRetourReel IS NULL')
            ->andWhere('e.dateRetour < :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Retourne les livres les plus empruntés.
     * @return array<array{titre: string, total: int}>
     */
    public function getTopLivresEmpruntes(int $limit = 5): array
    {
        return $this->createQueryBuilder('e')
            ->select('l.titre AS titre, COUNT(e.idEmp) AS total')
            ->join('e.livre', 'l')
            ->groupBy('l.idLivre, l.titre')
            ->orderBy('total', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les adhérents qui ont le plus d'emprunts.
     * @return array<array{nom: string, prenom: string, total: int}>
     */
    public function getTopAdherentsEmprunteurs(int $limit = 5): array
    {
        return $this->createQueryBuilder('e')
            ->select('u.nom AS nom, u.prenom AS prenom, COUNT(e.idEmp) AS total')
            ->join('e.adherent', 'a')
            ->join('a.utilisateur', 'u')
            ->groupBy('a.idAdh, u.nom, u.prenom')
            ->orderBy('total', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }


    /**
     * Retourne la durée moyenne d'un emprunt terminé en jours.
     */
    public function getDureeMoyenneEmprunt(): ?float
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT AVG(DATEDIFF(date_retour_reel, date_emprunt)) AS duree_moyenne
            FROM emprunt
            WHERE date_retour_reel IS NOT NULL
        ";

        $result = $conn->executeQuery($sql)->fetchAssociative();

        return $result ? round((float) $result['duree_moyenne'], 1) : null;
    }
}
