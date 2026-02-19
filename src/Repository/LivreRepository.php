<?php

namespace App\Repository;

use App\Entity\Livre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Livre>
 */
class LivreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livre::class);
    }

    //    /**
    //     * @return Livre[] Returns an array of Livre objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Livre
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    /**
     * Récupère les livres avec pagination
     */
    public function findPaginated(int $page, int $limit): array
    {
        $limit = abs($limit);
        $result = [];

        $query = $this->createQueryBuilder('l')
            ->setMaxResults($limit)
            ->setFirstResult(($page * $limit) - $limit);

        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();

        if (empty($data)) {
            return $result;
        }

        $pages = ceil($paginator->count() / $limit);

        $result['items'] = $data;
        $result['total'] = $paginator->count();
        $result['pages'] = $pages;
        $result['page'] = $page;
        $result['limit'] = $limit;

        return $result;
    }

    /**
     * Recherche avancée de livres via critères combinables
     */
    public function findByAdvancedSearch(array $params): array
    {
        $qb = $this->createQueryBuilder('l');

        if (!empty($params['titre'])) {
            $qb->andWhere('l.titre LIKE :titre')
                ->setParameter('titre', '%' . $params['titre'] . '%');
        }

        if (!empty($params['auteur'])) {
            $qb->innerJoin('l.auteurs', 'a')
                ->andWhere('a.idAut = :auteur')
                ->setParameter('auteur', $params['auteur']);
        }

        if (!empty($params['categorie'])) {
            $qb->innerJoin('l.categories', 'c')
                ->andWhere('c.idCat = :categorie')
                ->setParameter('categorie', $params['categorie']);
        }

        if (!empty($params['langue'])) {
            $qb->andWhere('l.langue = :langue')
                ->setParameter('langue', $params['langue']);
        }

        if (!empty($params['dateMin'])) {
            $qb->andWhere('l.dateSortie >= :dateMin')
                ->setParameter('dateMin', $params['dateMin']);
        }

        if (!empty($params['dateMax'])) {
            $qb->andWhere('l.dateSortie <= :dateMax')
                ->setParameter('dateMax', $params['dateMax']);
        }

        return $qb->getQuery()->getResult();
    }
}
