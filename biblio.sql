-- Adminer 5.4.1 MySQL 8.0.45-0ubuntu0.24.04.1 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP DATABASE IF EXISTS `biblio`;
CREATE DATABASE `biblio` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `biblio`;

DROP TABLE IF EXISTS `adherent`;
CREATE TABLE `adherent` (
  `id_adh` int NOT NULL AUTO_INCREMENT,
  `date_adhesion` datetime NOT NULL,
  `date_naiss` date NOT NULL,
  `adresse_postale` varchar(255) NOT NULL,
  `num_tel` varchar(13) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `utilisateur_id` int NOT NULL,
  `est_actif` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_adh`),
  UNIQUE KEY `UNIQ_90D3F060FB88E14F` (`utilisateur_id`),
  CONSTRAINT `FK_90D3F060FB88E14F` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `adherent` (`id_adh`, `date_adhesion`, `date_naiss`, `adresse_postale`, `num_tel`, `photo`, `utilisateur_id`, `est_actif`) VALUES
(27,	'2024-08-05 07:50:52',	'1964-04-04',	'18, rue Valentin\n80167 Marie-les-Bains',	'0644591093',	NULL,	35,	1),
(28,	'2024-12-19 12:33:34',	'1968-12-29',	'4, chemin de Leclerc\n68365 Imbert',	'0609178096',	NULL,	36,	1),
(29,	'2025-12-24 15:07:13',	'1972-07-16',	'21, chemin de Allard\n86387 Chretien',	'0648488851',	NULL,	37,	1),
(30,	'2025-03-13 08:05:52',	'1959-02-28',	'46, impasse de Adam\n67996 Leger',	'0629911365',	NULL,	38,	1),
(31,	'2024-06-12 05:54:25',	'1999-08-15',	'736, chemin Anastasie Lebreton\n98629 Vidal',	'0636629585',	NULL,	39,	1),
(32,	'2025-03-22 08:43:16',	'1970-01-12',	'33, rue Charrier\n72165 BegueVille',	'0652068549',	NULL,	40,	1),
(33,	'2025-06-11 13:33:31',	'1981-04-26',	'96, rue Joseph Alves\n23378 Bouchet',	'0661794201',	NULL,	41,	1),
(34,	'2025-10-16 04:34:21',	'1966-10-14',	'1, rue Chevallier\n91278 Morel-sur-Carre',	'0614292764',	NULL,	42,	1),
(35,	'2025-12-14 17:51:13',	'1957-06-24',	'43, rue Henri Morel\n43024 Grenier',	'0628390042',	NULL,	43,	1),
(36,	'2025-02-02 05:47:30',	'1973-01-09',	'87, avenue Laine\n49519 Mercier-sur-Gregoire',	'0678428268',	NULL,	44,	1),
(37,	'2025-11-27 06:45:15',	'2008-01-02',	'41, avenue de Buisson\n72302 Labbenec',	'0619065975',	NULL,	45,	1),
(38,	'2024-12-23 20:43:33',	'1985-04-14',	'72, boulevard Gros\n20085 Guillet',	'0632002510',	NULL,	46,	1),
(39,	'2026-01-13 17:00:59',	'2003-11-05',	'place Thomas Raymond\n65416 Rousset',	'0647913616',	NULL,	47,	1),
(40,	'2025-03-13 04:09:06',	'2010-01-26',	'10, impasse Roland Techer\n89195 Guillot',	'0694663537',	NULL,	48,	1),
(41,	'2025-04-15 09:18:33',	'1974-08-28',	'63, chemin de Valentin\n95559 Philippe',	'0655487745',	NULL,	49,	1),
(42,	'2024-11-27 04:46:58',	'1956-12-05',	'271, rue de Jourdan\n62128 Levy-sur-Mer',	'0622607391',	NULL,	50,	1),
(43,	'2026-02-22 12:13:43',	'1980-03-09',	'943, impasse Martin\n49395 Navarro-sur-Lemaire',	'0699249426',	NULL,	51,	1),
(44,	'2025-10-29 17:30:00',	'1974-12-22',	'257, rue Lambert\n85029 Leroux',	'0682407515',	NULL,	52,	1),
(45,	'2025-09-19 01:30:15',	'2008-10-21',	'74, rue de Ollivier\n90851 BesnardBourg',	'0639589596',	NULL,	53,	1),
(46,	'2025-09-16 13:19:41',	'1983-09-18',	'70, boulevard Grondin\n08166 Martineaudan',	'0631088428',	NULL,	54,	1),
(47,	'2026-03-11 16:58:35',	'1985-04-15',	'155 rue Jean Valjean',	'1234568997',	NULL,	56,	1);

DROP TABLE IF EXISTS `auteur`;
CREATE TABLE `auteur` (
  `id_aut` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `date_naissance` date NOT NULL,
  `date_deces` date DEFAULT NULL,
  `nationalite` varchar(20) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `description` longtext,
  PRIMARY KEY (`id_aut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `auteur` (`id_aut`, `nom`, `prenom`, `date_naissance`, `date_deces`, `nationalite`, `photo`, `description`) VALUES
(11,	'Hugo',	'Victor',	'1802-02-26',	'1885-05-22',	'Française',	NULL,	'Poète, dramaturge et romancier, considéré comme l\'un des plus grands écrivains de la langue française.'),
(12,	'Camus',	'Albert',	'1913-11-07',	'1960-01-04',	'Française',	NULL,	'Écrivain, philosophe et journaliste, prix Nobel de littérature en 1957.'),
(13,	'Zola',	'Émile',	'1840-04-02',	'1902-09-29',	'Française',	NULL,	'Chef de file du naturalisme, auteur de la fresque des Rougon-Macquart.'),
(14,	'Dumas',	'Alexandre',	'1802-07-24',	'1870-12-05',	'Française',	NULL,	'Auteur prolifique de romans historiques et d\'aventures.'),
(15,	'Flaubert',	'Gustave',	'1821-12-12',	'1880-05-08',	'Française',	NULL,	'Maître du réalisme, perfectionniste du style littéraire.'),
(16,	'Beauvoir',	'Simone',	'1908-01-09',	'1986-04-14',	'Française',	NULL,	'Philosophe, romancière et essayiste, figure majeure du féminisme.'),
(17,	'Verne',	'Jules',	'1828-02-08',	'1905-03-24',	'Française',	NULL,	'Précurseur de la science-fiction, auteur des Voyages extraordinaires.'),
(18,	'Prévert',	'Jacques',	'1900-02-04',	'1977-04-11',	'Française',	NULL,	'Poète et scénariste, connu pour ses recueils Paroles et Histoires.'),
(19,	'Sagan',	'Françoise',	'1935-06-21',	'2004-09-24',	'Française',	NULL,	'Romancière célèbre pour Bonjour tristesse, écrit à 18 ans.'),
(20,	'Proust',	'Marcel',	'1871-07-10',	'1922-11-18',	'Française',	NULL,	'Auteur d\'À la recherche du temps perdu, monument de la littérature.');

DROP TABLE IF EXISTS `categorie`;
CREATE TABLE `categorie` (
  `id_cat` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` longtext,
  PRIMARY KEY (`id_cat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `categorie` (`id_cat`, `nom`, `description`) VALUES
(11,	'Roman',	'Romans classiques et contemporains de la littérature française et internationale.'),
(12,	'Science-Fiction',	'Œuvres imaginant des futurs possibles, des voyages spatiaux et des technologies avancées.'),
(13,	'Policier',	'Enquêtes, mystères et suspense au cœur de récits captivants.'),
(14,	'Fantastique',	'Mondes magiques, créatures surnaturelles et aventures extraordinaires.'),
(15,	'Biographie',	'Récits de vie de personnalités historiques, artistiques ou scientifiques.'),
(16,	'Histoire',	'Ouvrages retraçant les grands événements et périodes de l\'histoire mondiale.'),
(17,	'Philosophie',	'Réflexions sur l\'existence, la morale, la connaissance et la société.'),
(18,	'Poésie',	'Recueils de poèmes et œuvres en vers de toutes époques.'),
(19,	'Jeunesse',	'Livres destinés aux enfants et aux adolescents.'),
(20,	'Sciences',	'Ouvrages de vulgarisation scientifique et découvertes majeures.');

DROP TABLE IF EXISTS `doctrine_migration_versions`;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20260309085231',	'2026-03-09 08:52:40',	1303),
('DoctrineMigrations\\Version20260310133111',	'2026-03-10 13:31:16',	66);

DROP TABLE IF EXISTS `emprunt`;
CREATE TABLE `emprunt` (
  `id_emp` int NOT NULL AUTO_INCREMENT,
  `date_emprunt` datetime NOT NULL,
  `date_retour` datetime NOT NULL,
  `date_retour_reel` datetime DEFAULT NULL,
  `adherent_id` int NOT NULL,
  `livre_id` int NOT NULL,
  PRIMARY KEY (`id_emp`),
  KEY `IDX_364071D725F06C53` (`adherent_id`),
  KEY `IDX_364071D737D925CB` (`livre_id`),
  CONSTRAINT `FK_364071D725F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherent` (`id_adh`),
  CONSTRAINT `FK_364071D737D925CB` FOREIGN KEY (`livre_id`) REFERENCES `livre` (`id_livre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `emprunt` (`id_emp`, `date_emprunt`, `date_retour`, `date_retour_reel`, `adherent_id`, `livre_id`) VALUES
(24,	'2026-03-11 16:55:49',	'2026-03-26 16:55:49',	'2026-03-11 16:59:35',	34,	94),
(25,	'2026-03-11 16:55:49',	'2026-03-26 16:55:49',	'2026-03-11 16:59:37',	34,	92),
(26,	'2026-03-11 16:56:29',	'2026-03-26 16:56:29',	NULL,	39,	98),
(27,	'2026-03-11 16:59:25',	'2026-03-26 16:59:25',	NULL,	30,	97),
(28,	'2026-03-11 16:59:25',	'2026-03-26 16:59:25',	'2026-03-11 16:59:40',	30,	88),
(29,	'2026-03-11 16:59:56',	'2026-03-26 16:59:56',	NULL,	27,	65),
(30,	'2026-03-11 16:59:56',	'2026-03-26 16:59:56',	NULL,	27,	91);

DROP TABLE IF EXISTS `livre`;
CREATE TABLE `livre` (
  `id_livre` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `date_sortie` datetime NOT NULL,
  `langue` varchar(255) NOT NULL,
  `photo_couverture` varchar(255) DEFAULT NULL,
  `synopsis` longtext,
  PRIMARY KEY (`id_livre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `livre` (`id_livre`, `titre`, `date_sortie`, `langue`, `photo_couverture`, `synopsis`) VALUES
(52,	'Les Misérables',	'1862-04-03 00:00:00',	'Français',	NULL,	NULL),
(53,	'Notre-Dame de Paris',	'1831-03-16 00:00:00',	'Français',	NULL,	NULL),
(54,	'Les Contemplations',	'1856-04-23 00:00:00',	'Français',	NULL,	NULL),
(55,	'Les Travailleurs de la mer',	'1866-03-12 00:00:00',	'Français',	NULL,	NULL),
(56,	'L\'Homme qui rit',	'1869-04-01 00:00:00',	'Français',	NULL,	NULL),
(57,	'L\'Étranger',	'1942-06-15 00:00:00',	'Français',	NULL,	NULL),
(58,	'La Peste',	'1947-06-10 00:00:00',	'Français',	NULL,	NULL),
(59,	'Le Mythe de Sisyphe',	'1942-10-16 00:00:00',	'Français',	NULL,	NULL),
(60,	'La Chute',	'1956-05-25 00:00:00',	'Français',	NULL,	NULL),
(61,	'L\'Homme révolté',	'1951-10-18 00:00:00',	'Français',	NULL,	NULL),
(62,	'Germinal',	'1885-03-02 00:00:00',	'Français',	NULL,	NULL),
(63,	'L\'Assommoir',	'1877-01-01 00:00:00',	'Français',	NULL,	NULL),
(64,	'Nana',	'1880-03-15 00:00:00',	'Français',	NULL,	NULL),
(65,	'Au Bonheur des Dames',	'1883-03-05 00:00:00',	'Français',	NULL,	NULL),
(66,	'La Bête humaine',	'1890-03-02 00:00:00',	'Français',	NULL,	NULL),
(67,	'Le Comte de Monte-Cristo',	'1844-08-28 00:00:00',	'Français',	NULL,	NULL),
(68,	'Les Trois Mousquetaires',	'1844-03-14 00:00:00',	'Français',	NULL,	NULL),
(69,	'Vingt Ans après',	'1845-01-21 00:00:00',	'Français',	NULL,	NULL),
(70,	'La Reine Margot',	'1845-12-23 00:00:00',	'Français',	NULL,	NULL),
(71,	'Le Vicomte de Bragelonne',	'1847-10-01 00:00:00',	'Français',	NULL,	NULL),
(72,	'Madame Bovary',	'1857-04-15 00:00:00',	'Français',	NULL,	NULL),
(73,	'L\'Éducation sentimentale',	'1869-11-17 00:00:00',	'Français',	NULL,	NULL),
(74,	'Salammbô',	'1862-11-24 00:00:00',	'Français',	NULL,	NULL),
(75,	'Trois Contes',	'1877-04-24 00:00:00',	'Français',	NULL,	NULL),
(76,	'Bouvard et Pécuchet',	'1881-03-01 00:00:00',	'Français',	NULL,	NULL),
(77,	'Le Deuxième Sexe',	'1949-06-01 00:00:00',	'Français',	NULL,	NULL),
(78,	'Les Mandarins',	'1954-10-15 00:00:00',	'Français',	NULL,	NULL),
(79,	'Mémoires d\'une jeune fille rangée',	'1958-01-01 00:00:00',	'Français',	NULL,	NULL),
(80,	'La Force de l\'âge',	'1960-11-01 00:00:00',	'Français',	NULL,	NULL),
(81,	'L\'Invitée',	'1943-08-20 00:00:00',	'Français',	NULL,	NULL),
(82,	'Vingt Mille Lieues sous les mers',	'1870-06-20 00:00:00',	'Français',	NULL,	NULL),
(83,	'Le Tour du monde en 80 jours',	'1873-01-30 00:00:00',	'Français',	NULL,	NULL),
(84,	'Voyage au centre de la Terre',	'1864-11-25 00:00:00',	'Français',	NULL,	NULL),
(85,	'De la Terre à la Lune',	'1865-10-25 00:00:00',	'Français',	NULL,	NULL),
(86,	'L\'Île mystérieuse',	'1875-01-01 00:00:00',	'Français',	NULL,	NULL),
(87,	'Paroles',	'1946-05-10 00:00:00',	'Français',	NULL,	NULL),
(88,	'Histoires',	'1946-10-01 00:00:00',	'Français',	NULL,	NULL),
(89,	'Spectacle',	'1951-03-15 00:00:00',	'Français',	NULL,	NULL),
(90,	'La Pluie et le Beau Temps',	'1955-06-01 00:00:00',	'Français',	NULL,	NULL),
(91,	'Fatras',	'1966-01-01 00:00:00',	'Français',	NULL,	NULL),
(92,	'Bonjour tristesse',	'1954-03-15 00:00:00',	'Français',	NULL,	NULL),
(93,	'Un certain sourire',	'1956-06-01 00:00:00',	'Français',	NULL,	NULL),
(94,	'Aimez-vous Brahms...',	'1959-09-15 00:00:00',	'Français',	NULL,	NULL),
(95,	'La Chamade',	'1965-09-01 00:00:00',	'Français',	NULL,	NULL),
(96,	'Le Garde du cœur',	'1968-04-01 00:00:00',	'Français',	NULL,	NULL),
(97,	'Du côté de chez Swann',	'1913-11-14 00:00:00',	'Français',	NULL,	NULL),
(98,	'À l\'ombre des jeunes filles en fleurs',	'1919-06-30 00:00:00',	'Français',	NULL,	NULL),
(99,	'Le Côté de Guermantes',	'1920-10-01 00:00:00',	'Français',	NULL,	NULL),
(100,	'Sodome et Gomorrhe',	'1921-05-01 00:00:00',	'Français',	NULL,	NULL),
(101,	'Le Temps retrouvé',	'1927-01-01 00:00:00',	'Français',	NULL,	NULL);

DROP TABLE IF EXISTS `livre_auteur`;
CREATE TABLE `livre_auteur` (
  `livre_id` int NOT NULL,
  `auteur_id` int NOT NULL,
  PRIMARY KEY (`livre_id`,`auteur_id`),
  KEY `IDX_A11876B537D925CB` (`livre_id`),
  KEY `IDX_A11876B560BB6FE6` (`auteur_id`),
  CONSTRAINT `FK_A11876B537D925CB` FOREIGN KEY (`livre_id`) REFERENCES `livre` (`id_livre`),
  CONSTRAINT `FK_A11876B560BB6FE6` FOREIGN KEY (`auteur_id`) REFERENCES `auteur` (`id_aut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `livre_auteur` (`livre_id`, `auteur_id`) VALUES
(52,	11),
(53,	11),
(54,	11),
(55,	11),
(56,	11),
(57,	12),
(58,	12),
(59,	12),
(60,	12),
(61,	12),
(62,	13),
(63,	13),
(64,	13),
(65,	13),
(66,	13),
(67,	14),
(68,	14),
(69,	14),
(70,	14),
(71,	14),
(72,	15),
(73,	15),
(74,	15),
(75,	15),
(76,	15),
(77,	16),
(78,	16),
(79,	16),
(80,	16),
(81,	16),
(82,	17),
(83,	17),
(84,	17),
(85,	17),
(86,	17),
(87,	18),
(88,	18),
(89,	18),
(90,	18),
(91,	18),
(92,	19),
(93,	19),
(94,	19),
(95,	19),
(96,	19),
(97,	20),
(98,	20),
(99,	20),
(100,	20),
(101,	20);

DROP TABLE IF EXISTS `livre_categorie`;
CREATE TABLE `livre_categorie` (
  `livre_id` int NOT NULL,
  `categorie_id` int NOT NULL,
  PRIMARY KEY (`livre_id`,`categorie_id`),
  KEY `IDX_E61B069E37D925CB` (`livre_id`),
  KEY `IDX_E61B069EBCF5E72D` (`categorie_id`),
  CONSTRAINT `FK_E61B069E37D925CB` FOREIGN KEY (`livre_id`) REFERENCES `livre` (`id_livre`),
  CONSTRAINT `FK_E61B069EBCF5E72D` FOREIGN KEY (`categorie_id`) REFERENCES `categorie` (`id_cat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `livre_categorie` (`livre_id`, `categorie_id`) VALUES
(52,	11),
(53,	11),
(53,	14),
(54,	18),
(55,	11),
(56,	11),
(56,	14),
(57,	11),
(57,	17),
(58,	11),
(59,	17),
(60,	11),
(60,	17),
(61,	17),
(62,	11),
(62,	16),
(63,	11),
(64,	11),
(65,	11),
(66,	11),
(66,	13),
(67,	11),
(67,	16),
(68,	11),
(68,	16),
(69,	11),
(69,	16),
(70,	11),
(70,	16),
(71,	11),
(71,	16),
(72,	11),
(73,	11),
(74,	11),
(74,	16),
(75,	11),
(76,	11),
(76,	17),
(77,	17),
(78,	11),
(79,	15),
(80,	15),
(81,	11),
(81,	17),
(82,	11),
(82,	12),
(83,	11),
(84,	12),
(85,	12),
(86,	11),
(86,	12),
(87,	18),
(88,	18),
(89,	18),
(90,	18),
(91,	18),
(92,	11),
(93,	11),
(94,	11),
(95,	11),
(96,	11),
(96,	13),
(97,	11),
(98,	11),
(99,	11),
(100,	11),
(101,	11),
(101,	17);

DROP TABLE IF EXISTS `reservations`;
CREATE TABLE `reservations` (
  `id_resa` int NOT NULL AUTO_INCREMENT,
  `date_resa` datetime NOT NULL,
  `adherent_id` int NOT NULL,
  `livre_id` int NOT NULL,
  PRIMARY KEY (`id_resa`),
  UNIQUE KEY `UNIQ_4DA23937D925CB` (`livre_id`),
  KEY `IDX_4DA23925F06C53` (`adherent_id`),
  CONSTRAINT `FK_4DA23925F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherent` (`id_adh`),
  CONSTRAINT `FK_4DA23937D925CB` FOREIGN KEY (`livre_id`) REFERENCES `livre` (`id_livre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `reservations` (`id_resa`, `date_resa`, `adherent_id`, `livre_id`) VALUES
(14,	'2026-03-11 00:00:00',	47,	54),
(15,	'2026-03-11 00:00:00',	47,	60);

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE `utilisateur` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180) NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `utilisateur` (`id`, `email`, `roles`, `password`, `nom`, `prenom`) VALUES
(1,	'alexlolo@lolo.com',	'[\"ROLE_BIBLIO\"]',	'$2y$13$Ls5U/Z/hVVahVmjWmHaSKexPV/OLIvhDZwOxsQXoaA1yD7PPGu5Ny',	'Lovin',	'Alex'),
(2,	'argelles@alexian.com',	'[\"ROLE_BIBLIO\"]',	'$2y$13$2JMWIeuxnSLr2GWpWpE5a.anivaytM.PDqu33Ob1HzDTMloFHCww2',	'Arguelles',	'Alexian'),
(3,	'adrien@goat.com',	'[\"ROLE_ADMIN\"]',	'$2y$13$kyRfmEXzLO.bde3QMh765.LkCxo1/m0aHZF6EDahS.0OIfyKMHOXK',	'Theophile',	'Adrien'),
(35,	'grégoire.mallet0@example.fr',	'[\"ROLE_ADHERENT\"]',	'$2y$13$Y4FwJOL2WN1HiUsBhF73p.OPU3tP5wZPj3dxAaJVclMGHab.dTiZu',	'Mallet',	'Grégoire'),
(36,	'christiane.blanc1@example.fr',	'[\"ROLE_ADHERENT\"]',	'$2y$13$xeQeixrHVdoQy3A6FqufO.t5C9EzrMMO2atw8GbTRC0vr4ZGYQ3w.',	'Blanc',	'Christiane'),
(37,	'Émile.jourdan2@example.fr',	'[\"ROLE_ADHERENT\"]',	'$2y$13$Rm74aWL7Fa2ItNunY9cphuKnx9r7sJmCKYyrG/RheLS8XhjZWVGca',	'Jourdan',	'Émile'),
(38,	'jeanne.guyot3@example.fr',	'[\"ROLE_ADHERENT\"]',	'$2y$13$ShZbw1/DfR7E5JB/fWr2h.cO950Ttd7k5SoWD5C5kUHAK1fGXSIUq',	'Guyot',	'Jeanne'),
(39,	'timothée.maury4@example.fr',	'[\"ROLE_ADHERENT\"]',	'$2y$13$cbUp16hM9dXBNdI9K91up.MCcvkCoaXEJ2cnT8I0/SppRggBV39d.',	'Maury',	'Timothée'),
(40,	'guy.pons5@example.fr',	'[\"ROLE_ADHERENT\"]',	'$2y$13$9K5gokb/EaQQc6dYGbwMr.rpJwp81ya2g916jcNz2XjB7GHpE5/Qq',	'Pons',	'Guy'),
(41,	'timothée.jacquot6@example.fr',	'[\"ROLE_ADHERENT\"]',	'$2y$13$rIxMBin/IQUaPu30tnbAxeaPXbJXbUJmlOv7dyYlevyZ7BMQttXXe',	'Jacquot',	'Timothée'),
(42,	'zacharie.techer7@example.fr',	'[\"ROLE_ADHERENT\"]',	'$2y$13$zKbMZ0Fv6dCDTUV1p86tvexWLkeMOkwdMG.9G/BPrXQ7s7udg/X8y',	'Techer',	'Zacharie'),
(43,	'franck.maillard8@example.fr',	'[\"ROLE_ADHERENT\"]',	'$2y$13$pzzpIyQJriH5Goe2smN5VOjLC0gQktqSijHZTJpqLZ2C4rnLo5cxC',	'Maillard',	'Franck'),
(44,	'thomas.bourdon9@example.fr',	'[\"ROLE_ADHERENT\"]',	'$2y$13$u9l9J3/EeIOG2fBkgftDEuxQBm27U4P6s2ryKqSh1XtqDmn.8fOg2',	'Bourdon',	'Thomas'),
(45,	'noémi.bruneau10@example.fr',	'[\"ROLE_ADHERENT\"]',	'$2y$13$JT5fc4nEzpQu.uPRGX1bb.d5oVfW6e1KBfBzcgF5xTTTZ8ClOrC8S',	'Bruneau',	'Noémi'),
(46,	'raymond.boucher11@example.fr',	'[\"ROLE_ADHERENT\"]',	'$2y$13$SdoUpSwXvZoV4Ca7INbpp.D2lQUBaDnE2o4VjDi9ClEiZYIkHIRHG',	'Boucher',	'Raymond'),
(47,	'suzanne.meunier12@example.fr',	'[\"ROLE_ADHERENT\"]',	'$2y$13$.SiHY/bi6DusEAQr9jeBPu2HtjaMUdMToBBl6HBG7YiRLRZLqWx9q',	'Meunier',	'Suzanne'),
(48,	'suzanne.vidal13@example.fr',	'[\"ROLE_ADHERENT\"]',	'$2y$13$v7R7fAy8IMd2xA1Hake5qO0scNdNfudLaANuE8xl13bBmvNejCoUe',	'Vidal',	'Suzanne'),
(49,	'clémence.petitjean14@example.fr',	'[\"ROLE_ADHERENT\"]',	'$2y$13$gbStwaqID8DrEfnScV4J9./chDjWUPQPNPdtXF/1oVlDXSsmlG4Le',	'Petitjean',	'Clémence'),
(50,	'adrien.picard15@example.fr',	'[\"ROLE_ADHERENT\"]',	'$2y$13$/7h7dYpq/5mAjWb8OpO.weyj47AOsvTD8qB1p1hU66psfz/7KKAT6',	'Picard',	'Adrien'),
(51,	'andrée.meunier16@example.fr',	'[\"ROLE_ADHERENT\"]',	'$2y$13$tf/L9WW2QQZ5c9/JqHRDv.0mYX.QSq2MjRISqUs8irkuDwhDFh786',	'Meunier',	'Andrée'),
(52,	'virginie.perret17@example.fr',	'[\"ROLE_ADHERENT\"]',	'$2y$13$dpE.gbMs3dHP05rQmu7x1eDCmKKqyDisYcOVQtOYGQ555Q8vt.MDO',	'Perret',	'Virginie'),
(53,	'odette.laroche18@example.fr',	'[\"ROLE_ADHERENT\"]',	'$2y$13$GJeVZP0oOBTbmiTIRJcdsubmJ0ssG4KWyE1vipoYKUBsJ.hQcnVTe',	'Laroche',	'Odette'),
(54,	'margot.masson19@example.fr',	'[\"ROLE_ADHERENT\"]',	'$2y$13$owbxcHbptKBPfuEnYnrFiOKpZMT7/9J6NFi.t3BubCs2pOep1K0ym',	'Masson',	'Margot'),
(55,	'admin@biblio.com',	'[\"ROLE_ADMIN\"]',	'$2y$13$ON.KGZvUxepyIKE9NXVWze4HiBpjUxGNFUuz1Ky/H6aWp6m21Ivr.',	'admin',	'admin'),
(56,	'adherent@biblio.com',	'[\"ROLE_ADHERENT\"]',	'$2y$13$z.F4yO5rKWvR/6IwQQhOcOlOrf3ZisVcP2ONb/Xrm0W8.PbjMN2je',	'adherent',	'adherent');

-- 2026-03-11 17:01:17 UTC
