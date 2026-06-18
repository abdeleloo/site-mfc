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

/* ── ACTION : Suppression (POST uniquement pour éviter CSRF via GET) ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_delete'])) {
    if (!csrf_verify()) {
        $error = 'Action refusée : jeton CSRF invalide.';
    } else {
    $code = trim((string)$_POST['_delete']);
    try {
        $pdo->beginTransaction();
        $pdo->prepare('DELETE FROM asso_4 WHERE CodeFormation = ?')->execute([$code]);
        $pdo->prepare('DELETE FROM asso_6 WHERE CodeFormation = ?')->execute([$code]);
        $pdo->prepare('DELETE FROM asso_5 WHERE CodeFormation = ?')->execute([$code]);
        $pdo->prepare('DELETE FROM formations WHERE CodeFormation = ?')->execute([$code]);
        $pdo->commit();
        $message = "Formation « $code » supprimée.";
    } catch (Throwable $e) {
        $pdo->rollBack();
        $error = 'Suppression impossible (inscriptions ou sessions liées existantes).';
    }
    } // end csrf ok
}

/* ── ACTION : Édition (chargement du formulaire) ── */
if (isset($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM formations WHERE CodeFormation = ? LIMIT 1');
        $stmt->execute([trim((string)$_GET['edit'])]);
        $editRow = $stmt->fetch();
    } catch (Throwable $e) {}
}

/* ── ACTION : Enregistrement (ajout ou mise à jour) ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['_delete'])) {
    if (!csrf_verify()) {
        $error = 'Action refusée : jeton CSRF invalide.';
    }
    $code        = trim((string)($_POST['CodeFormation'] ?? ''));
    $nom         = trim((string)($_POST['Nom'] ?? ''));
    $categorie   = trim((string)($_POST['Categorie'] ?? ''));
    $types       = trim((string)($_POST['Types'] ?? ''));
    $description = trim((string)($_POST['Description'] ?? ''));
    $duree       = $_POST['DureeJours'] !== '' ? (int)$_POST['DureeJours'] : null;
    $prix        = $_POST['Prix'] !== '' ? (float)str_replace(',', '.', $_POST['Prix']) : null;
    $niveau      = trim((string)($_POST['Niveau'] ?? ''));
    $isEdit      = ($_POST['_action'] ?? '') === 'edit';

    if ($error === '' && ($code === '' || $nom === '')) {
        $error = 'Le code et le nom sont obligatoires.';
    } elseif ($error === '') {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO formations (CodeFormation, Nom, Categorie, Types, Description, DureeJours, Prix, Niveau)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                   Nom=VALUES(Nom), Categorie=VALUES(Categorie), Types=VALUES(Types),
                   Description=VALUES(Description), DureeJours=VALUES(DureeJours),
                   Prix=VALUES(Prix), Niveau=VALUES(Niveau)'
            );
            $stmt->execute([$code, $nom, $categorie, $types, $description, $duree, $prix, $niveau]);
            $message = $isEdit ? "Formation « $code » mise à jour." : "Formation « $code » ajoutée.";
            $editRow = null;
        } catch (Throwable $e) {
            $error = 'Enregistrement impossible : ' . $e->getMessage();
        }
    }
}

/* ── LECTURE : liste formations ── */
$formations = [];
try {
    $formations = $pdo->query(
        'SELECT CodeFormation, Nom, Categorie, Types, DureeJours, Prix, Niveau FROM formations ORDER BY Nom'
    )->fetchAll();
} catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion formations — Admin MFC</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
<?php require __DIR__ . '/includes/sidebar.php'; ?>
<div class="admin-content">

    <div class="admin-topbar">
        <h1>📚 Gestion des formations</h1>
        <a href="../pages/formations.php" target="_blank" class="btn btn-blue">🌐 Voir le site</a>
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
            <?= $editRow ? '✏️ Modifier la formation' : '➕ Ajouter une formation' ?>
        </h2>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="_action" value="<?= $editRow ? 'edit' : 'add' ?>">
            <div class="form-grid">
                <div>
                    <label>Code formation *</label>
                    <input name="CodeFormation" required maxlength="50"
                           value="<?= htmlspecialchars($editRow['CodeFormation'] ?? ($_POST['CodeFormation'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                           <?= $editRow ? 'readonly style="background:#f1f5f9;"' : '' ?>>
                </div>
                <div>
                    <label>Nom *</label>
                    <input name="Nom" required maxlength="50"
                           value="<?= htmlspecialchars($editRow['Nom'] ?? ($_POST['Nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div>
                    <label>Catégorie</label>
                    <select name="Categorie">
                        <option value="">— Sélectionner —</option>
                        <?php foreach (['Bureautique', 'Informatique & Digital', 'Technique & Réseaux'] as $cat): ?>
                            <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>"
                                <?= ($editRow['Categorie'] ?? '') === $cat ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Types (clé badge)</label>
                    <select name="Types">
                        <option value="">— Sélectionner —</option>
                        <?php foreach (['bureautique' => 'Bureautique', 'informatique' => 'Informatique & Digital', 'technique' => 'Technique & Réseaux'] as $k => $v): ?>
                            <option value="<?= $k ?>" <?= ($editRow['Types'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Durée (jours)</label>
                    <input name="DureeJours" type="number" min="1" max="30"
                           value="<?= htmlspecialchars((string)($editRow['DureeJours'] ?? ($_POST['DureeJours'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div>
                    <label>Prix HT (€)</label>
                    <input name="Prix" type="number" step="0.01" min="0"
                           value="<?= htmlspecialchars((string)($editRow['Prix'] ?? ($_POST['Prix'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div>
                    <label>Niveau</label>
                    <select name="Niveau">
                        <option value="">— Sélectionner —</option>
                        <?php foreach (['Débutant', 'Intermédiaire', 'Avancé', 'Expert'] as $niv): ?>
                            <option value="<?= $niv ?>" <?= ($editRow['Niveau'] ?? '') === $niv ? 'selected' : '' ?>><?= $niv ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div>
                <label>Description</label>
                <textarea name="Description"><?= htmlspecialchars($editRow['Description'] ?? ($_POST['Description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div style="margin-top:1rem;display:flex;gap:.75rem;">
                <button type="submit" class="btn btn-green">
                    <?= $editRow ? '💾 Enregistrer les modifications' : '➕ Ajouter' ?>
                </button>
                <?php if ($editRow): ?>
                    <a href="formations.php" class="btn btn-gray">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ── Liste des formations ── -->
    <h2 style="font-size:1.1rem;color:#1e3a8a;margin-bottom:.5rem;">
        📚 Catalogue (<?= count($formations) ?> formation<?= count($formations) > 1 ? 's' : '' ?>)
    </h2>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Nom</th>
                <th>Catégorie</th>
                <th>Durée</th>
                <th>Prix</th>
                <th>Niveau</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($formations as $f): ?>
                <tr>
                    <td><code><?= htmlspecialchars($f['CodeFormation'], ENT_QUOTES, 'UTF-8') ?></code></td>
                    <td><?= htmlspecialchars($f['Nom'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($f['Categorie'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($f['DureeJours'] !== null ? $f['DureeJours'] . ' j' : '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $f['Prix'] !== null ? htmlspecialchars(number_format((float)$f['Prix'], 0, ',', ' '), ENT_QUOTES, 'UTF-8') . ' €' : '-' ?></td>
                    <td><?= htmlspecialchars($f['Niveau'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td style="white-space:nowrap;">
                        <a href="formations.php?edit=<?= htmlspecialchars($f['CodeFormation'], ENT_QUOTES, 'UTF-8') ?>"
                           class="btn btn-blue" style="font-size:.8rem;padding:.25rem .7rem;">Modifier</a>
                        <form method="post" style="display:inline;"
                              onsubmit="return confirm('Supprimer la formation <?= htmlspecialchars(addslashes($f['CodeFormation']), ENT_QUOTES, 'UTF-8') ?> et ses associations ?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_delete" value="<?= htmlspecialchars($f['CodeFormation'], ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="btn btn-red" style="font-size:.8rem;padding:.25rem .7rem;">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div><!-- .admin-content -->
</div><!-- .admin-layout -->
</body>
</html>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             