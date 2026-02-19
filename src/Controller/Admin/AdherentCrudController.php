<?php

namespace App\Controller\Admin;

use App\Entity\Adherent;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

class AdherentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Adherent::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('idAdh', 'ID')->hideOnForm(),
            TextField::new('nom'),
            TextField::new('prenom', 'Prénom'),
            DateField::new('dateNaiss', 'Date de naissance'),
            EmailField::new('email'),
            TextField::new('adressePostale', 'Adresse postale'),
            TelephoneField::new('numTel', 'Téléphone'),
            TextField::new('photo')->hideOnIndex(),
            DateField::new('dateAdhesion', "Date d'adhésion"),
            AssociationField::new('emprunts')->hideOnForm(),
            AssociationField::new('reservations', 'Réservations')->hideOnForm(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }
}
