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

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('idLivre', 'ID')->hideOnForm()->hideOnIndex(),
            TextField::new('titre'),
            DateField::new('dateSortie', 'Date de sortie'),
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
