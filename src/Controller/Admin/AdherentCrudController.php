<?php

namespace App\Controller\Admin;

use App\Entity\Adherent;
use App\Entity\Emprunt;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

class AdherentCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $em;

    public function __construct(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em)
    {
        $this->passwordHasher = $passwordHasher;
        $this->em = $em;
    }

    public static function getEntityFqcn(): string
    {
        return Adherent::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if (!$request) {
            return $qb;
        }

        $status = $request->query->get('status');

        if ($status === 'actifs') {
            $qb->andWhere('entity.estActif = true');
        } elseif ($status === 'suspendus') {
            $qb->andWhere('entity.estActif = false');
        }

        return $qb;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        $photo = TextField::new('photo')->hideOnIndex();
        $dateAdhesion = DateField::new('dateAdhesion', "Date d'adhésion")->hideOnForm()->hideOnIndex();
        
        $estActif = BooleanField::new('estActif', 'Actif')
            ->renderAsSwitch(false)
            ->hideOnForm();

        return [
            // Liste : seulement nom, prénom, email, et le statut
            TextField::new('utilisateur.nom', 'Nom')->hideOnForm(),
            TextField::new('utilisateur.prenom', 'Prénom')->hideOnForm(),
            EmailField::new('utilisateur.email', 'Email')->hideOnForm(),
            $estActif,

            // Formulaire uniquement
            AssociationField::new('utilisateur')
                ->renderAsEmbeddedForm(AdherentUtilisateurCrudController::class)
                ->setLabel(false)
                ->onlyOnForms(),
            DateField::new('dateNaiss', 'Date de naissance')
                ->hideOnIndex()
                ->setFormTypeOption('attr', ['max' => (new \DateTime())->format('Y-m-d')]),
            TextField::new('adressePostale', 'Adresse postale')->hideOnIndex(),
            TelephoneField::new('numTel', 'Téléphone')->hideOnIndex(),
            $photo,
            $dateAdhesion,
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $voirEmprunts = Action::new('voirEmprunts', 'Détails', 'fa fa-eye')
            ->linkToCrudAction('voirEmprunts');

        $request = $this->container->get('request_stack')->getCurrentRequest();
        if (!$request) {
            return $actions
                ->add(Crud::PAGE_INDEX, $voirEmprunts)
                ->setPermission(Action::EDIT, 'ROLE_ADMIN')
                ->setPermission(Action::DELETE, 'ROLE_ADMIN');
        }

        $currentStatus = $request->query->get('status');
        $urlGenerator = $this->container->get(AdminUrlGenerator::class);

        // Bouton TOUT
        $urlTout = $urlGenerator->setController(self::class)->setAction(Action::INDEX)->unset('status')->generateUrl();
        $btnTout = Action::new('filterTout', 'Tous')
            ->createAsGlobalAction()
            ->linkToUrl($urlTout)
            ->setCssClass(!$currentStatus ? 'btn btn-dark' : 'btn btn-light');

        // Bouton ACTIFS
        $urlActifs = $urlGenerator->setController(self::class)->setAction(Action::INDEX)->set('status', 'actifs')->generateUrl();
        $btnActifs = Action::new('filterActifs', 'Actifs')
            ->createAsGlobalAction()
            ->linkToUrl($urlActifs)
            ->setCssClass($currentStatus === 'actifs' ? 'btn btn-success' : 'btn btn-light');

        // Bouton SUSPENDUS
        $urlSuspendus = $urlGenerator->setController(self::class)->setAction(Action::INDEX)->set('status', 'suspendus')->generateUrl();
        $btnSuspendus = Action::new('filterSuspendus', 'Suspendus')
            ->createAsGlobalAction()
            ->linkToUrl($urlSuspendus)
            ->setCssClass($currentStatus === 'suspendus' ? 'btn btn-danger' : 'btn btn-light');

        return $actions
            ->add(Crud::PAGE_INDEX, $voirEmprunts)
            ->add(Crud::PAGE_INDEX, $btnTout)
            ->add(Crud::PAGE_INDEX, $btnActifs)
            ->add(Crud::PAGE_INDEX, $btnSuspendus)
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }

    public function voirEmprunts(AdminContext $context): Response
    {
        $adherentId = $context->getRequest()->query->get('entityId');
        $adherent = $this->em->getRepository(Adherent::class)->find($adherentId);

        if (!$adherent) {
            throw $this->createNotFoundException('Adhérent non trouvé');
        }

        $emprunts = $this->em->getRepository(Emprunt::class)->findBy(['adherent' => $adherent]);

        $urlRetour = $this->container->get(AdminUrlGenerator::class)
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->render('admin/adherent.html.twig', [
            'adherent' => $adherent,
            'emprunts' => $emprunts,
            'urlRetour' => $urlRetour,
        ]);
    }

    public function marquerRendu(AdminContext $context): Response
    {
        $empruntId = $context->getRequest()->query->get('empruntId');
        $emprunt = $this->em->getRepository(Emprunt::class)->find($empruntId);

        if ($emprunt) {
            $emprunt->setDateRetourReel(new \DateTime());
            $this->em->flush();
            $this->addFlash('success', 'Le livre a été marqué comme rendu.');
        }

        // Rediriger vers la page emprunts de l'adhérent
        $url = $this->container->get(AdminUrlGenerator::class)
            ->setController(self::class)
            ->setAction('voirEmprunts')
            ->setEntityId($emprunt->getAdherent()->getIdAdh())
            ->generateUrl();

        return $this->redirect($url);
    }

    public function toggleActif(AdminContext $context): Response
    {
        $adherentId = $context->getRequest()->query->get('entityId');
        $adherent = $this->em->getRepository(Adherent::class)->find($adherentId);

        if (!$adherent) {
            throw $this->createNotFoundException('Adhérent non trouvé');
        }

        // Si on essaie de le suspendre (il est actif), on vérifie d'abord s'il a des emprunts en cours
        if ($adherent->isEstActif()) {
            $empruntsEnCours = $this->em->getRepository(Emprunt::class)->findBy([
                'adherent' => $adherent,
                'dateRetourReel' => null
            ]);

            if (count($empruntsEnCours) > 0) {
                $this->addFlash('danger', sprintf(
                    'Impossible de suspendre cet adhérent car il a encore %d emprunt(s) en cours.',
                    count($empruntsEnCours)
                ));
            } else {
                $adherent->setEstActif(false);
                $this->em->flush();
                $this->addFlash('success', 'Le compte de l\'adhérent a été suspendu.');
            }
        } else {
            // S'il était suspendu, on le réactive sans condition
            $adherent->setEstActif(true);
            $this->em->flush();
            $this->addFlash('success', 'Le compte de l\'adhérent a été réactivé.');
        }

        $url = $this->container->get(AdminUrlGenerator::class)
            ->setController(self::class)
            ->setAction('voirEmprunts')
            ->setEntityId($adherent->getIdAdh())
            ->generateUrl();

        return $this->redirect($url);
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