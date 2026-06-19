
-- smarthome.sql — Schéma et données initiales pour SmartHome
-- Contient les instructions de création de tables et données de démonstration.
-- Utilisez mysql < smarthome.sql pour réinitialiser la base en local.
-- Vérifiez les préfixes et les encodages avant import.
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `smarthome`
--

-- --------------------------------------------------------

--
-- Structure de la table `administrateur`
--

CREATE TABLE `administrateur` (
  `id_admin` int(11) NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot de passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` int(150) NOT NULL,
  `role` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `administrateur`
--

INSERT INTO `administrateur` (`id_admin`, `nom`, `prenom`, `email`, `mot de passe`, `telephone`, `role`) VALUES
(1, 'AMERAMA', 'CHRIST EPIPHANE', 'christ@gmail.com', 'christ@07', 7665543, 'CREATEUR'),
(12345, 'BARRO', 'RAYAN', 'rayanbarro@outlook.fr', 'yanis@07', 766851291, 'CREATEUR');

-- --------------------------------------------------------

--
-- Structure de la table `annonces`
--

CREATE TABLE `annonces` (
  `id` int(10) UNSIGNED NOT NULL,
  `proprietaire_id` int(10) UNSIGNED NOT NULL,
  `ville_id` smallint(5) UNSIGNED DEFAULT NULL,
  `titre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `adresse` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quartier` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_logement` enum('Studio','T1','T2','T3','T4+','Colocation','Résidence étudiante') COLLATE utf8mb4_unicode_ci NOT NULL,
  `surface_m2` tinyint(3) UNSIGNED NOT NULL,
  `nb_pieces` tinyint(3) UNSIGNED DEFAULT '1',
  `etage` tinyint(4) DEFAULT NULL,
  `meuble` tinyint(1) NOT NULL DEFAULT '0',
  `loyer` smallint(5) UNSIGNED NOT NULL COMMENT 'Loyer mensuel en €',
  `charges` smallint(5) UNSIGNED DEFAULT '0' COMMENT 'Charges mensuelles en €',
  `charges_incluses` tinyint(1) DEFAULT '0' COMMENT '1 = charges incluses dans le loyer',
  `depot_garantie` smallint(5) UNSIGNED DEFAULT NULL COMMENT 'Dépôt de garantie en €',
  `ascenseur` tinyint(1) DEFAULT '0',
  `digicode` tinyint(1) DEFAULT '0',
  `gardien` tinyint(1) DEFAULT '0',
  `cave` tinyint(1) DEFAULT '0',
  `parking` tinyint(1) DEFAULT '0',
  `balcon` tinyint(1) DEFAULT '0',
  `fibre` tinyint(1) DEFAULT '0',
  `lave_linge` tinyint(1) DEFAULT '0',
  `badge` enum('','Nouveau','Populaire','Étudiant') COLLATE utf8mb4_unicode_ci DEFAULT '',
  `image_unsplash` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID Unsplash pour la photo principale',
  `statut` enum('disponible','loué','suspendu') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disponible',
  `verifie` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Annonce validée par équipe SmartHome',
  `date_disponible` date DEFAULT NULL,
  `cree_le` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mis_a_jour_le` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `annonces`
--

INSERT INTO `annonces` (`id`, `proprietaire_id`, `ville_id`, `titre`, `description`, `adresse`, `quartier`, `type_logement`, `surface_m2`, `nb_pieces`, `etage`, `meuble`, `loyer`, `charges`, `charges_incluses`, `depot_garantie`, `ascenseur`, `digicode`, `gardien`, `cave`, `parking`, `balcon`, `fibre`, `lave_linge`, `badge`, `image_unsplash`, `statut`, `verifie`, `date_disponible`, `cree_le`, `mis_a_jour_le`) VALUES
(1, 1, 13, 'Studio — Montmartre', 'Studio lumineux au 3e étage, cuisine équipée, parquet, proche Sacré-Cœur.', NULL, 'Montmartre', 'Studio', 18, 1, NULL, 1, 620, 0, 1, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 'Nouveau', '1502672260266-1c1ef2d93688', 'disponible', 1, NULL, '2026-06-05 13:44:43', '2026-06-05 13:44:43'),
(2, 1, 15, 'Chambre — Belleville', 'Chambre dans coloc de 3 personnes, espaces communs spacieux, ambiance conviviale.', NULL, 'Belleville', 'Colocation', 12, 1, NULL, 1, 450, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 'Étudiant', '1560448204-e02f11c3d0e2', 'disponible', 1, NULL, '2026-06-05 13:44:43', '2026-06-05 13:44:43'),
(3, 2, 12, 'Appartement T1 — Nation', 'T1 refait à neuf, double vitrage, ascenseur, cave. Quartier calme.', NULL, 'Nation', 'T1', 32, 1, NULL, 0, 980, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 'Populaire', '1493809842364-78817add7ffb', 'disponible', 1, NULL, '2026-06-05 13:44:43', '2026-06-05 13:44:43'),
(4, 1, 11, 'Studio — Oberkampf', 'Studio avec balcon filant et vue dégagée, cuisine ouverte, lave-linge inclus.', NULL, 'Oberkampf', 'Studio', 22, 1, NULL, 1, 750, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, '', '1484154218962-a197022b5858', 'disponible', 1, NULL, '2026-06-05 13:44:43', '2026-06-05 13:44:43'),
(5, 2, 11, 'T2 — Bastille', 'Beau T2 avec séjour lumineux, chambre séparée, parquet chêne, gardien.', NULL, 'Bastille', 'T2', 45, 1, NULL, 0, 1350, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 'Nouveau', '1522708323474-4769b6b16f0c', 'disponible', 1, NULL, '2026-06-05 13:44:43', '2026-06-05 13:44:43'),
(6, 3, 10, 'Chambre — République', 'Chambre meublée dans grande coloc, cuisine moderne, très bien desservie.', NULL, 'République', 'Colocation', 14, 1, NULL, 1, 490, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 'Étudiant', '1555636222-cae831e670b3', 'disponible', 1, NULL, '2026-06-05 13:44:43', '2026-06-05 13:44:43'),
(7, 1, 9, 'Studio — Pigalle', 'Studio cosy en rez-de-chaussée surélevé, tout équipé, charges comprises.', NULL, 'Pigalle', 'Studio', 20, 1, NULL, 1, 700, 0, 1, NULL, 0, 0, 0, 0, 0, 0, 0, 0, '', '1506905925346-21bda4d32df4', 'disponible', 1, NULL, '2026-06-05 13:44:43', '2026-06-05 13:44:43'),
(8, 2, 2, 'T1 — Marais', 'T1 meublé en plein Marais, cachet haussmannien, parquet, lumineux.', NULL, 'Le Marais', 'T1', 28, 1, NULL, 1, 1100, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 'Populaire', '1519125323398-675f0ddb6308', 'disponible', 1, NULL, '2026-06-05 13:44:43', '2026-06-05 13:44:43'),
(9, 3, 19, 'Studio — La Défense', 'Studio moderne résidence neuve, accès direct RER A, parking inclus.', NULL, 'La Défense', 'Studio', 24, 1, NULL, 1, 830, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, '', '1486325212027-8081e485255e', 'disponible', 1, NULL, '2026-06-05 13:44:43', '2026-06-05 13:44:43'),
(10, 1, 20, 'Chambre — Vincennes', 'Chambre dans maison avec jardin, coloc calme et sérieuse, proche bois.', NULL, 'Vincennes', 'Colocation', 16, 1, NULL, 1, 520, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 'Étudiant', '1573496359142-b8d87734a5a2', 'disponible', 1, NULL, '2026-06-05 13:44:43', '2026-06-05 13:44:43'),
(11, 2, 1, 'T2 — Châtelet', 'T2 hypercentre, vue sur toits parisiens, double séjour, cave et digicode.', NULL, 'Châtelet', 'T2', 48, 1, NULL, 0, 1600, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 'Nouveau', '1512917774080-9991f1c4c750', 'disponible', 1, NULL, '2026-06-05 13:44:43', '2026-06-05 13:44:43'),
(12, 3, 14, 'Studio — Alésia', 'Studio refait, cuisine américaine, proche marché, quartier résidentiel.', NULL, 'Alésia', 'Studio', 19, 1, NULL, 1, 660, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, '', '1502005229762-cf1b2da7c5d6', 'disponible', 1, NULL, '2026-06-05 13:44:43', '2026-06-05 13:44:43'),
(13, 1, 3, 'Chambre — Jussieu', 'Chambre à 5 min de Jussieu, parfait pour étudiants, internet fibre inclus.', NULL, 'Jussieu', 'Colocation', 13, 1, NULL, 1, 510, 0, 1, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 'Étudiant', '1555636222-cae831e670b3', 'disponible', 1, NULL, '2026-06-05 13:44:43', '2026-06-05 13:44:43'),
(14, 2, 12, 'T1 — Daumesnil', 'T1 calme, double exposition, proche lac Daumesnil, cave incluse.', NULL, 'Daumesnil', 'T1', 30, 1, NULL, 0, 920, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, '', '1493809842364-78817add7ffb', 'disponible', 1, NULL, '2026-06-05 13:44:43', '2026-06-05 13:44:43'),
(15, 3, 10, 'Studio — Strasbourg-Saint-Denis', 'Studio animé, proche bars et restos, tout meublé, disponible de suite.', NULL, 'Strasbourg-Saint-Denis', 'Studio', 17, 1, NULL, 1, 640, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 'Populaire', '1502672260266-1c1ef2d93688', 'disponible', 1, NULL, '2026-06-05 13:44:43', '2026-06-05 13:44:43'),
(16, 1, 16, 'T3 — Boulogne', 'Grand T3 lumineux, deux chambres, balcon, parking, gardien, très calme.', NULL, 'Boulogne', 'T3', 65, 1, NULL, 0, 1800, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 'Nouveau', '1522708323474-4769b6b16f0c', 'disponible', 1, NULL, '2026-06-05 13:44:43', '2026-06-05 13:44:43'),
(17, 2, 25, 'Chambre — Pantin', 'Chambre dans loft atypique, proche ligne 5, coloc artistes et créatifs.', NULL, 'Pantin', 'Colocation', 15, 1, NULL, 1, 430, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 'Étudiant', '1560448204-e02f11c3d0e2', 'disponible', 1, NULL, '2026-06-05 13:44:43', '2026-06-05 13:44:43'),
(18, 3, 17, 'Studio — Levallois', 'Studio tout équipé résidence récente, digicode, interphone, calme.', NULL, 'Levallois', 'Studio', 25, 1, NULL, 1, 880, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, '', '1484154218962-a197022b5858', 'disponible', 1, NULL, '2026-06-05 13:44:43', '2026-06-05 13:44:43'),
(19, 1, 15, 'T1 — Gambetta', 'T1 ensoleillé, parquet, cuisine équipée, proche métro Gambetta ligne 3.', NULL, 'Gambetta', 'T1', 29, 1, NULL, 0, 870, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, '', '1486325212027-8081e485255e', 'disponible', 1, NULL, '2026-06-05 13:44:43', '2026-06-05 13:44:43'),
(20, 2, 23, 'Chambre — Cachan', 'Chambre en résidence étudiante, salle de bain privative, cafétéria sur place.', NULL, 'Cachan', 'Colocation', 12, 1, NULL, 1, 400, 0, 1, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 'Étudiant', '1573496359142-b8d87734a5a2', 'disponible', 1, NULL, '2026-06-05 13:44:43', '2026-06-05 13:44:43');

-- --------------------------------------------------------

--
-- Structure de la table `annonce_photos`
--

CREATE TABLE `annonce_photos` (
  `id` int(10) UNSIGNED NOT NULL,
  `annonce_id` int(10) UNSIGNED NOT NULL,
  `url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt_text` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordre` tinyint(3) UNSIGNED DEFAULT '0' COMMENT 'Ordre d''affichage',
  `principale` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

CREATE TABLE `avis` (
  `id` int(10) UNSIGNED NOT NULL,
  `auteur_id` int(10) UNSIGNED NOT NULL,
  `annonce_id` int(10) UNSIGNED DEFAULT NULL,
  `note` tinyint(3) UNSIGNED NOT NULL,
  `commentaire` text COLLATE utf8mb4_unicode_ci,
  `verifie` tinyint(1) DEFAULT '0',
  `cree_le` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `favoris`
--

CREATE TABLE `favoris` (
  `id` int(10) UNSIGNED NOT NULL,
  `utilisateur_id` int(10) UNSIGNED NOT NULL,
  `annonce_id` int(10) UNSIGNED NOT NULL,
  `cree_le` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `annonce_id` int(10) UNSIGNED NOT NULL,
  `expediteur_id` int(10) UNSIGNED NOT NULL,
  `destinataire_id` int(10) UNSIGNED NOT NULL,
  `contenu` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `lu` tinyint(1) NOT NULL DEFAULT '0',
  `envoye_le` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `recherches_sauvegardees`
--

CREATE TABLE `recherches_sauvegardees` (
  `id` int(10) UNSIGNED NOT NULL,
  `utilisateur_id` int(10) UNSIGNED NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ex : "Mon studio Montmartre"',
  `ville_id` smallint(5) UNSIGNED DEFAULT NULL,
  `type_logement` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loyer_max` smallint(5) UNSIGNED DEFAULT NULL,
  `surface_min` tinyint(3) UNSIGNED DEFAULT NULL,
  `meuble` tinyint(1) DEFAULT NULL,
  `alerte_email` tinyint(1) DEFAULT '1',
  `cree_le` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(10) UNSIGNED NOT NULL,
  `nom` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Hash bcrypt',
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('locataire','proprietaire','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'locataire',
  `situation` enum('etudiant','travailleur','etranger','autre') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `budget_min` smallint(5) UNSIGNED DEFAULT NULL COMMENT 'Loyer minimum souhaité (€)',
  `budget_max` smallint(5) UNSIGNED DEFAULT NULL COMMENT 'Loyer maximum souhaité (€)',
  `ville_cible` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ville ou quartier recherché',
  `surface_min` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'Surface minimum souhaitée (m²)',
  `type_logement` set('Studio','T1','T2','T3','Colocation','Résidence étudiante') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Types acceptés (multi-sélection)',
  `preference_meuble` enum('oui','non','indifferent') COLLATE utf8mb4_unicode_ci DEFAULT 'indifferent',
  `preferences_texte` text COLLATE utf8mb4_unicode_ci COMMENT 'Description libre des besoins',
  `email_verifie` tinyint(1) NOT NULL DEFAULT '0',
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `cree_le` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mis_a_jour_le` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `telephone`, `avatar_url`, `role`, `situation`, `budget_min`, `budget_max`, `ville_cible`, `surface_min`, `type_logement`, `preference_meuble`, `preferences_texte`, `email_verifie`, `actif`, `cree_le`, `mis_a_jour_le`) VALUES
(1, 'Martin', 'Jean', 'jean.martin@demo.fr', '$2b$12$demo', '079866855', NULL, 'proprietaire', NULL, NULL, NULL, NULL, NULL, NULL, 'indifferent', NULL, 0, 1, '2026-06-05 13:44:43', '2026-06-17 03:25:17'),
(2, 'Dubois', 'Sophie', 'sophie.dubois@demo.fr', '$2b$12$demo', '0788557780', NULL, 'proprietaire', NULL, NULL, NULL, NULL, NULL, NULL, 'indifferent', NULL, 0, 1, '2026-06-05 13:44:43', '2026-06-17 03:25:34'),
(3, 'Bernard', 'Marc', 'marc.bernard@demo.fr', '$2b$12$demo', '0780886656', NULL, 'proprietaire', NULL, NULL, NULL, NULL, NULL, NULL, 'indifferent', NULL, 0, 1, '2026-06-05 13:44:43', '2026-06-17 03:25:57'),
(4, 'Admin', 'SmartHome', 'admin@smarthome.fr', '$2b$12$demo', '0754434768', NULL, 'admin', NULL, NULL, NULL, NULL, NULL, NULL, 'indifferent', NULL, 0, 1, '2026-06-05 13:44:43', '2026-06-17 03:26:11'),
(5, 'Leroy', 'Emma', 'emma.leroy@demo.fr', '$2b$12$demo', '0745321689', NULL, 'locataire', '', 400, 700, 'Paris', NULL, NULL, 'indifferent', NULL, 0, 1, '2026-06-05 13:44:43', '2026-06-17 03:26:32'),
(6, 'Nguyen', 'Thierry', 'thierry.n@demo.fr', '$2b$12$demo', '0712456767', NULL, 'locataire', 'travailleur', 700, 1200, 'Paris', NULL, NULL, 'indifferent', NULL, 0, 1, '2026-06-05 13:44:43', '2026-06-17 03:26:45'),
(7, 'Garcia', 'Sofia', 'sofia.g@demo.fr', '$2b$12$demo', '0745673214', NULL, 'locataire', 'etranger', 500, 900, 'Paris', NULL, NULL, 'indifferent', NULL, 0, 1, '2026-06-05 13:44:43', '2026-06-17 03:27:00'),
(8, 'arslan', 'TRAORE', 'arslan@gmail.com', '$2y$10$bQMj7pwIbPOSHWuiHaYT1erY05MhbSpM.z15CyadkOp35TH4yVUTC', '0734456578', NULL, 'locataire', 'etudiant', NULL, NULL, NULL, NULL, NULL, 'indifferent', NULL, 0, 1, '2026-06-13 13:53:52', '2026-06-17 03:27:54'),
(9, 'christ', 'epiphane', 'epiphane@outlook.fr', '$2y$10$vV8r/7HBrn0GDeVz2b2N/.VmjlINPvftM3r7lXDry6djhF9ZMyuN2', '0732546798', NULL, 'locataire', 'etudiant', NULL, NULL, NULL, NULL, NULL, 'indifferent', NULL, 0, 1, '2026-06-13 13:58:28', '2026-06-17 03:28:12'),
(10, 'Tele', 'Yanis', 'yanis@gmail.com', '$2y$10$301VND3ZL.c3mgYzrIQq4.7sA7ELFiSekFUyQZuloXiQu.77dwkDm', '0754985423', NULL, 'locataire', 'etranger', NULL, NULL, NULL, NULL, NULL, 'indifferent', NULL, 0, 1, '2026-06-13 15:09:21', '2026-06-17 03:28:38'),
(11, 'ouedraogo', 'abdoul', 'abdoul@gmail.com', '$2y$10$h83CyRl91zLl.p0mSarcze9hq4i.i9pDHordZ0Tp3lnyRu0nckN2S', '0756436798', NULL, 'locataire', 'travailleur', NULL, NULL, NULL, NULL, NULL, 'indifferent', NULL, 0, 1, '2026-06-13 16:54:40', '2026-06-17 03:28:56'),
(12, 'DOE', 'JOHN', 'john@gmail.com', '$2y$10$FHhcToX4RW7lZOSjx/o.3.j7vtn3nHXw./3HjLc/IbzXFnZMA40JC', '0758125689', NULL, 'locataire', 'etudiant', NULL, NULL, NULL, NULL, NULL, 'indifferent', NULL, 0, 1, '2026-06-13 16:58:59', '2026-06-17 03:29:14'),
(13, 'ilbouldo', 'kalil', 'kalil@gmail.com', '$2y$10$7HTld6d3ecCiD7sNxLyGvOiecjbH/iAwN0CaA7P.LIDJq3N1x9dJK', '0765980914', NULL, 'locataire', 'etudiant', NULL, NULL, NULL, NULL, NULL, 'indifferent', NULL, 0, 1, '2026-06-15 21:07:30', '2026-06-17 03:30:25');

-- --------------------------------------------------------

--
-- Structure de la table `villes`
--

CREATE TABLE `villes` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_postal` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `departement` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT 'Île-de-France',
  `latitude` decimal(9,6) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `villes`
--

INSERT INTO `villes` (`id`, `nom`, `code_postal`, `departement`, `region`, `latitude`, `longitude`) VALUES
(1, 'Paris 1er', '75001', 'Paris', 'Île-de-France', NULL, NULL),
(2, 'Paris 3e', '75003', 'Paris', 'Île-de-France', NULL, NULL),
(3, 'Paris 5e', '75005', 'Paris', 'Île-de-France', NULL, NULL),
(4, 'Paris 7e', '75007', 'Paris', 'Île-de-France', NULL, NULL),
(5, 'Paris 9e', '75009', 'Paris', 'Île-de-France', NULL, NULL),
(6, 'Paris 10e', '75010', 'Paris', 'Île-de-France', NULL, NULL),
(7, 'Paris 11e', '75011', 'Paris', 'Île-de-France', NULL, NULL),
(8, 'Paris 12e', '75012', 'Paris', 'Île-de-France', NULL, NULL),
(9, 'Paris 13e', '75013', 'Paris', 'Île-de-France', NULL, NULL),
(10, 'Paris 14e', '75014', 'Paris', 'Île-de-France', NULL, NULL),
(11, 'Paris 15e', '75015', 'Paris', 'Île-de-France', NULL, NULL),
(12, 'Paris 16e', '75016', 'Paris', 'Île-de-France', NULL, NULL),
(13, 'Paris 18e', '75018', 'Paris', 'Île-de-France', NULL, NULL),
(14, 'Paris 19e', '75019', 'Paris', 'Île-de-France', NULL, NULL),
(15, 'Paris 20e', '75020', 'Paris', 'Île-de-France', NULL, NULL),
(16, 'Boulogne-Billancourt', '92100', 'Hauts-de-Seine', 'Île-de-France', NULL, NULL),
(17, 'Levallois-Perret', '92300', 'Hauts-de-Seine', 'Île-de-France', NULL, NULL),
(18, 'Neuilly-sur-Seine', '92200', 'Hauts-de-Seine', 'Île-de-France', NULL, NULL),
(19, 'Puteaux', '92800', 'Hauts-de-Seine', 'Île-de-France', NULL, NULL),
(20, 'Vincennes', '94300', 'Val-de-Marne', 'Île-de-France', NULL, NULL),
(21, 'Créteil', '94000', 'Val-de-Marne', 'Île-de-France', NULL, NULL),
(22, 'Ivry-sur-Seine', '94200', 'Val-de-Marne', 'Île-de-France', NULL, NULL),
(23, 'Cachan', '94230', 'Val-de-Marne', 'Île-de-France', NULL, NULL),
(24, 'Saint-Denis', '93200', 'Seine-Saint-Denis', 'Île-de-France', NULL, NULL),
(25, 'Pantin', '93500', 'Seine-Saint-Denis', 'Île-de-France', NULL, NULL),
(26, 'Montreuil', '93100', 'Seine-Saint-Denis', 'Île-de-France', NULL, NULL),
(27, 'Massy', '91300', 'Essonne', 'Île-de-France', NULL, NULL),
(28, 'Gif-sur-Yvette', '91190', 'Essonne', 'Île-de-France', NULL, NULL),
(29, 'Versailles', '78000', 'Yvelines', 'Île-de-France', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `visites`
--

CREATE TABLE `visites` (
  `id` int(10) UNSIGNED NOT NULL,
  `annonce_id` int(10) UNSIGNED NOT NULL,
  `locataire_id` int(10) UNSIGNED NOT NULL,
  `date_visite` datetime NOT NULL,
  `statut` enum('en_attente','confirmée','annulée','effectuée') COLLATE utf8mb4_unicode_ci DEFAULT 'en_attente',
  `note_locataire` text COLLATE utf8mb4_unicode_ci COMMENT 'Message accompagnant la demande',
  `note_proprio` text COLLATE utf8mb4_unicode_ci COMMENT 'Réponse du propriétaire',
  `cree_le` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_annonces_disponibles`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_annonces_disponibles` (
`id` int(10) unsigned
,`titre` varchar(150)
,`description` text
,`quartier` varchar(100)
,`ville` varchar(100)
,`code_postal` varchar(10)
,`type_logement` enum('Studio','T1','T2','T3','T4+','Colocation','Résidence étudiante')
,`surface_m2` tinyint(3) unsigned
,`meuble` tinyint(1)
,`loyer` smallint(5) unsigned
,`charges` smallint(5) unsigned
,`charges_incluses` tinyint(1)
,`badge` enum('','Nouveau','Populaire','Étudiant')
,`image_unsplash` varchar(100)
,`verifie` tinyint(1)
,`cree_le` datetime
,`proprietaire` varchar(161)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_favoris_count`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_favoris_count` (
`annonce_id` int(10) unsigned
,`nb_favoris` bigint(21)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_stats_homepage`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_stats_homepage` (
`annonces_disponibles` bigint(21)
,`locataires_inscrits` bigint(21)
,`proprietaires_inscrits` bigint(21)
,`satisfaction_pct` decimal(6,0)
);

-- --------------------------------------------------------

--
-- Structure de la vue `v_annonces_disponibles`
--
DROP TABLE IF EXISTS `v_annonces_disponibles`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_annonces_disponibles`  AS SELECT `a`.`id` AS `id`, `a`.`titre` AS `titre`, `a`.`description` AS `description`, `a`.`quartier` AS `quartier`, `v`.`nom` AS `ville`, `v`.`code_postal` AS `code_postal`, `a`.`type_logement` AS `type_logement`, `a`.`surface_m2` AS `surface_m2`, `a`.`meuble` AS `meuble`, `a`.`loyer` AS `loyer`, `a`.`charges` AS `charges`, `a`.`charges_incluses` AS `charges_incluses`, `a`.`badge` AS `badge`, `a`.`image_unsplash` AS `image_unsplash`, `a`.`verifie` AS `verifie`, `a`.`cree_le` AS `cree_le`, concat(`u`.`prenom`,' ',`u`.`nom`) AS `proprietaire` FROM ((`annonces` `a` left join `villes` `v` on((`a`.`ville_id` = `v`.`id`))) left join `utilisateurs` `u` on((`a`.`proprietaire_id` = `u`.`id`))) WHERE (`a`.`statut` = 'disponible')  ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_favoris_count`
--
DROP TABLE IF EXISTS `v_favoris_count`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_favoris_count`  AS SELECT `favoris`.`annonce_id` AS `annonce_id`, count(0) AS `nb_favoris` FROM `favoris` GROUP BY `favoris`.`annonce_id``annonce_id`  ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_stats_homepage`
--
DROP TABLE IF EXISTS `v_stats_homepage`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_stats_homepage`  AS SELECT (select count(0) from `annonces` where (`annonces`.`statut` = 'disponible')) AS `annonces_disponibles`, (select count(0) from `utilisateurs` where (`utilisateurs`.`role` = 'locataire')) AS `locataires_inscrits`, (select count(0) from `utilisateurs` where (`utilisateurs`.`role` = 'proprietaire')) AS `proprietaires_inscrits`, (select round((avg(`avis`.`note`) * 20),0) from `avis` where (`avis`.`verifie` = 1)) AS `satisfaction_pct``satisfaction_pct`  ;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `administrateur`
--
ALTER TABLE `administrateur`
  ADD PRIMARY KEY (`id_admin`);

--
-- Index pour la table `annonces`
--
ALTER TABLE `annonces`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_annonce_proprio` (`proprietaire_id`),
  ADD KEY `idx_type` (`type_logement`),
  ADD KEY `idx_loyer` (`loyer`),
  ADD KEY `idx_ville` (`ville_id`),
  ADD KEY `idx_statut` (`statut`);

--
-- Index pour la table `annonce_photos`
--
ALTER TABLE `annonce_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_annonce` (`annonce_id`);

--
-- Index pour la table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_avis_auteur` (`auteur_id`),
  ADD KEY `fk_avis_annonce` (`annonce_id`);

--
-- Index pour la table `favoris`
--
ALTER TABLE `favoris`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_favori` (`utilisateur_id`,`annonce_id`),
  ADD KEY `fk_favori_annonce` (`annonce_id`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_msg_exp` (`expediteur_id`),
  ADD KEY `idx_annonce` (`annonce_id`),
  ADD KEY `idx_dest` (`destinataire_id`);

--
-- Index pour la table `recherches_sauvegardees`
--
ALTER TABLE `recherches_sauvegardees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_rech_user` (`utilisateur_id`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `villes`
--
ALTER TABLE `villes`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `visites`
--
ALTER TABLE `visites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_visite_annonce` (`annonce_id`),
  ADD KEY `fk_visite_locataire` (`locataire_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `administrateur`
--
ALTER TABLE `administrateur`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12346;

--
-- AUTO_INCREMENT pour la table `annonces`
--
ALTER TABLE `annonces`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `annonce_photos`
--
ALTER TABLE `annonce_photos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `avis`
--
ALTER TABLE `avis`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `favoris`
--
ALTER TABLE `favoris`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `recherches_sauvegardees`
--
ALTER TABLE `recherches_sauvegardees`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `villes`
--
ALTER TABLE `villes`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT pour la table `visites`
--
ALTER TABLE `visites`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `annonces`
--
ALTER TABLE `annonces`
  ADD CONSTRAINT `fk_annonce_proprio` FOREIGN KEY (`proprietaire_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_annonce_ville` FOREIGN KEY (`ville_id`) REFERENCES `villes` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `annonce_photos`
--
ALTER TABLE `annonce_photos`
  ADD CONSTRAINT `fk_photo_annonce` FOREIGN KEY (`annonce_id`) REFERENCES `annonces` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `fk_avis_annonce` FOREIGN KEY (`annonce_id`) REFERENCES `annonces` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_avis_auteur` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `favoris`
--
ALTER TABLE `favoris`
  ADD CONSTRAINT `fk_favori_annonce` FOREIGN KEY (`annonce_id`) REFERENCES `annonces` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_favori_user` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_msg_annonce` FOREIGN KEY (`annonce_id`) REFERENCES `annonces` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_msg_dest` FOREIGN KEY (`destinataire_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_msg_exp` FOREIGN KEY (`expediteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `recherches_sauvegardees`
--
ALTER TABLE `recherches_sauvegardees`
  ADD CONSTRAINT `fk_rech_user` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `visites`
--
ALTER TABLE `visites`
  ADD CONSTRAINT `fk_visite_annonce` FOREIGN KEY (`annonce_id`) REFERENCES `annonces` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_visite_locataire` FOREIGN KEY (`locataire_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
