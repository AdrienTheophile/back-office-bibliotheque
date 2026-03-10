<?php

namespace App\Controller\Admin;

use App\Entity\Utilisateur;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

/**
 * Utilisé uniquement comme formulaire embarqué dans AdherentCrudController.
 * Le rôle ROLE_ADHERENT est pré-coché et le champ est désactivé.
 */
class AdherentUtilisateurCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Utilisateur::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            EmailField::new('email'),
            TextField::new('nom'),
            TextField::new('prenom', 'Prénom'),
            TextField::new('plainPassword', 'Mot de passe')
                ->onlyOnForms()
                ->setRequired($pageName === Crud::PAGE_NEW),
            ChoiceField::new('roles')
                ->setChoices([
                    'Adhérent' => 'ROLE_ADHERENT',
                ])
                ->allowMultipleChoices()
                ->renderExpanded()
                ->setFormTypeOption('data', ['ROLE_ADHERENT'])
                ->setFormTypeOption('disabled', true),
        ];
    }
}
