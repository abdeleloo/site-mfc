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

/* ── SUPPRESSION ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_delete'])) {
    if (!csrf_verify()) {
        $error = 'Action refusée : jeton CSRF invalide.';
    } else {
        $code = trim((string)$_POST['_delete']);
        try {
            $pdo->beginTransaction();
            $pdo->prepare('DELETE FROM asso_2 WHERE CodeStargiaire = ?')->execute([$code]);
            $pdo->prepare('DELETE FROM asso_3 WHERE CodeStargiaire = ?')->execute([$code]);
            $pdo->prepare('DELETE FROM fiche_inscription WHERE CodeStargiaire = ?')->execute([$code]);
            $pdo->prepare('DELETE FROM stagiaires WHERE CodeStargiaire = ?')->execute([$code]);
            $pdo->commit();
            $message = "Stagiaire « $code » supprimé.";
        } catch (Throwable $e) {
            $pdo->rollBack();
            $error = 'Suppression impossible : ' . $e->getMessage();
        }
    }
}

/* ── ÉDITION ── */
if (isset($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM stagiaires WHERE CodeStargiaire = ? LIMIT 1');
        $stmt->execute([trim((string)$_GET['edit'])]);
        $editRow = $stmt->fetch();
    } catch (Throwable $e) {}
}

/* ── ENREGISTREMENT ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['_delete'])) {
    if (!csrf_verify()) {
        $error = 'Action refusée : jeton CSRF invalide.';
    }
    $code    = trim((string)($_POST['CodeStargiaire'] ?? ''));
    $nom     = trim((string)($_POST['Nom']     ?? ''));
    $prenom  = trim((string)($_POST['Prenom']  ?? ''));
    $email   = trim((string)($_POST['Email']   ?? ''));
    $tel     = trim((string)($_POST['Tel']     ?? ''));
    $rue     = trim((string)($_POST['Rue']     ?? ''));
    $ville   = trim((string)($_POST['Ville']   ?? ''));
    $cp      = trim((string)($_POST['Cp']      ?? ''));
    $societe = trim((string)($_POST['Societe'] ?? ''));
    $isEdit  = ($_POST['_action'] ?? '') === 'edit';

    if ($error === '' && ($code === '' || $nom === '' || $prenom === '')) {
        $error = 'Code, nom et prénom sont obligatoires.';
    } elseif ($error === '') {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO stagiaires (CodeStargiaire, Nom, Prenom, Email, Tel, Rue, Ville, Cp, `Societé`)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                   Nom=VALUES(Nom), Prenom=VALUES(Prenom), Email=VALUES(Email),
                   Tel=VALUES(Tel), Rue=VALUES(Rue), Ville=VALUES(Ville),
                   Cp=VALUES(Cp), `Societé`=VALUES(`Societé`)'
            );
            $stmt->execute([$code, $nom, $prenom, $email, $tel, $rue, $ville, $cp, $societe]);
            $message = $isEdit ? "Stagiaire « $code » mis à jour." : "Stagiaire « $code » ajouté.";
            $editRow = null;
        } catch (Throwable $e) {
            $error = 'Enregistrement impossible : ' . $e->getMessage();
        }
    }
}

/* ── LECTURE + recherche ── */
$stagiaires  = [];
$search      = trim((string)($_GET['q'] ?? ''));
$totalCount  = 0;
$perPage     = 20;
$page        = max(1, (int)($_GET['page'] ?? 1));

try {
    $where = '';
    $params = [];
    if ($search !== '') {
        $where = "WHERE CodeStargiaire LIKE ? OR Nom LIKE ? OR Prenom LIKE ? OR Email LIKE ?";
        $like  = '%' . $search . '%';
        $params = [$like, $like, $like, $like];
    }
    $totalCount = (int)$pdo->query("SELECT COUNT(*) FROM stagiaires $where" . ($params ? '' : ''))->fetchColumn();
    // Rebuild with params for count
    if ($params) {
        $stmtC = $pdo->prepare("SELECT COUNT(*) FROM stagiaires $where");
        $stmtC->execute($params);
        $totalCount = (int)$stmtC->fetchColumn();
    }
    $totalPages = max(1, (int)ceil($totalCount / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    $stmtS = $pdo->prepare(
        "SELECT s.CodeStargiaire, s.Nom, s.Prenom, s.Email, s.Tel,
                s.Ville, s.`Societé` AS Societe,
                COUNT(DISTINCT fi.NumeroInsciption) AS NbInscriptions
         FROM stagiaires s
         LEFT JOIN fiche_inscription fi ON fi.CodeStargiaire = s.CodeStargiaire
         $where
         GROUP BY s.CodeStargiaire, s.Nom, s.Prenom, s.Email, s.Tel, s.Ville, s.`Societé`
         ORDER BY s.Nom, s.Prenom
         LIMIT $perPage OFFSET $offset"
    );
    $stmtS->execute($params);
    $stagiaires = $stmtS->fetchAll();
} catch (Throwable $e) {
    $error = 'Erreur de chargement : ' . $e->getMessage();
}

$buildUrl = function(int $p) use ($search): string {
    $params = ['page' => $p];
    if ($search !== '') $params['q'] = $search;
    return '?' . http_build_query($params);
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion stagiaires — Admin MFC</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
<?php require __DIR__ . '/includes/sidebar.php'; ?>
<div class="admin-content">

    <div class="admin-topbar">
        <h1>👤 Gestion des stagiaires</h1>
        <span class="badge badge-blue"><?= $totalCount ?> stagiaire<?= $totalCount > 1 ? 's' : '' ?></span>
    </div>

    <?php if ($message !== ''): ?>
        <p class="success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <p class="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <!-- ── Formulaire Ajouter / Modifier ── -->
    <div class="card-form">
        <h2 style="font-size:1.05rem;margin-bottom:1rem;color:#1e3a8a;">
            <?= $editRow ? '✏️ Modifier le stagiaire' : '➕ Ajouter un stagiaire' ?>
        </h2>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="_action" value="<?= $editRow ? 'edit' : 'add' ?>">
            <div class="form-grid">
                <div>
                    <label>Code stagiaire *</label>
                    <input name="CodeStargiaire" required maxlength="20"
                           value="<?= htmlspecialchars($editRow['CodeStargiaire'] ?? ($_POST['CodeStargiaire'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                           <?= $editRow ? 'readonly style="background:#f1f5f9;"' : '' ?>>
                </div>
                <div>
                    <label>Société</label>
                    <input name="Societe" maxlength="100"
                           value="<?= htmlspecialchars($editRow['Societé'] ?? ($_POST['Societe'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div>
                    <label>Nom *</label>
                    <input name="Nom" required maxlength="80"
                           value="<?= htmlspecialchars($editRow['Nom'] ?? ($_POST['Nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div>
                    <label>Prénom *</label>
                    <input name="Prenom" required maxlength="80"
                           value="<?= htmlspecialchars($editRow['Prenom'] ?? ($_POST['Prenom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div>
                    <label>Email</label>
                    <input name="Email" type="email" maxlength="120"
                           value="<?= htmlspecialchars($editRow['Email'] ?? ($_POST['Email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div>
                    <label>Téléphone</label>
                    <input name="Tel" type="tel" maxlength="20"
                           value="<?= htmlspecialchars($editRow['Tel'] ?? ($_POST['Tel'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div>
                    <label>Rue</label>
                    <input name="Rue" maxlength="150"
                           value="<?= htmlspecialchars($editRow['Rue'] ?? ($_POST['Rue'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div style="display:grid;grid-template-columns:2fr 1fr;gap:0 .75rem;">
                    <div>
                        <label>Ville</label>
                        <input name="Ville" maxlength="80"
                               value="<?= htmlspecialchars($editRow['Ville'] ?? ($_POST['Ville'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Code postal</label>
                        <input name="Cp" maxlength="10"
                               value="<?= htmlspecialchars($editRow['Cp'] ?? ($_POST['Cp'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
            </div>
            <div style="margin-top:1rem;display:flex;gap:.75rem;">
                <button type="submit" class="btn btn-green">
                    <?= $editRow ? '💾 Enregistrer' : '➕ Ajouter' ?>
                </button>
                <?php if ($editRow): ?>
                    <a href="stagiaires.php" class="btn btn-gray">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ── Recherche ── -->
    <form method="get" style="display:flex;gap:.6rem;margin-bottom:1rem;align-items:center;">
        <input name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
               placeholder="Rechercher par code, nom, prénom, email…"
               style="max-width:340px;padding:.45rem .75rem;border:1.5px solid #cbd5e1;border-radius:8px;font:inherit;font-size:.88rem;">
        <button type="submit" class="btn btn-blue">Rechercher</button>
        <?php if ($search !== ''): ?>
            <a href="stagiaires.php" class="btn btn-gray">Effacer</a>
        <?php endif; ?>
    </form>

    <!-- ── Liste ── -->
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Tél</th>
                <th>Ville</th>
                <th>Société</th>
                <th>Inscriptions</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stagiaires as $s): ?>
                <tr>
                    <td><code><?= htmlspecialchars($s['CodeStargiaire'], ENT_QUOTES, 'UTF-8') ?></code></td>
                    <td><?= htmlspecialchars($s['Nom'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($s['Prenom'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $s['Email'] ? '<a href="mailto:' . htmlspecialchars($s['Email'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($s['Email'], ENT_QUOTES, 'UTF-8') . '</a>' : '-' ?></td>
                    <td><?= htmlspecialchars($s['Tel'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($s['Ville'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($s['Societe'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <span class="badge <?= (int)$s['NbInscriptions'] > 0 ? 'badge-green' : 'badge-gray' ?>">
                            <?= (int)$s['NbInscriptions'] ?>
                        </span>
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="stagiaires.php?edit=<?= htmlspecialchars($s['CodeStargiaire'], ENT_QUOTES, 'UTF-8') ?>"
                           class="btn btn-blue" style="font-size:.78rem;padding:.22rem .65rem;">Modifier</a>
                        <form method="post" style="display:inline;"
                              onsubmit="return confirm('Supprimer le stagiaire <?= htmlspecialchars(addslashes($s['CodeStargiaire']), ENT_QUOTES, 'UTF-8') ?> et ses inscriptions ?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_delete" value="<?= htmlspecialchars($s['CodeStargiaire'], ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="btn btn-red" style="font-size:.78rem;padding:.22rem .65rem;">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($stagiaires)): ?>
                <tr><td colspan="9" style="color:#64748b;text-align:center;padding:1.25rem;">
                    <?= $search !== '' ? 'Aucun résultat pour « ' . htmlspecialchars($search, ENT_QUOTES, 'UTF-8') . ' ».' : 'Aucun stagiaire enregistré.' ?>
                </td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- ── Pagination ── -->
    <?php if ($totalPages > 1): ?>
    <nav style="display:flex;justify-content:center;gap:.4rem;align-items:center;flex-wrap:wrap;margin-top:1.25rem;">
        <?php if ($page > 1): ?>
            <a href="<?= $buildUrl($page - 1) ?>" class="btn btn-gray" style="padding:.3rem .75rem;">← Préc.</a>
        <?php endif; ?>
        <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
            <?php if ($p === $page): ?>
                <span style="background:#2563eb;color:#fff;padding:.3rem .75rem;border-radius:7px;font-size:.85rem;font-weight:700;"><?= $p ?></span>
            <?php else: ?>
                <a href="<?= $buildUrl($p) ?>" class="btn btn-gray" style="padding:.3rem .75rem;"><?= $p ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="<?= $buildUrl($page + 1) ?>" class="btn btn-gray" style="padding:.3rem .75rem;">Suiv. →</a>
        <?php endif; ?>
        <small style="color:#64748b;font-size:.78rem;">Page <?= $page ?>/<?= $totalPages ?> — <?= $totalCount ?> stagiaire<?= $totalCount > 1 ? 's' : '' ?></small>
    </nav>
    <?php endif; ?>

</div><!-- .admin-content -->
</div><!-- .admin-layout -->
</body>
</html>
