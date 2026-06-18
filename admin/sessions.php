<?php
session_start();
if (($_SESSION['admin_identifiant'] ?? '') === '') {
    header('Location: login.php');
    exit;
}
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/csrf.php';

$message = '';
$error   = '';
$editRow = null;

/* ── SUPPRESSION (POST) ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_delete'])) {
    if (!csrf_verify()) {
        $error = 'Action refusée : jeton CSRF invalide.';
    } else {
        $num = trim((string)$_POST['_delete']);
        try {
            $pdo->beginTransaction();
            $pdo->prepare('DELETE FROM asso_7 WHERE `NuméroSession` = ?')->execute([$num]);
            $pdo->prepare('DELETE FROM session WHERE `NuméroSession` = ?')->execute([$num]);
            $pdo->commit();
            $message = "Session « $num » supprimée.";
        } catch (Throwable $e) {
            $pdo->rollBack();
            $error = 'Suppression impossible (inscriptions liées existantes).';
        }
    }
}

/* ── ÉDITION (chargement) ── */
if (isset($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM session WHERE `NuméroSession` = ? LIMIT 1');
        $stmt->execute([trim((string)$_GET['edit'])]);
        $editRow = $stmt->fetch();
    } catch (Throwable $e) {}
}

/* ── ENREGISTREMENT (ajout / mise à jour) ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['_delete'])) {
    if (!csrf_verify()) {
        $error = 'Action refusée : jeton CSRF invalide.';
    }
    $num           = trim((string)($_POST['NumeroSession']  ?? ''));
    $codeFormation = trim((string)($_POST['CodeFormation']  ?? ''));
    $dateDebut     = trim((string)($_POST['DateDebut']      ?? ''));
    $dateFin       = trim((string)($_POST['DateFin']        ?? ''));
    $lieu          = trim((string)($_POST['Lieu']           ?? ''));
    $placesTotal   = $_POST['PlacesTotal']     !== '' ? (int)$_POST['PlacesTotal']     : null;
    $placesRest    = $_POST['PlacesRestantes'] !== '' ? (int)$_POST['PlacesRestantes'] : null;
    $isEdit        = ($_POST['_action'] ?? '') === 'edit';

    if ($error === '' && ($num === '' || $dateDebut === '' || $dateFin === '')) {
        $error = 'Numéro de session, date de début et date de fin sont obligatoires.';
    } elseif ($error === '') {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO session (`NuméroSession`, CodeFormation, DateDebut, DateFin, Lieu, PlacesTotal, PlacesRestantes)
                 VALUES (?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                   CodeFormation=VALUES(CodeFormation), DateDebut=VALUES(DateDebut),
                   DateFin=VALUES(DateFin), Lieu=VALUES(Lieu),
                   PlacesTotal=VALUES(PlacesTotal), PlacesRestantes=VALUES(PlacesRestantes)'
            );
            $stmt->execute([$num, $codeFormation ?: null, $dateDebut, $dateFin, $lieu ?: null, $placesTotal, $placesRest]);
            $message = $isEdit ? "Session « $num » mise à jour." : "Session « $num » ajoutée.";
            $editRow = null;
        } catch (Throwable $e) {
            $error = 'Enregistrement impossible : ' . $e->getMessage();
        }
    }
}

/* ── LECTURE : sessions ── */
$sessions   = [];
$formations = [];
try {
    $sessions = $pdo->query(
        "SELECT s.`NuméroSession` AS NumeroSession,
                s.CodeFormation, f.Nom AS FormationNom,
                s.DateDebut, s.DateFin, s.Lieu,
                s.PlacesTotal, s.PlacesRestantes,
                COUNT(DISTINCT a7.NumeroInsciption) AS NbInscrits
         FROM session s
         LEFT JOIN formations f ON f.CodeFormation = s.CodeFormation
         LEFT JOIN asso_7 a7 ON a7.`NuméroSession` = s.`NuméroSession`
         GROUP BY s.`NuméroSession`, s.CodeFormation, f.Nom, s.DateDebut, s.DateFin, s.Lieu, s.PlacesTotal, s.PlacesRestantes
         ORDER BY s.DateDebut"
    )->fetchAll();

    $formations = $pdo->query(
        'SELECT CodeFormation, Nom FROM formations ORDER BY Nom'
    )->fetchAll();
} catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion sessions — Admin MFC</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
<?php require __DIR__ . '/includes/sidebar.php'; ?>
<div class="admin-content">

    <div class="admin-topbar">
        <h1>📅 Gestion des sessions</h1>
    </div>

    <?php if ($message !== ''): ?>
        <p class="success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <p class="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <!-- ── Formulaire Ajouter / Modifier ── -->
    <div class="card-form">
        <h2 style="font-size:1.1rem;margin-bottom:1rem;color:#1e3a8a;">
            <?= $editRow ? '✏️ Modifier la session' : '➕ Ajouter une session' ?>
        </h2>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="_action" value="<?= $editRow ? 'edit' : 'add' ?>">
            <div class="form-grid">
                <div>
                    <label>N° Session *</label>
                    <input name="NumeroSession" required maxlength="50"
                           value="<?= htmlspecialchars($editRow['NuméroSession'] ?? ($_POST['NumeroSession'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                           <?= $editRow ? 'readonly style="background:#f1f5f9;"' : '' ?>>
                </div>
                <div>
                    <label>Formation</label>
                    <select name="CodeFormation">
                        <option value="">— Aucune —</option>
                        <?php foreach ($formations as $f): ?>
                            <option value="<?= htmlspecialchars($f['CodeFormation'], ENT_QUOTES, 'UTF-8') ?>"
                                <?= ($editRow['CodeFormation'] ?? ($_POST['CodeFormation'] ?? '')) === $f['CodeFormation'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['Nom'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Date de début *</label>
                    <input name="DateDebut" type="date" required
                           value="<?= htmlspecialchars($editRow['DateDebut'] ?? ($_POST['DateDebut'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div>
                    <label>Date de fin *</label>
                    <input name="DateFin" type="date" required
                           value="<?= htmlspecialchars($editRow['DateFin'] ?? ($_POST['DateFin'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div>
                    <label>Lieu</label>
                    <input name="Lieu" maxlength="100"
                           value="<?= htmlspecialchars($editRow['Lieu'] ?? ($_POST['Lieu'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 1rem;">
                    <div>
                        <label>Places totales</label>
                        <input name="PlacesTotal" type="number" min="1" max="200"
                               value="<?= htmlspecialchars((string)($editRow['PlacesTotal'] ?? ($_POST['PlacesTotal'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Places restantes</label>
                        <input name="PlacesRestantes" type="number" min="0" max="200"
                               value="<?= htmlspecialchars((string)($editRow['PlacesRestantes'] ?? ($_POST['PlacesRestantes'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
            </div>
            <div style="margin-top:1rem;display:flex;gap:.75rem;">
                <button type="submit" class="btn btn-green">
                    <?= $editRow ? '💾 Enregistrer les modifications' : '➕ Ajouter' ?>
                </button>
                <?php if ($editRow): ?>
                    <a href="sessions.php" class="btn btn-gray">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ── Liste des sessions ── -->
    <h2 style="font-size:1.1rem;color:#1e3a8a;margin-bottom:.5rem;">
        📅 Sessions (<?= count($sessions) ?> au total)
    </h2>
    <table>
        <thead>
            <tr>
                <th>N° Session</th>
                <th>Formation</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Lieu</th>
                <th>Places</th>
                <th>Inscrits</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sessions as $s): ?>
                <?php
                $places = '-';
                if (($s['PlacesTotal'] ?? null) !== null) {
                    $places = ($s['PlacesRestantes'] ?? '?') . '/' . $s['PlacesTotal'];
                }
                $nbInsc = (int)($s['NbInscrits'] ?? 0);
                ?>
                <tr>
                    <td><code><?= htmlspecialchars($s['NumeroSession'] ?? '', ENT_QUOTES, 'UTF-8') ?></code></td>
                    <td><?= htmlspecialchars($s['FormationNom'] ?? ($s['CodeFormation'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($s['DateDebut'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($s['DateFin'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($s['Lieu'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($places, ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <span class="badge <?= $nbInsc > 0 ? 'badge-green' : 'badge-gray' ?>">
                            <?= $nbInsc ?>
                        </span>
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="sessions.php?edit=<?= htmlspecialchars($s['NumeroSession'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           class="btn btn-blue" style="font-size:.8rem;padding:.25rem .7rem;">Modifier</a>
                        <form method="post" style="display:inline;"
                              onsubmit="return confirm('Supprimer la session <?= htmlspecialchars(addslashes($s['NumeroSession'] ?? ''), ENT_QUOTES, 'UTF-8') ?> ?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_delete" value="<?= htmlspecialchars($s['NumeroSession'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="btn btn-red" style="font-size:.8rem;padding:.25rem .7rem;"
                                <?= $nbInsc > 0 ? 'title="Attention : ' . $nbInsc . ' inscription(s) liées seront supprimées"' : '' ?>>
                                Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($sessions)): ?>
                <tr><td colspan="8" style="color:#64748b;text-align:center;padding:1rem;">Aucune session enregistrée.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div><!-- .admin-content -->
</div><!-- .admin-layout -->
</body>
</html>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 