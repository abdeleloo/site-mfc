-- ================================================================
-- SCRIPT MFC — CORRECTION ET ENRICHISSEMENT BASE DE DONNÉES
-- BTS SIO SLAM E6 — 2025-2026
-- MariaDB 10.4 / WAMP
--
-- INSTRUCTIONS :
--   1. Ouvrir phpMyAdmin → sélectionner la base "mfc"
--   2. Onglet "SQL" → coller tout le contenu → Exécuter
--      OU onglet "Importer" → choisir ce fichier
-- ================================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- ================================================================
-- ÉTAPE 1 — COLONNES MANQUANTES SUR LES TABLES EXISTANTES
-- ================================================================

-- 1.1 formations : colonnes pédagogiques
ALTER TABLE formations ADD COLUMN IF NOT EXISTS Description TEXT         NULL;
ALTER TABLE formations ADD COLUMN IF NOT EXISTS DureeJours  INT          NULL;
ALTER TABLE formations ADD COLUMN IF NOT EXISTS Prix        DECIMAL(8,2) NULL;
ALTER TABLE formations ADD COLUMN IF NOT EXISTS Niveau      VARCHAR(50)  NULL;

-- 1.2 session : colonnes manquantes (CodeFormation, Lieu, Places)
ALTER TABLE session ADD COLUMN IF NOT EXISTS CodeFormation   VARCHAR(50)  NULL;
ALTER TABLE session ADD COLUMN IF NOT EXISTS CodeCentre      VARCHAR(50)  NULL;
ALTER TABLE session ADD COLUMN IF NOT EXISTS Lieu            VARCHAR(100) NULL;
ALTER TABLE session ADD COLUMN IF NOT EXISTS PlacesTotal     INT NOT NULL DEFAULT 15;
ALTER TABLE session ADD COLUMN IF NOT EXISTS PlacesRestantes INT NOT NULL DEFAULT 15;

-- 1.3 fiche_inscription : lien direct avec le stagiaire (manquant dans le schéma)
ALTER TABLE fiche_inscription ADD COLUMN IF NOT EXISTS CodeStargiaire VARCHAR(50) NULL;

-- 1.4 centre_de_formation : informations pratiques
ALTER TABLE centre_de_formation ADD COLUMN IF NOT EXISTS Ville       VARCHAR(100) NULL;
ALTER TABLE centre_de_formation ADD COLUMN IF NOT EXISTS Adresse     VARCHAR(200) NULL;
ALTER TABLE centre_de_formation ADD COLUMN IF NOT EXISTS Responsable VARCHAR(100) NULL;
ALTER TABLE centre_de_formation ADD COLUMN IF NOT EXISTS NbPostes    INT          NULL;
ALTER TABLE centre_de_formation ADD COLUMN IF NOT EXISTS NbSalles    INT          NULL;

-- 1.5 financement : correction de la faute de frappe "LibélléFianacement"
ALTER TABLE financement ADD COLUMN IF NOT EXISTS LibelleFinancement VARCHAR(100) NULL;

-- Recopie des données de l'ancienne colonne (si elle contient déjà des données)
UPDATE financement
   SET LibelleFinancement = `LibélléFianacement`
 WHERE LibelleFinancement IS NULL;

-- ================================================================
-- ÉTAPE 2 — TABLE satisfaction (absente du schéma original)
-- ================================================================
CREATE TABLE IF NOT EXISTS satisfaction (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  note           TINYINT     NOT NULL,
  commentaire    TEXT        NULL,
  CodeFormation  VARCHAR(50) NULL,
  CodeStargiaire VARCHAR(50) NULL,
  date_          DATE        NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ================================================================
-- ÉTAPE 3 — DONNÉES DE RÉFÉRENCE
-- ================================================================

-- 3.1 Modes de financement
INSERT INTO financement (CodeFinancement, LibelleFinancement) VALUES
  ('FIN001', 'Plan de développement des compétences'),
  ('FIN002', 'OPCO — Opérateur de Compétences'),
  ('FIN003', 'CPF — Compte Personnel de Formation'),
  ('FIN004', 'Financement individuel'),
  ('FIN005', 'Aide régionale / Pôle Emploi')
ON DUPLICATE KEY UPDATE LibelleFinancement = VALUES(LibelleFinancement);

-- 3.2 Centres de formation (5 villes — données officielles MFC)
INSERT INTO centre_de_formation
  (CodeCentre, NomCentre, CodeFinancement, Ville, Adresse, Responsable, NbPostes, NbSalles)
VALUES
  ('CTR001', 'Lille (Siège)', 'FIN001', 'Lille',    '123 Rue de la Formation, 59000 Lille',          'Pierre BUS',        250, 30),
  ('CTR002', 'Bordeaux',      'FIN001', 'Bordeaux', '45 Avenue des Technologies, 33000 Bordeaux',   'Béatrice CHATEAU',  120, 15),
  ('CTR003', 'Lyon',          'FIN001', 'Lyon',     '78 Rue de l''Innovation, 69000 Lyon',            'Francesco POLI',    180, 20),
  ('CTR004', 'Nantes',        'FIN001', 'Nantes',   '12 Boulevard du Digital, 44000 Nantes',         'Zandezu DESU',      100, 12),
  ('CTR005', 'Nice',          'FIN001', 'Nice',     '8 Avenue de la Méditerranée, 06000 Nice',       'Abder MOMO',         80, 10)
ON DUPLICATE KEY UPDATE
  Ville       = VALUES(Ville),
  Adresse     = VALUES(Adresse),
  Responsable = VALUES(Responsable),
  NbPostes    = VALUES(NbPostes),
  NbSalles    = VALUES(NbSalles);

-- 3.3 Enrichissement des 6 formations existantes + correction Categorie/Types

UPDATE formations SET
  Categorie = 'Technique & Réseaux', Types = 'technique',
  Description = 'Administration des systèmes GNU/Linux et Windows Server, scripting Bash/PowerShell, virtualisation VMware/Hyper-V.',
  DureeJours = 5, Prix = 2500.00, Niveau = 'Intermédiaire'
WHERE CodeFormation = 'FOR001';

UPDATE formations SET
  Categorie = 'Informatique & Digital', Types = 'informatique',
  Description = 'Développement web front et back : HTML5, CSS3, JavaScript ES6+, PHP 8, Vue.js. Projet fil rouge inclus.',
  DureeJours = 10, Prix = 3800.00, Niveau = 'Débutant'
WHERE CodeFormation = 'FOR002';

UPDATE formations SET
  Categorie = 'Technique & Réseaux', Types = 'technique',
  Description = 'Fondamentaux de la cybersécurité : OWASP Top 10, analyse de vulnérabilités, tests de pénétration, RGPD.',
  DureeJours = 3, Prix = 1500.00, Niveau = 'Intermédiaire'
WHERE CodeFormation = 'FOR003';

UPDATE formations SET
  Categorie = 'Technique & Réseaux', Types = 'technique',
  Description = 'Cloud public AWS et Azure, conteneurisation Docker, orchestration Kubernetes, IaC avec Terraform.',
  DureeJours = 4, Prix = 2200.00, Niveau = 'Avancé'
WHERE CodeFormation = 'FOR004';

UPDATE formations SET
  Categorie = 'Informatique & Digital', Types = 'informatique',
  Description = 'Méthodes agiles Scrum et Kanban, gestion des risques, pilotage de projets IT, outils Jira et Trello.',
  DureeJours = 2, Prix = 1200.00, Niveau = 'Débutant'
WHERE CodeFormation = 'FOR005';

UPDATE formations SET
  Categorie = 'Informatique & Digital', Types = 'informatique',
  Description = 'Python pour la data : Pandas, NumPy, visualisation Matplotlib et Seaborn, initiation au Machine Learning.',
  DureeJours = 5, Prix = 2800.00, Niveau = 'Intermédiaire'
WHERE CodeFormation = 'FOR006';

-- Nouvelles formations Bureautique
INSERT INTO formations (CodeFormation, Nom, Categorie, Types, Description, DureeJours, Prix, Niveau) VALUES
  ('FOR007', 'Excel Avancé & Tableaux croisés dynamiques', 'Bureautique', 'bureautique',
   'Maîtrise avancée d''Excel : formules complexes, tableaux croisés dynamiques, macros VBA, Power Query.',
   3, 950.00, 'Intermédiaire'),
  ('FOR008', 'Suite Office 365 Complète', 'Bureautique', 'bureautique',
   'Word, Excel, PowerPoint, Outlook, Teams et SharePoint. Formation adaptée aux besoins métier.',
   5, 1400.00, 'Débutant'),
  ('FOR009', 'Word — Traitement de texte professionnel', 'Bureautique', 'bureautique',
   'Mise en page avancée, styles, publipostage, formulaires, documents longs et gestion des révisions.',
   2, 650.00, 'Débutant')
ON DUPLICATE KEY UPDATE
  Description = VALUES(Description), DureeJours = VALUES(DureeJours),
  Prix = VALUES(Prix), Niveau = VALUES(Niveau);

-- Nouvelles formations Technique
INSERT INTO formations (CodeFormation, Nom, Categorie, Types, Description, DureeJours, Prix, Niveau) VALUES
  ('FOR010', 'Réseaux LAN/WAN & Cisco (CCNA)', 'Technique & Réseaux', 'technique',
   'Protocoles TCP/IP, routage OSPF et BGP, switching VLAN, configuration Cisco. Préparation CCNA.',
   5, 2600.00, 'Intermédiaire'),
  ('FOR011', 'Virtualisation VMware & Hyper-V', 'Technique & Réseaux', 'technique',
   'Installation et administration de VMware ESXi, vCenter et Microsoft Hyper-V. Haute disponibilité.',
   3, 1800.00, 'Intermédiaire')
ON DUPLICATE KEY UPDATE
  Description = VALUES(Description), DureeJours = VALUES(DureeJours),
  Prix = VALUES(Prix), Niveau = VALUES(Niveau);

-- 3.4 Formateurs — données officielles MFC (tableau des effectifs)
INSERT INTO formateurs (Matricule, Nom, Prenom, `Spécialité`, Ville, Email) VALUES
  -- Pôle Bureautique
  ('FORS001', 'LOTUS',     'Albin',    'Responsable Cellule Bureautique', 'Lille',    'albin.lotus@mfc.fr'),
  ('FORS002', 'EMECENE',   'Sandrine', 'Bureautique — Microsoft Office',  'Lille',    'sandrine.emecene@mfc.fr'),
  ('FORS003', 'PRO',       'Ali',      'Bureautique — Excel & Access',    'Lille',    'ali.pro@mfc.fr'),
  ('FORS004', 'AKCESSE',   'Julie',    'Bureautique — Word & PowerPoint', 'Bordeaux', 'julie.akcesse@mfc.fr'),
  ('FORS005', 'POUSSETTE', 'Pauline',  'Bureautique — Office 365',        'Lyon',     'pauline.poussette@mfc.fr'),
  ('FORS006', 'HOCHON',    'Paul',     'Bureautique — outils collaboratifs','Nantes', 'paul.hochon@mfc.fr'),
  -- Pôle Informatique
  ('FORS010', 'MERISE',    'Denis',    'Responsable Cellule Informatique','Lille',    'denis.merise@mfc.fr'),
  ('FORS011', 'UMELLE',    'Alain',    'Développement & BDD',             'Lille',    'alain.umelle@mfc.fr'),
  ('FORS012', 'DEGANTT',   'Victor',   'Développement web',               'Lille',    'victor.degantt@mfc.fr'),
  ('FORS013', 'FLUX',      'Gaëlle',   'Développement — PHP & JavaScript','Lyon',     'gaelle.flux@mfc.fr'),
  ('FORS014', 'DATA',      'Jean',     'Data Science — Python & BI',      'Paris',    'jean.data@mfc.fr'),
  ('FORS015', 'VUKULIC',   'Igor',     'Systèmes GNU/Linux',              'Lille',    'igor.vukulic@mfc.fr'),
  -- Pôle Technique
  ('FORS020', 'CISCO',     'Pierre',   'Responsable Cellule Technique',   'Lille',    'pierre.cisco@mfc.fr'),
  ('FORS021', 'OUEB',      'Jamal',    'Réseaux & Web',                   'Bordeaux', 'jamal.oueb@mfc.fr'),
  ('FORS022', 'HACK',      'Luc',      'Cybersécurité & Pentest',         'Lille',    'luc.hack@mfc.fr'),
  ('FORS023', 'EUSTACHE',  'Jean',     'Infrastructure Réseaux',          'Lyon',     'jean.eustache@mfc.fr'),
  ('FORS024', 'ZLATAN',    'Carl',     'Cloud & Virtualisation',          'Nantes',   'carl.zlatan@mfc.fr'),
  ('FORS025', 'GANTZ',     'Aurélien', 'Systèmes Unix/Linux',             'Nice',     'aurelien.gantz@mfc.fr')
ON DUPLICATE KEY UPDATE
  `Spécialité` = VALUES(`Spécialité`),
  Email        = VALUES(Email);

-- 3.5 Affectation Formateurs → Formations (asso_6 = Formation ↔ Formateur)
INSERT INTO asso_6 (CodeFormation, Matricule) VALUES
  ('FOR001', 'FORS015'), ('FOR001', 'FORS023'),
  ('FOR002', 'FORS012'), ('FOR002', 'FORS013'),
  ('FOR003', 'FORS022'),
  ('FOR004', 'FORS024'),
  ('FOR005', 'FORS010'),
  ('FOR006', 'FORS014'),
  ('FOR007', 'FORS001'), ('FOR007', 'FORS002'), ('FOR007', 'FORS003'),
  ('FOR008', 'FORS004'), ('FOR008', 'FORS005'), ('FOR008', 'FORS006'),
  ('FOR009', 'FORS002'), ('FOR009', 'FORS004'),
  ('FOR010', 'FORS020'), ('FOR010', 'FORS021'),
  ('FOR011', 'FORS024'), ('FOR011', 'FORS015')
ON DUPLICATE KEY UPDATE CodeFormation = VALUES(CodeFormation);

-- 3.6 Affectation Formations → Centres (asso_4 = Centre ↔ Formation)
INSERT INTO asso_4 (CodeCentre, CodeFormation) VALUES
  -- Lille : toutes les formations
  ('CTR001','FOR001'),('CTR001','FOR002'),('CTR001','FOR003'),('CTR001','FOR004'),
  ('CTR001','FOR005'),('CTR001','FOR006'),('CTR001','FOR007'),('CTR001','FOR008'),
  ('CTR001','FOR009'),('CTR001','FOR010'),('CTR001','FOR011'),
  -- Bordeaux
  ('CTR002','FOR002'),('CTR002','FOR003'),('CTR002','FOR007'),('CTR002','FOR008'),('CTR002','FOR010'),
  -- Lyon
  ('CTR003','FOR001'),('CTR003','FOR002'),('CTR003','FOR006'),('CTR003','FOR007'),('CTR003','FOR011'),
  -- Nantes
  ('CTR004','FOR002'),('CTR004','FOR004'),('CTR004','FOR007'),('CTR004','FOR008'),('CTR004','FOR009'),
  -- Nice
  ('CTR005','FOR007'),('CTR005','FOR008'),('CTR005','FOR009'),('CTR005','FOR010')
ON DUPLICATE KEY UPDATE CodeFormation = VALUES(CodeFormation);

-- ================================================================
-- ÉTAPE 4 — DONNÉES TRANSACTIONNELLES
-- ================================================================

-- 4.1 Sessions (CodeFormation et Lieu désormais disponibles)
INSERT INTO session
  (`NuméroSession`, CodeFormation, CodeCentre, Lieu, DateDebut, DateFin, PlacesTotal, PlacesRestantes)
VALUES
  ('SES001','FOR001','CTR001','Lille',    '2026-06-15','2026-06-19',15,12),
  ('SES002','FOR002','CTR003','Lyon',     '2026-06-22','2026-07-03',12, 8),
  ('SES003','FOR003','CTR002','Bordeaux', '2026-07-07','2026-07-09',12,10),
  ('SES004','FOR004','CTR001','Lille',    '2026-07-14','2026-07-17',10, 7),
  ('SES005','FOR007','CTR001','Lille',    '2026-07-21','2026-07-23',20,15),
  ('SES006','FOR008','CTR005','Nice',     '2026-07-28','2026-08-01',15,11),
  ('SES007','FOR010','CTR001','Lille',    '2026-08-04','2026-08-08',12, 9),
  ('SES008','FOR006','CTR003','Lyon',     '2026-08-11','2026-08-15',10, 6),
  ('SES009','FOR002','CTR004','Nantes',   '2026-08-18','2026-08-28',12, 4),
  ('SES010','FOR005','CTR002','Bordeaux', '2026-09-01','2026-09-02',20,18),
  ('SES011','FOR009','CTR004','Nantes',   '2026-09-08','2026-09-09',15,13),
  ('SES012','FOR011','CTR001','Lille',    '2026-09-14','2026-09-16',10, 8)
ON DUPLICATE KEY UPDATE
  CodeFormation = VALUES(CodeFormation), Lieu = VALUES(Lieu),
  PlacesTotal = VALUES(PlacesTotal), PlacesRestantes = VALUES(PlacesRestantes);

-- 4.2 Stagiaires
INSERT INTO stagiaires
  (CodeStargiaire, Nom, Prenom, Rue, Ville, Cp, Tel, Email, `Societé`)
VALUES
  ('ST001','MARTIN',  'Sophie', '12 Rue du Moulin',            'Paris',     '75015','0611223344','sophie.martin@acme.fr',   'ACME SA'),
  ('ST002','BERNARD', 'Thomas', '5 Avenue Victor Hugo',        'Lyon',      '69003','0622334455','thomas.bernard@stech.fr', 'StartupTech'),
  ('ST003','PETIT',   'Marie',  '8 Boulevard Gambetta',        'Bordeaux',  '33000','0633445566','marie.petit@corp.fr',     'Corp Industries'),
  ('ST004','DURAND',  'Lucas',  '2 Place de la Mairie',        'Nantes',    '44000','0644556677','lucas.durand@free.fr',    'Indépendant'),
  ('ST005','LEROY',   'Emma',   '15 Rue des Artisans',         'Nice',      '06000','0655667788','emma.leroy@nice.fr',      'Entreprise Nice'),
  ('ST006','MOREAU',  'Antoine','3 Impasse du Bois',           'Lille',     '59000','0666778899','antoine.moreau@mairie.fr','Mairie de Lille'),
  ('ST007','SIMON',   'Camille','22 Rue de la Paix',           'Paris',     '75001','0677889900','camille.simon@gd.fr',     'Grande Distribution'),
  ('ST008','LAURENT', 'Nicolas','7 Allée des Chênes',          'Strasbourg','67000','0688990011','nicolas.laurent@ind.fr',  'Industries Est'),
  ('ST009','LEFEBVRE','Chloé',  '19 Rue du Général De Gaulle', 'Rennes',    '35000','0699001122','chloe.lefebvre@rns.fr',   'Consultance Ouest'),
  ('ST010','MICHEL',  'Julien', '6 Place Bellecour',           'Lyon',      '69001','0600112233','julien.michel@csl.fr',    'Consulting Pro')
ON DUPLICATE KEY UPDATE
  Email = VALUES(Email), Ville = VALUES(Ville), `Societé` = VALUES(`Societé`);

-- 4.3 Rattachement Stagiaires → Centres (asso_2)
INSERT INTO asso_2 (CodeStargiaire, CodeCentre) VALUES
  ('ST001','CTR001'),('ST002','CTR003'),('ST003','CTR002'),
  ('ST004','CTR004'),('ST005','CTR005'),('ST006','CTR001'),
  ('ST007','CTR001'),('ST008','CTR001'),('ST009','CTR001'),
  ('ST010','CTR003')
ON DUPLICATE KEY UPDATE CodeCentre = VALUES(CodeCentre);

-- 4.4 Fiches d'inscription (avec CodeStargiaire — lien désormais explicite)
INSERT INTO fiche_inscription (NumeroInsciption, CodeStargiaire, Date_) VALUES
  ('INS20260510001','ST001','2026-05-10'),
  ('INS20260512002','ST002','2026-05-12'),
  ('INS20260515003','ST003','2026-05-15'),
  ('INS20260518004','ST004','2026-05-18'),
  ('INS20260520005','ST005','2026-05-20'),
  ('INS20260522006','ST006','2026-05-22'),
  ('INS20260525007','ST007','2026-05-25'),
  ('INS20260528008','ST008','2026-05-28'),
  ('INS20260530009','ST009','2026-05-30'),
  ('INS20260601010','ST010','2026-06-01')
ON DUPLICATE KEY UPDATE CodeStargiaire = VALUES(CodeStargiaire);

-- 4.5 Liaison Inscription → Formation (asso_5)
INSERT INTO asso_5 (CodeFormation, NumeroInsciption) VALUES
  ('FOR001','INS20260510001'),('FOR002','INS20260512002'),
  ('FOR003','INS20260515003'),('FOR004','INS20260518004'),
  ('FOR007','INS20260520005'),('FOR008','INS20260522006'),
  ('FOR010','INS20260525007'),('FOR006','INS20260528008'),
  ('FOR002','INS20260530009'),('FOR005','INS20260601010')
ON DUPLICATE KEY UPDATE CodeFormation = VALUES(CodeFormation);

-- 4.6 Liaison Inscription → Session (asso_7)
INSERT INTO asso_7 (`NuméroSession`, NumeroInsciption) VALUES
  ('SES001','INS20260510001'),('SES002','INS20260512002'),
  ('SES003','INS20260515003'),('SES004','INS20260518004'),
  ('SES005','INS20260520005'),('SES006','INS20260522006'),
  ('SES007','INS20260525007'),('SES008','INS20260528008'),
  ('SES009','INS20260530009'),('SES010','INS20260601010')
ON DUPLICATE KEY UPDATE `NuméroSession` = VALUES(`NuméroSession`);

-- Mise à jour des places restantes
UPDATE session SET PlacesRestantes = PlacesRestantes - 1
WHERE `NuméroSession` IN ('SES001','SES002','SES003','SES004','SES005',
                           'SES006','SES007','SES008','SES009','SES010');

-- 4.7 Liaison Inscription → Financement (asso_8)
INSERT INTO asso_8 (CodeFinancement, NumeroInsciption) VALUES
  ('FIN001','INS20260510001'),('FIN002','INS20260512002'),
  ('FIN003','INS20260515003'),('FIN001','INS20260518004'),
  ('FIN003','INS20260520005'),('FIN001','INS20260522006'),
  ('FIN001','INS20260525007'),('FIN002','INS20260528008'),
  ('FIN003','INS20260530009'),('FIN001','INS20260601010')
ON DUPLICATE KEY UPDATE CodeFinancement = VALUES(CodeFinancement);

-- ================================================================
-- ÉTAPE 5 — DONNÉES DE SATISFACTION
-- ================================================================
INSERT INTO satisfaction (note, commentaire, CodeFormation, CodeStargiaire, date_) VALUES
  (5, 'Excellent formateur, très pédagogue. Exercices pratiques bien calibrés.',         'FOR001','ST001','2026-04-18'),
  (4, 'Formation complète, bon contenu. Le rythme est un peu soutenu.',                  'FOR002','ST002','2026-04-20'),
  (5, 'Parfait pour démarrer en cybersécurité. TP sur Kali Linux très appréciés.',       'FOR003','ST003','2026-04-25'),
  (3, 'Contenu intéressant mais manque de mise en pratique sur cas réels.',              'FOR004','ST004','2026-04-28'),
  (4, 'Très bonne approche pédagogique, formateur disponible et à l''écoute.',           'FOR007','ST005','2026-05-03'),
  (5, 'Organisation parfaite, support de cours de qualité. Je recommande !',             'FOR008','ST006','2026-05-06'),
  (4, 'Formation conforme à mes attentes. Je repars avec de vraies compétences.',        'FOR010','ST007','2026-05-10'),
  (5, 'La meilleure formation que j''ai suivie. Merci à l''équipe MFC !',               'FOR006','ST008','2026-05-13'),
  (4, 'Très utile pour ma montée en compétences. Bonnes références bibliographiques.',   'FOR002','ST009','2026-05-16'),
  (3, 'Formation bien structurée mais durée trop courte pour les sujets abordés.',       'FOR005','ST010','2026-05-20');

-- ================================================================
-- RÉACTIVATION DES CLÉS ÉTRANGÈRES
-- ================================================================
SET foreign_key_checks = 1;

-- ================================================================
-- VÉRIFICATION — à copier-coller dans un nouvel onglet SQL
-- ================================================================
SELECT 'formations'           AS table_name, COUNT(*) AS nb FROM formations
UNION ALL SELECT 'formateurs',           COUNT(*) FROM formateurs
UNION ALL SELECT 'centre_de_formation',  COUNT(*) FROM centre_de_formation
UNION ALL SELECT 'session',              COUNT(*) FROM session
UNION ALL SELECT 'stagiaires',           COUNT(*) FROM stagiaires
UNION ALL SELECT 'fiche_inscription',    COUNT(*) FROM fiche_inscription
UNION ALL SELECT 'satisfaction',         COUNT(*) FROM satisfaction
UNION ALL SELECT 'asso_2',               COUNT(*) FROM asso_2
UNION ALL SELECT 'asso_4',               COUNT(*) FROM asso_4
UNION ALL SELECT 'asso_5',               COUNT(*) FROM asso_5
UNION ALL SELECT 'asso_6',               COUNT(*) FROM asso_6
UNION ALL SELECT 'asso_7',               COUNT(*) FROM asso_7
UNION ALL SELECT 'asso_8',               COUNT(*) FROM asso_8;
