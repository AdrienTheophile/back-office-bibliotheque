<?php

namespace App\Controller\Admin;

use App\Entity\Livre;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class LivreCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Livre::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('idLivre', 'ID')->hideOnForm(),
            TextField::new('titre'),
            DateField::new('dateSortie', 'Date de sortie'),
            TextField::new('langue'),
            TextField::new('photoCouverture'),
            AssociationField::new('auteurs'),
            AssociationField::new('categories', 'Catégories'),
        ];
    }
}
