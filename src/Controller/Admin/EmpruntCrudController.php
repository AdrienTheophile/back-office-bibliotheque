<?php

namespace App\Controller\Admin;

use App\Entity\Emprunt;
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

class EmpruntCrudController extends AbstractCrudController
{
    public function __construct(
        private EmpruntRepository $empruntRepository,
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
            ->add(Crud::PAGE_INDEX, $btnRendus);
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

        // Récupérer tous les livres disponibles 
        $livresDisponibles = $this->empruntRepository->findAvailableLivres();
        
        // Créer un tableau [titre => objet Livre] pour le ChoiceField
        $choixLivres = [];
        foreach ($livresDisponibles as $livre) {
            $titre = $livre->getTitre();
            if (array_key_exists($titre, $choixLivres)) {
                $titre .= ' (ID: ' . $livre->getIdLivre() . ')';
            }
            $choixLivres[$titre] = $livre;
        }

        // selection de
        $livresField = ChoiceField::new('livresEmpruntes', 'Livres à emprunter')
            ->setChoices($choixLivres)
            ->allowMultipleChoices()
            ->setRequired(true)
            ->hideOnIndex()
            ->hideWhenUpdating();

        return [
            IdField::new('idEmp', 'ID')->hideOnForm(),
            AssociationField::new('adherent', 'Adhérent'),
            AssociationField::new('livre')
                ->hideOnForm() 
                ->setLabel('Livre'),

            // champ multiple pour remplacer le champ de livre de base 
            $livresField,

            DateField::new('dateEmprunt', "Date d'emprunt")->setFormTypeOption('data', new \DateTime()),
            DateField::new('dateRetour', 'Date limite retour')->setFormTypeOption('data', new \DateTime('+15 days')), //15 jours avant de considerer comme un retard
            
            DateField::new('dateRetourReel', 'Date de retour effectif')
                ->setFormat('dd/MM/yyyy')
                ->hideOnForm(), // On gère le retour via une action, ou on l'affiche seulement en lecture

            BooleanField::new('enRetard', 'En retard')
                ->renderAsSwitch(false)
                ->hideOnForm(),
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

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        
        $adherent = $entityInstance->getAdherent();
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
        
        // On persiste l'entité principale
        parent::persistEntity($entityManager, $entityInstance);

        // on crée les autres emprunts
        foreach ($livresSelectionnes as $autreLivre) {
            $nouvelEmprunt = new Emprunt();
            $nouvelEmprunt->setAdherent($adherent);
            $nouvelEmprunt->setLivre($autreLivre); // Un emprunt = Un livre
            $nouvelEmprunt->setDateEmprunt($entityInstance->getDateEmprunt());
            $nouvelEmprunt->setDateRetour($entityInstance->getDateRetour());
            
            $entityManager->persist($nouvelEmprunt);
        }
        
        // on s'assure d'envoyer les infos
        $entityManager->flush();

        $this->addFlash('success', sprintf('%d emprunt(s) créé(s) avec succès.', count($livresSelectionnes) + 1));
    }
}
