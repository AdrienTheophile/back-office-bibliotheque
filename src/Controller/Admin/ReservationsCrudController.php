<?php

namespace App\Controller\Admin;

use App\Entity\Reservations;
use App\Repository\EmpruntRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class ReservationsCrudController extends AbstractCrudController
{
    public function __construct(
        private EmpruntRepository $empruntRepository
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Reservations::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('idResa', 'ID')->hideOnForm(),
            AssociationField::new('adherent', 'Adhérent'),
            AssociationField::new('livre')
                ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                    $context = $this->getContext();
                    $entity = $context ? $context->getEntity()->getInstance() : null;
                    $adherent = $entity instanceof Reservations ? $entity->getAdherent() : null;

                    $unavailableIds = $this->empruntRepository->findLivreNotAvailable($adherent);
                    if (!empty($unavailableIds)) {
                        $alias = $queryBuilder->getRootAliases()[0];
                        $queryBuilder->where($queryBuilder->expr()->notIn($alias.'.idLivre', ':unavailableIds'))
                                     ->setParameter('unavailableIds', $unavailableIds);
                    }
                    return $queryBuilder;
                }),
            DateField::new('dateResa', 'Date de réservation'),
        ];
    }
}
