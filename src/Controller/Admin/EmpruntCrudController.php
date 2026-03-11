<?php

namespace App\Controller\Admin;

use App\Entity\Emprunt;
use App\Entity\Adherent;
use App\Repository\EmpruntRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;

class EmpruntCrudController extends AbstractCrudController
{
    public function __construct(
        private EmpruntRepository $empruntRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Emprunt::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if (!$request) {
            return $qb;
        }

        $status = $request->query->get('status');

        if ($status === 'en_cours') {
            $qb->andWhere('entity.dateRetourReel IS NULL');
        } elseif ($status === 'rendus') {
            $qb->andWhere('entity.dateRetourReel IS NOT NULL');
        } elseif ($status === 'retard') {
            $qb->andWhere('entity.dateRetourReel IS NULL')
               ->andWhere('entity.dateRetour < :now')
               ->setParameter('now', new \DateTime());
        }

        return $qb;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets->addJsFile('js/admin_emprunt.js');
    }

    public function configureActions(Actions $actions): Actions
    {
        // Action existante pour marquer comme rendu
        $returnBook = Action::new('returnBook', 'Marquer comme rendu', 'fa fa-check')
            ->linkToCrudAction('rendreLivre')
            ->displayIf(static function ($entity) {
                return $entity->getDateRetourReel() === null;
            });

        // Boutons de filtres
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if (!$request) {
            return $actions->add(Crud::PAGE_INDEX, $returnBook);
        }

        $currentStatus = $request->query->get('status');
        $urlGenerator = $this->container->get(AdminUrlGenerator::class);

        // Bouton TOUT
        $urlTout = $urlGenerator->setController(self::class)->setAction(Action::INDEX)->unset('status')->generateUrl();
        $btnTout = Action::new('filterTout', 'Tout afficher')
            ->createAsGlobalAction()
            ->linkToUrl($urlTout)
            ->setCssClass(!$currentStatus ? 'btn btn-dark' : 'btn btn-light');

        // Bouton EN COURS
        $urlEnCours = $urlGenerator->setController(self::class)->setAction(Action::INDEX)->set('status', 'en_cours')->generateUrl();
        $btnEnCours = Action::new('filterEnCours', 'En cours')
            ->createAsGlobalAction()
            ->linkToUrl($urlEnCours)
            ->setCssClass($currentStatus === 'en_cours' ? 'btn btn-dark' : 'btn btn-light');

        // Bouton RETARDS
        $urlRetard = $urlGenerator->setController(self::class)->setAction(Action::INDEX)->set('status', 'retard')->generateUrl();
        $btnRetard = Action::new('filterRetard', 'En retard')
            ->createAsGlobalAction()
            ->linkToUrl($urlRetard)
            ->setCssClass($currentStatus === 'retard' ? 'btn btn-danger' : 'btn btn-light');
            
        // Bouton RENDUS
        $urlRendus = $urlGenerator->setController(self::class)->setAction(Action::INDEX)->set('status', 'rendus')->generateUrl();
        $btnRendus = Action::new('filterRendus', 'Terminés')
            ->createAsGlobalAction()
            ->linkToUrl($urlRendus)
            ->setCssClass($currentStatus === 'rendus' ? 'btn btn-dark' : 'btn btn-light');

        return $actions
            ->add(Crud::PAGE_INDEX, $returnBook)
            ->add(Crud::PAGE_INDEX, $btnTout)
            ->add(Crud::PAGE_INDEX, $btnEnCours)
            ->add(Crud::PAGE_INDEX, $btnRetard)
            ->add(Crud::PAGE_INDEX, $btnRendus)
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }

    public function rendreLivre(AdminContext $context, EntityManagerInterface $entityManager): Response
    {
        $id = $context->getRequest()->query->get('entityId');
        $emprunt = $entityManager->getRepository(Emprunt::class)->find($id);

        if (!$emprunt) {
            throw $this->createNotFoundException('Emprunt non trouvé');
        }

        $emprunt->setDateRetourReel(new \DateTime());
        
        $entityManager->flush();

        $this->addFlash('success', 'Le livre a été marqué comme rendu.');

        $url = $this->container->get(AdminUrlGenerator::class)
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureFields(string $pageName): iterable
    {
        // Vérifier si l'adhérent est pré-sélectionné (depuis la page adhérent)
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $adherentId = $request ? $request->query->get('adherentId') : null;
        $adherent = null;
        
        if ($adherentId) {
            $adherent = $this->em->getRepository(Adherent::class)->find($adherentId);
        }

        // Récupérer tous les livres disponibles (non empruntés et non réservés par quelqu'un d'autre)
        $livresDisponibles = $this->empruntRepository->findAvailableLivres($adherent);
        
        // Créer un tableau [titre => objet Livre] pour le ChoiceField
        $choixLivres = [];
        foreach ($livresDisponibles as $livre) {
            $titre = $livre->getTitre();
            if (array_key_exists($titre, $choixLivres)) {
                $titre .= ' (ID: ' . $livre->getIdLivre() . ')';
            }
            $choixLivres[$titre] = $livre;
        }

        $maxSelection = 5;

        if ($adherent) {
            // Remove closed loans from count 
            $activeCount = $this->empruntRepository->countEmpruntByAdherent($adherent);
            $maxSelection = max(0, 5 - $activeCount);
        }

        // selection de
        $livresField = ChoiceField::new('livresEmpruntes', "Livres à emprunter (Max: $maxSelection)")
            ->setChoices($choixLivres)
            ->allowMultipleChoices()
            ->setRequired(true)
            ->hideOnIndex()
            ->hideWhenUpdating()
            ->setFormTypeOption('attr', [
                'data-max-selection' => $maxSelection,
            ]);

        $adherentField = AssociationField::new('adherent', 'Adhérent');
        if ($adherentId) {
            $adherentField->setFormTypeOption('disabled', true);
        }

        $dateEmprunt = DateField::new('dateEmprunt', "Date d'emprunt")->setFormat('dd/MM/yyyy');
        $dateRetour = DateField::new('dateRetour', 'Date limite retour')->setFormat('dd/MM/yyyy');
        $dateRetourReel = DateField::new('dateRetourReel', 'Date de retour effectif')->setFormat('dd/MM/yyyy');

        if ($pageName === Crud::PAGE_NEW) {
            $dateEmprunt->setFormTypeOption('data', new \DateTime())->setFormTypeOption('disabled', true);
            $dateRetour->setFormTypeOption('data', new \DateTime('+15 days'))->setFormTypeOption('disabled', true);
            $dateRetourReel->hideOnForm();
        } elseif ($pageName === Crud::PAGE_EDIT) {
            $dateEmprunt->setRequired(true);
            $dateRetour->setRequired(true);
            $dateRetourReel->setRequired(false);
        } else {
            $dateRetourReel->hideOnForm();
        }

        return [
            IdField::new('idEmp', 'ID')->hideOnForm()->hideOnIndex(),
            $adherentField,
            AssociationField::new('livre')
                ->hideOnForm()
                ->setLabel('Livre'),
            $dateEmprunt->hideOnIndex(),
            $dateRetour->hideOnIndex(),
            $dateRetourReel->hideOnIndex(),
            BooleanField::new('enRetard', 'En retard')
                ->renderAsSwitch(false)
                ->hideOnForm(),

            // champ multiple pour remplacer le champ de livre de base
            $livresField,
        ];
    }

    public function configureFilters(Filters $filters): Filters 
    {
        return $filters
            ->add('adherent')
            ->add('dateEmprunt')
            ->add('dateRetour')
            ->add('dateRetourReel');
    }

    public function createEntity(string $entityFqcn)
    {
        $emprunt = new Emprunt();

        $request = $this->container->get('request_stack')->getCurrentRequest();
        $adherentId = $request ? $request->query->get('adherentId') : null;

        if ($adherentId) {
            $adherent = $this->em->getRepository(Adherent::class)->find($adherentId);
            if ($adherent) {
                $emprunt->setAdherent($adherent);
            }
        }

        return $emprunt;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Si l'adhérent a été pré-sélectionné (champ disabled ne soumet pas la valeur)
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $adherentId = $request ? $request->query->get('adherentId') : null;
        if ($adherentId && !$entityInstance->getAdherent()) {
            $adherent = $entityManager->getRepository(Adherent::class)->find($adherentId);
            if ($adherent) {
                $entityInstance->setAdherent($adherent);
            }
        }

        
        $adherent = $entityInstance->getAdherent();

        // Vérification de la suspension
        if (!$adherent->isEstActif()) {
            $this->addFlash('danger', sprintf(
                'Impossible d\'enregistrer cet emprunt : Le compte de l\'adhérent "%s" est actuellement suspendu.',
                $adherent
            ));
            
            return; // dans ce cas on ne change rien
        }

        $livresSelectionnes = $entityInstance->getLivresEmpruntes();

        // Vérification limite de 5 emprunts en simultanés
        $activeCount = $this->empruntRepository->countEmpruntByAdherent($adherent);
        $totalTarget = $activeCount + count($livresSelectionnes);

        if ($totalTarget > 5) {
            $this->addFlash('danger', sprintf(
                '%s a déjà %d emprunt(s) en cours. Impossible d\'en ajouter %d de plus (Limite : 5).',
                $adherent,
                $activeCount,
                count($livresSelectionnes)
            ));
            
            return; //dans ce cas on ne change rien 
        }

        // Création des emprunts

        // 1er livre assigné à l'entité ($entityInstance) d'EasyAdmin
        $premierLivre = array_shift($livresSelectionnes);
        $entityInstance->setLivre($premierLivre);

        // Forcer les dates, car les champs désactivés dans le form ne sont pas renvoyés
        $dateEmprunt = new \DateTime();
        $dateRetour = (clone $dateEmprunt)->modify('+15 days');
        $entityInstance->setDateEmprunt($dateEmprunt);
        $entityInstance->setDateRetour($dateRetour);
        
        // On persiste l'entité principale
        parent::persistEntity($entityManager, $entityInstance);

        // on crée les autres emprunts
        foreach ($livresSelectionnes as $autreLivre) {
            $nouvelEmprunt = new Emprunt();
            $nouvelEmprunt->setAdherent($adherent);
            $nouvelEmprunt->setLivre($autreLivre); // Un emprunt = Un livre
            $nouvelEmprunt->setDateEmprunt($dateEmprunt);
            $nouvelEmprunt->setDateRetour($dateRetour);
            
            $entityManager->persist($nouvelEmprunt);
        }
        
        // on s'assure d'envoyer les infos
        $entityManager->flush();

        $this->addFlash('success', sprintf('%d emprunt(s) créé(s) avec succès.', count($livresSelectionnes) + 1));
    }

    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $request = $context->getRequest();
        $adherentId = $request->query->get('adherentId');

        if ($adherentId) {
            $url = $this->container->get(AdminUrlGenerator::class)
                ->setController(AdherentCrudController::class)
                ->setAction('voirEmprunts')
                ->setEntityId($adherentId)
                ->generateUrl();

            return $this->redirect($url);
        }

        return parent::getRedirectResponseAfterSave($context, $action);
    }
}
