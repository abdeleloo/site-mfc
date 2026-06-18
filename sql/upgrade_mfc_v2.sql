-- MFC - Mise à jour schéma pour fonctionnalités type "site exemple"
-- Base : mfc
-- Objectifs :
-- - enrichir les formations (prix, durée, niveau, description)
-- - relier les sessions à une formation + capacité/lieu
-- - ajouter un questionnaire de satisfaction (table satisfaction)

USE `mfc`;

-- 1) Formations : nouveaux champs (si non présents)
ALTER TABLE `formations`
  ADD COLUMN `Description` TEXT NULL AFTER `Nom`,
  ADD COLUMN `DureeJours` INT NULL AFTER `Description`,
  ADD COLUMN `Prix` DECIMAL(8,2) NULL AFTER `DureeJours`,
  ADD COLUMN `Niveau` VARCHAR(50) NULL AFTER `Prix`;

-- 2) Sessions : relier à une formation et ajouter infos pratiques
ALTER TABLE `session`
  ADD COLUMN `CodeFormation` VARCHAR(50) NULL AFTER `NuméroSession`,
  ADD COLUMN `Lieu` VARCHAR(100) NULL AFTER `DateFin`,
  ADD COLUMN `Salle` VARCHAR(100) NULL AFTER `Lieu`,
  ADD COLUMN `PlacesTotal` INT NULL AFTER `Salle`,
  ADD COLUMN `PlacesRestantes` INT NULL AFTER `PlacesTotal`;

ALTER TABLE `session`
  ADD CONSTRAINT `fk_session_formation`
  FOREIGN KEY (`CodeFormation`) REFERENCES `formations` (`CodeFormation`);

-- 3) Satisfaction : table de questionnaire
CREATE TABLE IF NOT EXISTS `satisfaction` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `role` VARCHAR(30) NOT NULL,
  `email` VARCHAR(150) NULL,
  `note` TINYINT NOT NULL,
  `commentaire` TEXT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4) Données réalistes (formations existantes FOR001..FOR006)
UPDATE `formations`
SET
  `Categorie` = COALESCE(`Categorie`, 'Informatique & Digital')
WHERE `CodeFormation` IN ('FOR001','FOR002','FOR003','FOR004','FOR005','FOR006');

UPDATE `formations`
SET `Description`='Gestion de réseaux d’entreprise, services AD/DNS/DHCP, supervision et bonnes pratiques de sécurisation.',
    `DureeJours`=5,
    `Prix`=2500.00,
    `Niveau`='Avancé'
WHERE `CodeFormation`='FOR001';

UPDATE `formations`
SET `Description`='Front-end + back-end : conception, API, base de données et déploiement.',
    `DureeJours`=10,
    `Prix`=3800.00,
    `Niveau`='Intermédiaire'
WHERE `CodeFormation`='FOR002';

UPDATE `formations`
SET `Description`='Bases de la cybersécurité : menaces, bonnes pratiques, protection des données et sensibilisation.',
    `DureeJours`=3,
    `Prix`=1500.00,
    `Niveau`='Tous niveaux'
WHERE `CodeFormation`='FOR003';

UPDATE `formations`
SET `Description`='Cloud, virtualisation et déploiement : concepts, architectures, exploitation et sécurité.',
    `DureeJours`=4,
    `Prix`=2200.00,
    `Niveau`='Avancé'
WHERE `CodeFormation`='FOR004';

UPDATE `formations`
SET `Description`='Méthodes et outils pour piloter un projet : planning, risques, budget, communication.',
    `DureeJours`=5,
    `Prix`=2500.00,
    `Niveau`='Intermédiaire'
WHERE `CodeFormation`='FOR005';

UPDATE `formations`
SET `Description`='Initiation data : analyse, visualisation, notions de machine learning et cas pratiques.',
    `DureeJours`=8,
    `Prix`=3200.00,
    `Niveau`='Avancé'
WHERE `CodeFormation`='FOR006';

-- 5) Sessions réalistes (exemples 2024)
INSERT INTO `session` (`NuméroSession`, `CodeFormation`, `DateDebut`, `DateFin`, `Lieu`, `Salle`, `PlacesTotal`, `PlacesRestantes`)
VALUES
  ('SES001','FOR001','2024-04-15','2024-04-19','Lille','Salle A101',15,12),
  ('SES002','FOR002','2024-05-06','2024-05-17','Lyon','Salle B202',12,8),
  ('SES003','FOR003','2024-05-13','2024-05-15','Bordeaux','Salle C303',12,10),
  ('SES004','FOR004','2024-05-27','2024-05-30','Nantes','Salle D404',14,11)
ON DUPLICATE KEY UPDATE
  `CodeFormation`=VALUES(`CodeFormation`),
  `DateDebut`=VALUES(`DateDebut`),
  `DateFin`=VALUES(`DateFin`),
  `Lieu`=VALUES(`Lieu`),
  `Salle`=VALUES(`Salle`),
  `PlacesTotal`=VALUES(`PlacesTotal`),
  `PlacesRestantes`=VALUES(`PlacesRestantes`);

