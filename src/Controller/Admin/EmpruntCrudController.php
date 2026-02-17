<?php

namespace App\Controller\Admin;

use App\Entity\Emprunt;
use App\Repository\EmpruntRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

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

    public function configureFields(string $pageName): iterable
    {
        // Récupérer les IDs des livres non disponibles pour filtrer le champ
        $unavailableIds = $this->empruntRepository->findLivreNotAvailable();

        // Récupérer tous les livres disponibles (non empruntés)
        $livresDisponibles = $this->empruntRepository->findAvailableLivres();
        
        // Créer un tableau [titre => objet Livre] pour le ChoiceField
        $choixLivres = [];
        foreach ($livresDisponibles as $livre) {
            $titre = $livre->getTitre();
            // Gestion basique des doublons de titres
            if (array_key_exists($titre, $choixLivres)) {
                $titre .= ' (ID: ' . $livre->getIdLivre() . ')';
            }
            $choixLivres[$titre] = $livre;
        }

        // Champ pour la création (sélection multiple)
        $livresField = \EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField::new('livresEmpruntes', 'Livres à emprunter')
            ->setChoices($choixLivres)
            ->allowMultipleChoices()
            ->setRequired(true)
            ->hideOnIndex()
            ->hideWhenUpdating();

        return [
            IdField::new('idEmp', 'ID')->hideOnForm(),
            AssociationField::new('adherent', 'Adhérent'),
            
            // Champ standard (affichage/édition)
            AssociationField::new('livre')
                ->hideOnForm() // On le cache à la création car on utilise la sélection multiple
                ->setLabel('Livre'),

            // Notre champ multiple (création seulement)
            $livresField,

            DateField::new('dateEmprunt', "Date d'emprunt")->setFormTypeOption('data', new \DateTime()),
            DateField::new('dateRetour', 'Date de retour')->setFormTypeOption('data', new \DateTime('+30 days')),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var Emprunt $entityInstance */
        $adherent = $entityInstance->getAdherent();
        $livresSelectionnes = $entityInstance->getLivresEmpruntes();

        // Si on n'a pas sélectionné de livres (cas rare si required=true), on laisse faire EasyAdmin qui plantera
        if (empty($livresSelectionnes)) {
            parent::persistEntity($entityManager, $entityInstance);
            return;
        }

        // Vérification limite de 5 emprunts
        $activeCount = $this->empruntRepository->countEmpruntByAdherent($adherent);
        $totalTarget = $activeCount + count($livresSelectionnes);

        if ($totalTarget > 5) {
            $this->addFlash('danger', sprintf(
                '%s a déjà %d emprunt(s) en cours. Impossible d\'en ajouter %d de plus (Limite : 5).',
                $adherent,
                $activeCount,
                count($livresSelectionnes)
            ));
            // On ne persiste rien
            return;
        }

        // Création des emprunts
        // 1. Le premier livre est assigné à l'entité courante ($entityInstance) gérée par EasyAdmin
        $premierLivre = array_shift($livresSelectionnes);
        $entityInstance->setLivre($premierLivre);
        
        // On persiste l'entité principale
        parent::persistEntity($entityManager, $entityInstance);

        // 2. On crée manuellement les autres emprunts
        foreach ($livresSelectionnes as $autreLivre) {
            $nouvelEmprunt = new Emprunt();
            $nouvelEmprunt->setAdherent($adherent);
            $nouvelEmprunt->setLivre($autreLivre); // Un emprunt = Un livre
            $nouvelEmprunt->setDateEmprunt($entityInstance->getDateEmprunt());
            $nouvelEmprunt->setDateRetour($entityInstance->getDateRetour());
            
            $entityManager->persist($nouvelEmprunt);
        }
        
        // Le flush final sera fait par EasyAdmin ou on peut le forcer ici si besoin, 
        // mais EasyAdmin flush à la fin de l'action 'new'.
        // Cependant, comme on a ajouté des objets manuellement, il vaut mieux s'assurer qu'ils partent.
        $entityManager->flush();

        $this->addFlash('success', sprintf('%d emprunt(s) créé(s) avec succès.', count($livresSelectionnes) + 1));
    }
}
