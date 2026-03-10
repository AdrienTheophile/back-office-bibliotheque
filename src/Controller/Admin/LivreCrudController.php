<?php

namespace App\Controller\Admin;

use App\Entity\Livre;
use App\Entity\Categorie;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_ADMIN')]
class LivreCrudController extends AbstractCrudController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getEntityFqcn(): string
    {
        return Livre::class;
    }

    public function createEntity(string $entityFqcn)
    {
        $livre = new Livre();

        $request = $this->container->get('request_stack')->getCurrentRequest();
        $categorieId = $request ? $request->query->get('categorieId') : null;

        if ($categorieId) {
            $categorie = $this->em->getRepository(Categorie::class)->find($categorieId);
            if ($categorie) {
                $livre->addCategory($categorie);
            }
        }

        return $livre;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if (!$request) {
            return $qb;
        }

        $status = $request->query->get('status');

        if ($status === 'dispo') {
            // Un livre est disponible s'il n'a AUCUN emprunt où dateRetourReel est NULL
            $qb->leftJoin('entity.emprunts', 'e', 'WITH', 'e.dateRetourReel IS NULL')
               ->andWhere('e.idEmp IS NULL');
        } elseif ($status === 'indispo') {
            // Un livre est indisponible s'il a AU MOINS UN emprunt où dateRetourReel est NULL
            $qb->innerJoin('entity.emprunts', 'e', 'WITH', 'e.dateRetourReel IS NULL');
        }

        return $qb;
    }

    public function configureActions(Actions $actions): Actions
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if (!$request) {
            return $actions;
        }

        $currentStatus = $request->query->get('status');
        $urlGenerator = $this->container->get(AdminUrlGenerator::class);

        // Bouton TOUT
        $urlTout = $urlGenerator->setController(self::class)->setAction(Action::INDEX)->unset('status')->generateUrl();
        $btnTout = Action::new('filterTout', 'Tout afficher')
            ->createAsGlobalAction()
            ->linkToUrl($urlTout)
            ->setCssClass(!$currentStatus ? 'btn btn-dark' : 'btn btn-light');

        // Bouton DISPONIBLES
        $urlDispo = $urlGenerator->setController(self::class)->setAction(Action::INDEX)->set('status', 'dispo')->generateUrl();
        $btnDispo = Action::new('filterDispo', 'Disponibles')
            ->createAsGlobalAction()
            ->linkToUrl($urlDispo)
            ->setCssClass($currentStatus === 'dispo' ? 'btn btn-success' : 'btn btn-light');

        // Bouton NON DISPONIBLES
        $urlIndispo = $urlGenerator->setController(self::class)->setAction(Action::INDEX)->set('status', 'indispo')->generateUrl();
        $btnIndispo = Action::new('filterIndispo', 'Non disponibles')
            ->createAsGlobalAction()
            ->linkToUrl($urlIndispo)
            ->setCssClass($currentStatus === 'indispo' ? 'btn btn-warning text-dark' : 'btn btn-light');

        return $actions
            ->add(Crud::PAGE_INDEX, $btnTout)
            ->add(Crud::PAGE_INDEX, $btnDispo)
            ->add(Crud::PAGE_INDEX, $btnIndispo);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('idLivre', 'ID')->hideOnForm()->hideOnIndex(),
            TextField::new('titre'),
            DateField::new('dateSortie', 'Date de sortie')->setFormat('dd/MM/yyyy'),
            TextField::new('langue'),
            TextField::new('photoCouverture')->hideOnIndex(),
            BooleanField::new('disponible', 'Disponible')
                ->renderAsSwitch(false)
                ->hideOnForm(),
            AssociationField::new('auteurs'),
            AssociationField::new('categories', 'Catégories'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('categories');
    }
}
