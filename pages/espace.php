<?php
session_start();
$pageTitle = 'Mon espace - MFC';
$basePath = '..';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/header.php';

$role   = $_SESSION['user_role'] ?? '';
$prenom = $_SESSION['user_prenom'] ?? '';
$nom    = $_SESSION['user_nom'] ?? '';
$code   = $_SESSION['user_code'] ?? '';

if ($role === '') {
    header('Location: connexion.php');
    exit;
}

/*
 * Mapping réel des tables d'association (schéma MFC) :
 *   asso_2 : stagiaires  ↔ centre_de_formation  (CodeStargiaire + CodeCentre)
 *   asso_4 : centre_de_formation ↔ formations    (CodeCentre    + CodeFormation)
 *   asso_5 : formations  ↔ fiche_inscription     (CodeFormation + NumeroInsciption)
 *   asso_6 : formations  ↔ formateurs            (CodeFormation + Matricule)
 *   asso_7 : session     ↔ fiche_inscription     (NuméroSession + NumeroInsciption)
 *   asso_8 : financement ↔ fiche_inscription     (CodeFinancement + NumeroInsciption)
 *
 *   fiche_inscription.CodeStargiaire → lien direct stagiaire ↔ inscription (présent en BDD)
 *   session.CodeFormation, Lieu, PlacesTotal, PlacesRestantes → présents en BDD
 */

$data        = [];   // inscriptions (stagiaire) ou formations (formateur)
$centres     = [];   // centres du stagiaire (via asso_2)
$espaceError = '';

try {
    if ($role === 'stagiaire') {

        // --- Centres de formation rattachés au stagiaire (via asso_2) ---
        $stmt = $pdo->prepare(
            "SELECT cf.CodeCentre, cf.NomCentre
             FROM asso_2 a2
             JOIN centre_de_formation cf ON cf.CodeCentre = a2.CodeCentre
             WHERE a2.CodeStargiaire = ?
             ORDER BY cf.NomCentre"
        );
        $stmt->execute([$code]);
        $centres = $stmt->fetchAll();

        // --- Inscriptions du stagiaire connecté (fiche_inscription.CodeStargiaire) ---
        $stmt = $pdo->prepare(
            "SELECT fi.NumeroInsciption, fi.Date_,
                    f.Nom AS FormationNom, f.Categorie,
                    s.DateDebut, s.DateFin, s.Lieu
             FROM fiche_inscription fi
             JOIN asso_5 a5 ON a5.NumeroInsciption = fi.NumeroInsciption
             JOIN formations f ON f.CodeFormation = a5.CodeFormation
             LEFT JOIN asso_7 a7 ON a7.NumeroInsciption = fi.NumeroInsciption
             LEFT JOIN session s ON s.`NuméroSession` = a7.`NuméroSession`
             WHERE fi.CodeStargiaire = ?
             ORDER BY fi.Date_ DESC"
        );
        $stmt->execute([$code]);
        $data = $stmt->fetchAll();

    } elseif ($role === 'formateur') {

        // --- Formations enseignées par ce formateur (via asso_6) ---
        $stmt = $pdo->prepare(
            "SELECT f.CodeFormation, f.Nom AS FormationNom,
                    f.Categorie, f.Types
             FROM asso_6 a6
             JOIN formations f ON f.CodeFormation = a6.CodeFormation
             WHERE a6.Matricule = ?
             ORDER BY f.Nom"
        );
        $stmt->execute([$code]);
        $data = $stmt->fetchAll();

    }
} catch (Throwable $e) {
    $espaceError = 'Impossible de charger vos données.';
}
?>
<main>
    <h2>Mon espace</h2>
    <p>Bienvenue <strong><?= htmlspecialchars(trim($prenom . ' ' . $nom), ENT_QUOTES, 'UTF-8') ?></strong>
       <span class="badge badge-blue"><?= $role === 'formateur' ? 'Formateur' : 'Stagiaire' ?></span>
    </p>

    <!-- Informations personnelles -->
    <div class="card">
        <h3>Mes informations</h3>
        <p><strong>Code / Matricule&nbsp;:</strong> <?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Profil&nbsp;:</strong> <?= $role === 'formateur' ? 'Formateur MFC' : 'Stagiaire' ?></p>
    </div>

    <?php if ($espaceError !== ''): ?>
        <p class="alert"><?= htmlspecialchars($espaceError, ENT_QUOTES, 'UTF-8') ?></p>

    <?php elseif ($role === 'stagiaire'): ?>

        <!-- Centre(s) de rattachement du stagiaire -->
        <?php if (!empty($centres)): ?>
        <div class="card">
            <h3>Mon centre de formation</h3>
            <div class="grid grid-2">
                <?php foreach ($centres as $c): ?>
                    <div class="card accent-blue">
                        <h4><?= htmlspecialchars($c['NomCentre'] ?? $c['CodeCentre'], ENT_QUOTES, 'UTF-8') ?></h4>
                        <span class="badge badge-blue"><?= htmlspecialchars($c['CodeCentre'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Mes inscriptions -->
        <div class="card">
            <h3>Mes inscriptions</h3>
            <?php if (empty($data)): ?>
                <p class="muted">Aucune inscription enregistrée pour le moment.</p>
            <?php else: ?>
                <table class="session-table">
                    <thead>
                        <tr>
                            <th>Formation</th>
                            <th>Date inscription</th>
                            <th>Session</th>
                            <th>Lieu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                            <?php
                            $dateDebut = $row['DateDebut'] ?? '';
                            $dateFin   = $row['DateFin']   ?? '';
                            $sessionStr = ($dateDebut !== '' && $dateFin !== '')
                                ? htmlspecialchars($dateDebut . ' → ' . $dateFin, ENT_QUOTES, 'UTF-8')
                                : '—';
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['FormationNom'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong>
                                    <br><small><?= htmlspecialchars($row['Categorie'] ?? '', ENT_QUOTES, 'UTF-8') ?></small></td>
                                <td><?= htmlspecialchars($row['Date_'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= $sessionStr ?></td>
                                <td><?= htmlspecialchars($row['Lieu'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    <?php elseif ($role === 'formateur'): ?>

        <!-- Formations enseignées par le formateur -->
        <div class="card">
            <h3>Mes formations</h3>
            <?php if (empty($data)): ?>
                <p class="muted">Aucune formation assignée pour le moment.</p>
            <?php else: ?>
                <table class="session-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Formation</th>
                            <th>Catégorie</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['CodeFormation'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <a href="formation_detail.php?code=<?= htmlspecialchars($row['CodeFormation'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($row['FormationNom'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($row['Categorie'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['Types'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    <?php endif; ?>

    <!-- Accès rapide -->
    <div class="card">
        <h3>Accès rapide</h3>
        <div class="grid grid-2">
            <a href="formations.php" style="text-decoration:none;">
                <div class="card accent-blue">
                    <h4>Catalogue formations</h4>
                    <p>Toutes nos formations disponibles</p>
                </div>
            </a>
            <?php if ($role === 'stagiaire'): ?>
            <a href="inscription.php" style="text-decoration:none;">
                <div class="card accent-orange">
                    <h4>Nouvelle inscription</h4>
                    <p>S'inscrire à une formation</p>
                </div>
            </a>
            <?php endif; ?>
            <a href="satisfaction.php" style="text-decoration:none;">
                <div class="card accent-green">
                    <h4>Questionnaire satisfaction</h4>
                    <p>Évaluer une formation suivie</p>
                </div>
            </a>
        </div>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
