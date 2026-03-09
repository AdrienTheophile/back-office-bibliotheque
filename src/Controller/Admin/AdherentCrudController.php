<?php

namespace App\Controller\Admin;

use App\Entity\Adherent;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdherentCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public static function getEntityFqcn(): string
    {
        return Adherent::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('idAdh', 'ID')->hideOnForm(),
            AssociationField::new('utilisateur')->renderAsEmbeddedForm(AdherentUtilisateurCrudController::class)->setLabel(false)->onlyOnForms(),
            TextField::new('utilisateur.nom', 'Nom')->hideOnForm(),
            TextField::new('utilisateur.prenom', 'Prénom')->hideOnForm(),
            EmailField::new('utilisateur.email', 'Email')->hideOnForm(),
            DateField::new('dateNaiss', 'Date de naissance')
                ->setFormTypeOption('attr', ['max' => (new \DateTime())->format('Y-m-d')]),
            TextField::new('adressePostale', 'Adresse postale'),
            TelephoneField::new('numTel', 'Téléphone'),
            TextField::new('photo')->hideOnIndex(),
            DateField::new('dateAdhesion', "Date d'adhésion")->hideOnForm(),
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

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Adherent) {
            $entityInstance->setDateAdhesion(new \DateTime());

            if ($entityInstance->getUtilisateur()) {
                $utilisateur = $entityInstance->getUtilisateur();
                $utilisateur->setRoles(['ROLE_ADHERENT']);
                $utilisateur->setPassword(
                    $this->passwordHasher->hashPassword($utilisateur, $utilisateur->getPassword())
                );
            }
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Adherent && $entityInstance->getUtilisateur()) {
            $utilisateur = $entityInstance->getUtilisateur();
            $utilisateur->setRoles(['ROLE_ADHERENT']);
            $utilisateur->setPassword(
                $this->passwordHasher->hashPassword($utilisateur, $utilisateur->getPassword())
            );
        }
        parent::updateEntity($entityManager, $entityInstance);
    }
}