<?php
$pageTitle = 'Détail formation - MFC';
$basePath = '..';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/header.php';

$codeFormation = trim((string)($_GET['code'] ?? ''));
$formation  = null;
$sessions   = [];
$formateurs = [];
$error      = '';

if ($codeFormation !== '') {
    try {
        // --- Colonnes dynamiques de la table formations ---
        $stmt = $pdo->prepare(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'formations'
             AND COLUMN_NAME IN ('Categorie','Types','Description','DureeJours','Prix','Niveau')"
        );
        $stmt->execute([$dbName]);
        $existingCols = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $selectCols = ['CodeFormation', 'Nom'];
        foreach (['Categorie', 'Types', 'Description', 'DureeJours', 'Prix', 'Niveau'] as $col) {
            if (in_array($col, $existingCols, true)) {
                $selectCols[] = $col;
            }
        }
        $stmt = $pdo->prepare('SELECT ' . implode(', ', $selectCols) . ' FROM formations WHERE CodeFormation = ? LIMIT 1');
        $stmt->execute([$codeFormation]);
        $formation = $stmt->fetch();

        // --- Sessions de cette formation ---
        $stmt = $pdo->prepare(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'session'
             AND COLUMN_NAME IN ('CodeFormation','Lieu','PlacesRestantes','PlacesTotal')"
        );
        $stmt->execute([$dbName]);
        $sessionCols = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (in_array('CodeFormation', $sessionCols, true)) {
            $selectSess = "s.`NuméroSession` AS NumeroSession, s.DateDebut, s.DateFin";
            if (in_array('Lieu', $sessionCols, true))            $selectSess .= ", s.Lieu";
            if (in_array('PlacesRestantes', $sessionCols, true)) $selectSess .= ", s.PlacesRestantes";
            if (in_array('PlacesTotal', $sessionCols, true))     $selectSess .= ", s.PlacesTotal";

            $stmt = $pdo->prepare("SELECT $selectSess FROM session s WHERE s.CodeFormation = ? ORDER BY s.DateDebut");
            $stmt->execute([$codeFormation]);
            $sessions = $stmt->fetchAll();
        }

        // --- Formateurs qui enseignent cette formation (via asso_6) ---
        $stmt = $pdo->prepare(
            "SELECT fo.Matricule, fo.Nom, fo.Prenom, fo.`Spécialité` AS Specialite
             FROM asso_6 a6
             JOIN formateurs fo ON fo.Matricule = a6.Matricule
             WHERE a6.CodeFormation = ?
             ORDER BY fo.Nom"
        );
        $stmt->execute([$codeFormation]);
        $formateurs = $stmt->fetchAll();

    } catch (Throwable $e) {
        $error = 'Impossible de charger le détail de la formation.';
    }
}
?>
<main>
    <h2>Détail de la formation</h2>

    <?php if ($error !== ''): ?>
        <p class="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>

    <?php elseif ($codeFormation === ''): ?>
        <p>Sélectionnez une formation depuis <a href="formations.php">le catalogue</a>.</p>

    <?php elseif (!$formation): ?>
        <p>Formation introuvable. <a href="formations.php">Retour au catalogue</a>.</p>

    <?php else: ?>
        <!-- Fiche formation -->
        <div class="card">
            <h3><?= htmlspecialchars($formation['Nom'] ?? '', ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars(
                ($formation['Description'] ?? '') !== '' ? $formation['Description'] : 'Programme proposé par le centre MFC.',
                ENT_QUOTES, 'UTF-8'
            ) ?></p>
            <p style="margin-top:.75rem;">
                <?php if (!empty($formation['Categorie'])): ?>
                    <span class="badge badge-blue"><?= htmlspecialchars($formation['Categorie'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
                <?php if (!empty($formation['Niveau'])): ?>
                    <span class="badge badge-blue"><?= htmlspecialchars($formation['Niveau'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
                <?php if (!empty($formation['DureeJours'])): ?>
                    <span class="badge badge-green"><?= htmlspecialchars((string)$formation['DureeJours'], ENT_QUOTES, 'UTF-8') ?> jours</span>
                <?php endif; ?>
                <?php if (!empty($formation['Prix'])): ?>
                    <span class="badge badge-orange"><?= htmlspecialchars(number_format((float)$formation['Prix'], 0, ',', ' '), ENT_QUOTES, 'UTF-8') ?> € HT</span>
                <?php endif; ?>
            </p>
            <p style="margin-top:1rem;">
                <a href="inscription.php?formation=<?= htmlspecialchars($codeFormation, ENT_QUOTES, 'UTF-8') ?>"
                   style="background:#1e3a8a;color:#fff;padding:.5rem 1.25rem;border-radius:6px;text-decoration:none;font-weight:600;">
                    ✏️ S'inscrire à cette formation
                </a>
            </p>
        </div>

        <!-- Formateurs -->
        <?php if (!empty($formateurs)): ?>
        <h3>Formateurs</h3>
        <div class="trainer-list">
            <?php foreach ($formateurs as $f): ?>
                <?php $initials = strtoupper(mb_substr($f['Prenom'] ?? '', 0, 1) . mb_substr($f['Nom'] ?? '', 0, 1)); ?>
                <div class="trainer-card">
                    <div class="trainer-img"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></div>
                    <h4><?= htmlspecialchars(trim(($f['Prenom'] ?? '') . ' ' . ($f['Nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></h4>
                    <p><strong><?= htmlspecialchars($f['Specialite'] ?? 'Formateur MFC', ENT_QUOTES, 'UTF-8') ?></strong></p>
                    <span class="badge badge-blue"><?= htmlspecialchars($f['Matricule'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Sessions -->
        <h3>Sessions disponibles</h3>
        <?php if (empty($sessions)): ?>
            <div class="card">
                <p class="muted">Aucune session n'est encore publiée pour cette formation.</p>
                <p>Contactez-nous via la page <a href="contact.php">Contact</a> pour connaître les prochaines dates.</p>
            </div>
        <?php else: ?>
            <table class="session-table">
                <thead>
                    <tr>
                        <th>Session</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Lieu</th>
                        <th>Places</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sessions as $s): ?>
                        <?php
                        $places = '';
                        if (isset($s['PlacesTotal']) && $s['PlacesTotal'] !== '') {
                            $places = ($s['PlacesRestantes'] ?? '?') . '/' . $s['PlacesTotal'];
                        } elseif (isset($s['PlacesRestantes']) && $s['PlacesRestantes'] !== '') {
                            $places = $s['PlacesRestantes'] . ' restantes';
                        }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($s['NumeroSession'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($s['DateDebut'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($s['DateFin'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($s['Lieu'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($places !== '' ? $places : '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a href="inscription.php?formation=<?= htmlspecialchars($codeFormation, ENT_QUOTES, 'UTF-8') ?>"
                                   style="color:#1e3a8a;font-weight:600;">S'inscrire</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <p style="margin-top:1.5rem;"><a href="formations.php">← Retour au catalogue</a></p>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
