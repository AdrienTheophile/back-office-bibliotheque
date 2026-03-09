<?php

namespace App\DataFixtures;

use App\Entity\Adherent;
use App\Entity\Auteur;
use App\Entity\Categorie;
use App\Entity\Livre;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // =====================================================================
        // 10 Catégories (noms réalistes de bibliothèque)
        // =====================================================================
        $categoriesData = [
            ['Roman', 'Romans classiques et contemporains de la littérature française et internationale.'],
            ['Science-Fiction', 'Œuvres imaginant des futurs possibles, des voyages spatiaux et des technologies avancées.'],
            ['Policier', 'Enquêtes, mystères et suspense au cœur de récits captivants.'],
            ['Fantastique', 'Mondes magiques, créatures surnaturelles et aventures extraordinaires.'],
            ['Biographie', 'Récits de vie de personnalités historiques, artistiques ou scientifiques.'],
            ['Histoire', 'Ouvrages retraçant les grands événements et périodes de l\'histoire mondiale.'],
            ['Philosophie', 'Réflexions sur l\'existence, la morale, la connaissance et la société.'],
            ['Poésie', 'Recueils de poèmes et œuvres en vers de toutes époques.'],
            ['Jeunesse', 'Livres destinés aux enfants et aux adolescents.'],
            ['Sciences', 'Ouvrages de vulgarisation scientifique et découvertes majeures.'],
        ];

        $categories = [];
        foreach ($categoriesData as [$nom, $description]) {
            $categorie = new Categorie();
            $categorie->setNom($nom);
            $categorie->setDescription($description);
            $manager->persist($categorie);
            $categories[] = $categorie;
        }

        // =====================================================================
        // 10 Auteurs (écrivains francophones réalistes)
        // =====================================================================
        $auteursData = [
            ['Hugo', 'Victor', '1802-02-26', '1885-05-22', 'Française', 'Poète, dramaturge et romancier, considéré comme l\'un des plus grands écrivains de la langue française.'],
            ['Camus', 'Albert', '1913-11-07', '1960-01-04', 'Française', 'Écrivain, philosophe et journaliste, prix Nobel de littérature en 1957.'],
            ['Zola', 'Émile', '1840-04-02', '1902-09-29', 'Française', 'Chef de file du naturalisme, auteur de la fresque des Rougon-Macquart.'],
            ['Dumas', 'Alexandre', '1802-07-24', '1870-12-05', 'Française', 'Auteur prolifique de romans historiques et d\'aventures.'],
            ['Flaubert', 'Gustave', '1821-12-12', '1880-05-08', 'Française', 'Maître du réalisme, perfectionniste du style littéraire.'],
            ['Beauvoir', 'Simone', '1908-01-09', '1986-04-14', 'Française', 'Philosophe, romancière et essayiste, figure majeure du féminisme.'],
            ['Verne', 'Jules', '1828-02-08', '1905-03-24', 'Française', 'Précurseur de la science-fiction, auteur des Voyages extraordinaires.'],
            ['Prévert', 'Jacques', '1900-02-04', '1977-04-11', 'Française', 'Poète et scénariste, connu pour ses recueils Paroles et Histoires.'],
            ['Sagan', 'Françoise', '1935-06-21', '2004-09-24', 'Française', 'Romancière célèbre pour Bonjour tristesse, écrit à 18 ans.'],
            ['Proust', 'Marcel', '1871-07-10', '1922-11-18', 'Française', 'Auteur d\'À la recherche du temps perdu, monument de la littérature.'],
        ];

        $auteurs = [];
        foreach ($auteursData as [$nom, $prenom, $naissance, $deces, $nationalite, $description]) {
            $auteur = new Auteur();
            $auteur->setNom($nom);
            $auteur->setPrenom($prenom);
            $auteur->setDateNaissance(new \DateTime($naissance));
            $auteur->setDateDeces($deces ? new \DateTime($deces) : null);
            $auteur->setNationalite($nationalite);
            $auteur->setDescription($description);
            $manager->persist($auteur);
            $auteurs[] = $auteur;
        }

        // =====================================================================
        // 50 Livres (titres réalistes en français)
        // =====================================================================
        $livresData = [
            // Victor Hugo
            ['Les Misérables', '1862-04-03', 'Français', [0], [0]],
            ['Notre-Dame de Paris', '1831-03-16', 'Français', [0], [0, 3]],
            ['Les Contemplations', '1856-04-23', 'Français', [0], [7]],
            ['Les Travailleurs de la mer', '1866-03-12', 'Français', [0], [0]],
            ['L\'Homme qui rit', '1869-04-01', 'Français', [0], [0, 3]],
            // Albert Camus
            ['L\'Étranger', '1942-06-15', 'Français', [1], [0, 6]],
            ['La Peste', '1947-06-10', 'Français', [1], [0]],
            ['Le Mythe de Sisyphe', '1942-10-16', 'Français', [1], [6]],
            ['La Chute', '1956-05-25', 'Français', [1], [0, 6]],
            ['L\'Homme révolté', '1951-10-18', 'Français', [1], [6]],
            // Émile Zola
            ['Germinal', '1885-03-02', 'Français', [2], [0, 5]],
            ['L\'Assommoir', '1877-01-01', 'Français', [2], [0]],
            ['Nana', '1880-03-15', 'Français', [2], [0]],
            ['Au Bonheur des Dames', '1883-03-05', 'Français', [2], [0]],
            ['La Bête humaine', '1890-03-02', 'Français', [2], [0, 2]],
            // Alexandre Dumas
            ['Le Comte de Monte-Cristo', '1844-08-28', 'Français', [3], [0, 5]],
            ['Les Trois Mousquetaires', '1844-03-14', 'Français', [3], [0, 5]],
            ['Vingt Ans après', '1845-01-21', 'Français', [3], [0, 5]],
            ['La Reine Margot', '1845-12-23', 'Français', [3], [0, 5]],
            ['Le Vicomte de Bragelonne', '1847-10-01', 'Français', [3], [0, 5]],
            // Gustave Flaubert
            ['Madame Bovary', '1857-04-15', 'Français', [4], [0]],
            ['L\'Éducation sentimentale', '1869-11-17', 'Français', [4], [0]],
            ['Salammbô', '1862-11-24', 'Français', [4], [0, 5]],
            ['Trois Contes', '1877-04-24', 'Français', [4], [0]],
            ['Bouvard et Pécuchet', '1881-03-01', 'Français', [4], [0, 6]],
            // Simone de Beauvoir
            ['Le Deuxième Sexe', '1949-06-01', 'Français', [5], [6]],
            ['Les Mandarins', '1954-10-15', 'Français', [5], [0]],
            ['Mémoires d\'une jeune fille rangée', '1958-01-01', 'Français', [5], [4]],
            ['La Force de l\'âge', '1960-11-01', 'Français', [5], [4]],
            ['L\'Invitée', '1943-08-20', 'Français', [5], [0, 6]],
            // Jules Verne
            ['Vingt Mille Lieues sous les mers', '1870-06-20', 'Français', [6], [1, 0]],
            ['Le Tour du monde en 80 jours', '1873-01-30', 'Français', [6], [0]],
            ['Voyage au centre de la Terre', '1864-11-25', 'Français', [6], [1]],
            ['De la Terre à la Lune', '1865-10-25', 'Français', [6], [1]],
            ['L\'Île mystérieuse', '1875-01-01', 'Français', [6], [0, 1]],
            // Jacques Prévert
            ['Paroles', '1946-05-10', 'Français', [7], [7]],
            ['Histoires', '1946-10-01', 'Français', [7], [7]],
            ['Spectacle', '1951-03-15', 'Français', [7], [7]],
            ['La Pluie et le Beau Temps', '1955-06-01', 'Français', [7], [7]],
            ['Fatras', '1966-01-01', 'Français', [7], [7]],
            // Françoise Sagan
            ['Bonjour tristesse', '1954-03-15', 'Français', [8], [0]],
            ['Un certain sourire', '1956-06-01', 'Français', [8], [0]],
            ['Aimez-vous Brahms...', '1959-09-15', 'Français', [8], [0]],
            ['La Chamade', '1965-09-01', 'Français', [8], [0]],
            ['Le Garde du cœur', '1968-04-01', 'Français', [8], [0, 2]],
            // Marcel Proust
            ['Du côté de chez Swann', '1913-11-14', 'Français', [9], [0]],
            ['À l\'ombre des jeunes filles en fleurs', '1919-06-30', 'Français', [9], [0]],
            ['Le Côté de Guermantes', '1920-10-01', 'Français', [9], [0]],
            ['Sodome et Gomorrhe', '1921-05-01', 'Français', [9], [0]],
            ['Le Temps retrouvé', '1927-01-01', 'Français', [9], [0, 6]],
        ];

        foreach ($livresData as [$titre, $dateSortie, $langue, $auteurIndexes, $categorieIndexes]) {
            $livre = new Livre();
            $livre->setTitre($titre);
            $livre->setDateSortie(new \DateTime($dateSortie));
            $livre->setLangue($langue);

            foreach ($auteurIndexes as $idx) {
                $livre->addAuteur($auteurs[$idx]);
            }
            foreach ($categorieIndexes as $idx) {
                $livre->addCategory($categories[$idx]);
            }

            $manager->persist($livre);
        }

        // =====================================================================
        // 20 Adhérents (avec Utilisateur lié, données Faker fr_FR)
        // =====================================================================
        for ($i = 0; $i < 20; $i++) {
            $utilisateur = new Utilisateur();
            $prenom = $faker->firstName();
            $nom = $faker->lastName();
            $utilisateur->setNom($nom);
            $utilisateur->setPrenom($prenom);
            $utilisateur->setEmail(strtolower($prenom) . '.' . strtolower($nom) . $i . '@example.fr');
            $utilisateur->setRoles(['ROLE_ADHERENT']);
            $utilisateur->setPassword(
                $this->passwordHasher->hashPassword($utilisateur, 'adherent123')
            );
            $manager->persist($utilisateur);

            $adherent = new Adherent();
            $adherent->setUtilisateur($utilisateur);
            $adherent->setDateAdhesion($faker->dateTimeBetween('-2 years', 'now'));
            $adherent->setDateNaiss($faker->dateTimeBetween('-70 years', '-16 years'));
            $adherent->setAdressePostale($faker->address());
            $adherent->setNumTel($faker->numerify('06########'));
            $manager->persist($adherent);
        }

        $manager->flush();
    }
}
