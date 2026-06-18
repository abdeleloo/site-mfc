-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 13 mars 2026 à 12:00
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `mfc`
--

-- --------------------------------------------------------

--
-- Structure de la table `asso_2`
--

CREATE TABLE `asso_2` (
  `CodeStargiaire` varchar(50) NOT NULL,
  `CodeCentre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `asso_3`
--

CREATE TABLE `asso_3` (
  `CodeCentre` varchar(50) NOT NULL,
  `NumérosSalles` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `asso_4`
--

CREATE TABLE `asso_4` (
  `CodeCentre` varchar(50) NOT NULL,
  `CodeFormation` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `asso_5`
--

CREATE TABLE `asso_5` (
  `CodeFormation` varchar(50) NOT NULL,
  `NumeroInsciption` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `asso_6`
--

CREATE TABLE `asso_6` (
  `CodeFormation` varchar(50) NOT NULL,
  `Matricule` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `asso_7`
--

CREATE TABLE `asso_7` (
  `NuméroSession` varchar(50) NOT NULL,
  `NumeroInsciption` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `asso_8`
--

CREATE TABLE `asso_8` (
  `CodeFinancement` varchar(50) NOT NULL,
  `NumeroInsciption` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `centre_de_formation`
--

CREATE TABLE `centre_de_formation` (
  `CodeCentre` varchar(50) NOT NULL,
  `NomCentre` varchar(50) DEFAULT NULL,
  `CodeFinancement` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fiche_inscription`
--

CREATE TABLE `fiche_inscription` (
  `NumeroInsciption` varchar(50) NOT NULL,
  `Date_` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `financement`
--

CREATE TABLE `financement` (
  `CodeFinancement` varchar(50) NOT NULL,
  `LibélléFianacement` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `formateurs`
--

CREATE TABLE `formateurs` (
  `Matricule` varchar(50) NOT NULL,
  `Nom` varchar(50) DEFAULT NULL,
  `Prenom` varchar(50) DEFAULT NULL,
  `Rue` varchar(50) DEFAULT NULL,
  `Ville` varchar(50) DEFAULT NULL,
  `Cp` varchar(50) DEFAULT NULL,
  `Tel` varchar(50) DEFAULT NULL,
  `Email` varchar(50) DEFAULT NULL,
  `Spécialité` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `formations`
--

CREATE TABLE `formations` (
  `CodeFormation` varchar(50) NOT NULL,
  `Nom` varchar(50) DEFAULT NULL,
  `Categorie` varchar(50) DEFAULT NULL,
  `Types` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `formations`
--

INSERT INTO `formations` (`CodeFormation`, `Nom`, `Categorie`, `Types`) VALUES
('FOR001', 'Administration Réseau & Systèmes', 'Informatique & Digital', 'dev'),
('FOR002', 'Développement Web Fullstack', 'Informatique & Digital', 'dev'),
('FOR003', 'Cybersécurité Fondamentale', 'Informatique & Digital', 'seminaire'),
('FOR004', 'Cloud Computing & Virtualisation', 'Informatique & Digital', 'institution'),
('FOR005', 'Gestion de Projet IT', 'Informatique & Digital', 'seminaire'),
('FOR006', 'Data Science & Analyse de Données', 'Informatique & Digital', 'dev');

-- --------------------------------------------------------

--
-- Structure de la table `salles`
--

CREATE TABLE `salles` (
  `NumérosSalles` varchar(50) NOT NULL,
  `Nom` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `session`
--

CREATE TABLE `session` (
  `NuméroSession` varchar(50) NOT NULL,
  `DateDebut` varchar(50) DEFAULT NULL,
  `DateFin` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `stagiaires`
--

CREATE TABLE `stagiaires` (
  `CodeStargiaire` varchar(50) NOT NULL,
  `Nom` varchar(50) DEFAULT NULL,
  `Prenom` varchar(50) DEFAULT NULL,
  `Rue` varchar(50) DEFAULT NULL,
  `Ville` varchar(50) DEFAULT NULL,
  `Cp` varchar(50) DEFAULT NULL,
  `Tel` varchar(50) DEFAULT NULL,
  `Email` varchar(50) DEFAULT NULL,
  `Societé` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `asso_2`
--
ALTER TABLE `asso_2`
  ADD PRIMARY KEY (`CodeStargiaire`,`CodeCentre`),
  ADD KEY `CodeCentre` (`CodeCentre`);

--
-- Index pour la table `asso_3`
--
ALTER TABLE `asso_3`
  ADD PRIMARY KEY (`CodeCentre`,`NumérosSalles`),
  ADD KEY `NumérosSalles` (`NumérosSalles`);

--
-- Index pour la table `asso_4`
--
ALTER TABLE `asso_4`
  ADD PRIMARY KEY (`CodeCentre`,`CodeFormation`),
  ADD KEY `CodeFormation` (`CodeFormation`);

--
-- Index pour la table `asso_5`
--
ALTER TABLE `asso_5`
  ADD PRIMARY KEY (`CodeFormation`,`NumeroInsciption`),
  ADD KEY `NumeroInsciption` (`NumeroInsciption`);

--
-- Index pour la table `asso_6`
--
ALTER TABLE `asso_6`
  ADD PRIMARY KEY (`CodeFormation`,`Matricule`),
  ADD KEY `Matricule` (`Matricule`);

--
-- Index pour la table `asso_7`
--
ALTER TABLE `asso_7`
  ADD PRIMARY KEY (`NuméroSession`,`NumeroInsciption`),
  ADD KEY `NumeroInsciption` (`NumeroInsciption`);

--
-- Index pour la table `asso_8`
--
ALTER TABLE `asso_8`
  ADD PRIMARY KEY (`CodeFinancement`,`NumeroInsciption`),
  ADD KEY `NumeroInsciption` (`NumeroInsciption`);

--
-- Index pour la table `centre_de_formation`
--
ALTER TABLE `centre_de_formation`
  ADD PRIMARY KEY (`CodeCentre`),
  ADD KEY `CodeFinancement` (`CodeFinancement`);

--
-- Index pour la table `fiche_inscription`
--
ALTER TABLE `fiche_inscription`
  ADD PRIMARY KEY (`NumeroInsciption`);

--
-- Index pour la table `financement`
--
ALTER TABLE `financement`
  ADD PRIMARY KEY (`CodeFinancement`);

--
-- Index pour la table `formateurs`
--
ALTER TABLE `formateurs`
  ADD PRIMARY KEY (`Matricule`);

--
-- Index pour la table `formations`
--
ALTER TABLE `formations`
  ADD PRIMARY KEY (`CodeFormation`);

--
-- Index pour la table `salles`
--
ALTER TABLE `salles`
  ADD PRIMARY KEY (`NumérosSalles`);

--
-- Index pour la table `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`NuméroSession`);

--
-- Index pour la table `stagiaires`
--
ALTER TABLE `stagiaires`
  ADD PRIMARY KEY (`CodeStargiaire`);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `asso_2`
--
ALTER TABLE `asso_2`
  ADD CONSTRAINT `asso_2_ibfk_1` FOREIGN KEY (`CodeStargiaire`) REFERENCES `stagiaires` (`CodeStargiaire`),
  ADD CONSTRAINT `asso_2_ibfk_2` FOREIGN KEY (`CodeCentre`) REFERENCES `centre_de_formation` (`CodeCentre`);

--
-- Contraintes pour la table `asso_3`
--
ALTER TABLE `asso_3`
  ADD CONSTRAINT `asso_3_ibfk_1` FOREIGN KEY (`CodeCentre`) REFERENCES `centre_de_formation` (`CodeCentre`),
  ADD CONSTRAINT `asso_3_ibfk_2` FOREIGN KEY (`NumérosSalles`) REFERENCES `salles` (`NumérosSalles`);

--
-- Contraintes pour la table `asso_4`
--
ALTER TABLE `asso_4`
  ADD CONSTRAINT `asso_4_ibfk_1` FOREIGN KEY (`CodeCentre`) REFERENCES `centre_de_formation` (`CodeCentre`),
  ADD CONSTRAINT `asso_4_ibfk_2` FOREIGN KEY (`CodeFormation`) REFERENCES `formations` (`CodeFormation`);

--
-- Contraintes pour la table `asso_5`
--
ALTER TABLE `asso_5`
  ADD CONSTRAINT `asso_5_ibfk_1` FOREIGN KEY (`CodeFormation`) REFERENCES `formations` (`CodeFormation`),
  ADD CONSTRAINT `asso_5_ibfk_2` FOREIGN KEY (`NumeroInsciption`) REFERENCES `fiche_inscription` (`NumeroInsciption`);

--
-- Contraintes pour la table `asso_6`
--
ALTER TABLE `asso_6`
  ADD CONSTRAINT `asso_6_ibfk_1` FOREIGN KEY (`CodeFormation`) REFERENCES `formations` (`CodeFormation`),
  ADD CONSTRAINT `asso_6_ibfk_2` FOREIGN KEY (`Matricule`) REFERENCES `formateurs` (`Matricule`);

--
-- Contraintes pour la table `asso_7`
--
ALTER TABLE `asso_7`
  ADD CONSTRAINT `asso_7_ibfk_1` FOREIGN KEY (`NuméroSession`) REFERENCES `session` (`NuméroSession`),
  ADD CONSTRAINT `asso_7_ibfk_2` FOREIGN KEY (`NumeroInsciption`) REFERENCES `fiche_inscription` (`NumeroInsciption`);

--
-- Contraintes pour la table `asso_8`
--
ALTER TABLE `asso_8`
  ADD CONSTRAINT `asso_8_ibfk_1` FOREIGN KEY (`CodeFinancement`) REFERENCES `financement` (`CodeFinancement`),
  ADD CONSTRAINT `asso_8_ibfk_2` FOREIGN KEY (`NumeroInsciption`) REFERENCES `fiche_inscription` (`NumeroInsciption`);

--
-- Contraintes pour la table `centre_de_formation`
--
ALTER TABLE `centre_de_formation`
  ADD CONSTRAINT `centre_de_formation_ibfk_1` FOREIGN KEY (`CodeFinancement`) REFERENCES `financement` (`CodeFinancement`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
