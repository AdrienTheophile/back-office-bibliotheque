<?php

namespace App\Controller\Admin;

use App\Entity\Adherent;
use App\Entity\Emprunt;
use App\Entity\Reservations;
use App\Entity\Utilisateur;
use App\Repository\EmpruntRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
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
        $photoForm = TextField::new('photo', 'URL de la photo')->onlyOnForms();
        $photo = ImageField::new('photo', 'Photo')
            ->setBasePath('')
            ->hideOnForm()
            ->hideOnIndex();
        $dateAdhesion = DateField::new('dateAdhesion', "Date d'adhésion")
            ->setFormat('dd/MM/yyyy')
            ->hideOnForm()
            ->hideOnIndex();
        
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
                ->setFormat('dd/MM/yyyy')
                ->hideOnIndex()
                ->setFormTypeOption('attr', ['max' => (new \DateTime())->format('Y-m-d')]),
            TextField::new('adressePostale', 'Adresse postale')->hideOnIndex(),
            TelephoneField::new('numTel', 'Téléphone')->hideOnIndex(),
            $photoForm,
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
            ->setEntityId($emprunt ? $emprunt->getAdherent()->getIdAdh() : null)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function emprunterReservation(AdminContext $context, EmpruntRepository $empruntRepository): Response
    {
        $reservationId = $context->getRequest()->query->get('reservationId');
        $reservation = $this->em->getRepository(Reservations::class)->find($reservationId);

        if (!$reservation) {
            $this->addFlash('danger', 'Réservation introuvable.');
        } else {
            $adherent = $reservation->getAdherent();
            $livre = $reservation->getLivre();

            // Vérification de la suspension
            if (!$adherent->isEstActif()) {
                $this->addFlash('danger', 'Impossible d\'emprunter ce livre : Le compte est suspendu.');
            } else {
                // Vérification de la limite
                $activeCount = $empruntRepository->countEmpruntByAdherent($adherent);
                if ($activeCount >= 5) {
                    $this->addFlash('danger', sprintf(
                        'Cet adhérent a déjà %d emprunt(s) en cours. Impossible d\'emprunter plus (Limite : 5).',
                        $activeCount
                    ));
                } else {
                    $emprunt = new Emprunt();
                    $dateEmprunt = new \DateTime();
                    $dateRetour = (clone $dateEmprunt)->modify('+15 days');

                    $emprunt->setAdherent($adherent);
                    $emprunt->setLivre($livre);
                    $emprunt->setDateEmprunt($dateEmprunt);
                    $emprunt->setDateRetour($dateRetour);

                    $this->em->persist($emprunt);
                    $this->em->remove($reservation); // Delete the reservation
                    $this->em->flush();

                    $this->addFlash('success', 'La réservation a été transformée en emprunt.');
                }
            }
        }

        // Rediriger vers la page emprunts de l'adhérent
        $url = $this->container->get(AdminUrlGenerator::class)
            ->setController(self::class)
            ->setAction('voirEmprunts')
            ->setEntityId($adherent ? $adherent->getIdAdh() : null)
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
                
                if ($utilisateur->getPlainPassword()) {
                    $utilisateur->setPassword(
                        $this->passwordHasher->hashPassword($utilisateur, $utilisateur->getPlainPassword())
                    );
                    $utilisateur->eraseCredentials();
                }
            }
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Adherent && $entityInstance->getUtilisateur()) {
            $utilisateur = $entityInstance->getUtilisateur();
            $utilisateur->setRoles(['ROLE_ADHERENT']);
            
            if ($utilisateur->getPlainPassword()) {
                $utilisateur->setPassword(
                    $this->passwordHasher->hashPassword($utilisateur, $utilisateur->getPlainPassword())
                );
                $utilisateur->eraseCredentials();
            }
        }
        parent::updateEntity($entityManager, $entityInstance);
    }
}