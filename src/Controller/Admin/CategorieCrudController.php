<?php

namespace App\Controller\Admin;

use App\Entity\Categorie;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

#[IsGranted('ROLE_ADMIN')]
class CategorieCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Categorie::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $voirLivres = Action::new('voirLivres', 'Détails', 'fa fa-eye')
            ->linkToCrudAction('detailsCategorie');

        return $actions
            ->add(Crud::PAGE_INDEX, $voirLivres)
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }

    public function detailsCategorie(\EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext $context, \Doctrine\ORM\EntityManagerInterface $em): \Symfony\Component\HttpFoundation\Response
    {
        $categorieId = $context->getRequest()->query->get('entityId');
        $categorie = $em->getRepository(Categorie::class)->find($categorieId);

        if (!$categorie) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }

        $livres = $categorie->getLivres();

        $urlRetour = $this->container->get(AdminUrlGenerator::class)
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->render('admin/categorie.html.twig', [
            'categorie' => $categorie,
            'livres' => $livres,
            'urlRetour' => $urlRetour,
        ]);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('idCat', 'ID')->hideOnForm()->hideOnIndex(),
            TextField::new('nom'),
            TextareaField::new('description'),
            AssociationField::new('livres')->hideOnForm(),
        ];
    }
}
