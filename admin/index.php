<?php
session_start();
$pageTitle = 'Tableau de bord - MFC';
$identifiant = $_SESSION['admin_identifiant'] ?? '';
if ($identifiant === '') {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../config/db.php';

$stats = ['formations' => 0, 'stagiaires' => 0, 'formateurs' => 0, 'sessions' => 0, 'inscriptions' => 0];
try {
    foreach ([
        'formations'   => 'SELECT COUNT(*) FROM formations',
        'stagiaires'   => 'SELECT COUNT(*) FROM stagiaires',
        'formateurs'   => 'SELECT COUNT(*) FROM formateurs',
        'sessions'     => 'SELECT COUNT(*) FROM session',
        'inscriptions' => 'SELECT COUNT(*) FROM fiche_inscription',
    ] as $key => $sql) {
        $stats[$key] = (int)$pdo->query($sql)->fetchColumn();
    }
} catch (Throwable $e) {
}

$prochainsSessions = [];
try {
    $prochainsSessions = $pdo->query(
        "SELECT s.`NuméroSession` AS NumeroSession,
                f.Nom AS FormationNom,
                s.DateDebut, s.DateFin,
                s.Lieu,
                s.PlacesRestantes, s.PlacesTotal,
                COUNT(DISTINCT a7.NumeroInsciption) AS NbInscrits
         FROM session s
         LEFT JOIN formations f  ON f.CodeFormation       = s.CodeFormation
         LEFT JOIN asso_7 a7     ON a7.`NuméroSession`    = s.`NuméroSession`
         GROUP BY s.`NuméroSession`, f.Nom, s.DateDebut, s.DateFin, s.Lieu, s.PlacesRestantes, s.PlacesTotal
         ORDER BY s.DateDebut
         LIMIT 10"
    )->fetchAll();
} catch (Throwable $e) {
}

/*
 * Dernières inscriptions : fiche_inscription → asso_5 → formations.
 * Note : pas de lien direct stagiaires ↔ fiche_inscription dans le schéma actuel.
 */
$derniersInscrits = [];
try {
    $derniersInscrits = $pdo->query(
        "SELECT fi.NumeroInsciption, fi.Date_,
                GROUP_CONCAT(DISTINCT f.Nom ORDER BY f.Nom SEPARATOR ', ') AS FormationsNoms
         FROM fiche_inscription fi
         JOIN asso_5 a5 ON a5.NumeroInsciption = fi.NumeroInsciption
         JOIN formations f ON f.CodeFormation = a5.CodeFormation
         GROUP BY fi.NumeroInsciption, fi.Date_
         ORDER BY fi.Date_ DESC
         LIMIT 5"
    )->fetchAll();
} catch (Throwable $e) {
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
    <?php require __DIR__ . '/includes/sidebar.php'; ?>
    <div class="admin-content">
        <div class="admin-topbar">
            <h1>🏠 Tableau de bord</h1>
            <span class="user-info">Connecté : <strong><?= htmlspecialchars($identifiant, ENT_QUOTES, 'UTF-8') ?></strong></span>
        </div>

        <section>
            <h2>Vue d'ensemble</h2>
            <div class="stats-grid">
                <div class="stat-box">
                    <strong><?= $stats['formations'] ?></strong>
                    <span>Formations</span>
                </div>
                <div class="stat-box">
                    <strong><?= $stats['sessions'] ?></strong>
                    <span>Sessions</span>
                </div>
                <div class="stat-box">
                    <strong><?= $stats['stagiaires'] ?></strong>
                    <span>Stagiaires</span>
                </div>
                <div class="stat-box">
                    <strong><?= $stats['formateurs'] ?></strong>
                    <span>Formateurs</span>
                </div>
                <div class="stat-box">
                    <strong><?= $stats['inscriptions'] ?></strong>
                    <span>Inscriptions</span>
                </div>
            </div>
        </section>

        <?php if (!empty($prochainsSessions)): ?>
        <section>
            <h2>Sessions planifiées</h2>
            <table>
                <thead>
                    <tr>
                        <th>Formation</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Lieu</th>
                        <th>Places</th>
                        <th>Inscrits</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prochainsSessions as $s): ?>
                        <?php
                        $places = ($s['PlacesTotal'] ?? 0) > 0
                            ? ($s['PlacesRestantes'] ?? '?') . '/' . $s['PlacesTotal']
                            : '-';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($s['FormationNom'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($s['DateDebut'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($s['DateFin'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($s['Lieu'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($places, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge badge-<?= (int)($s['NbInscrits'] ?? 0) > 0 ? 'green' : 'blue' ?>"><?= (int)($s['NbInscrits'] ?? 0) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <?php endif; ?>

        <?php
        $satisfactionStats = [];
        $satisfactionTotal = 0;
        try {
            $rows = $pdo->query("SELECT note, COUNT(*) AS nb FROM satisfaction GROUP BY note ORDER BY note DESC")->fetchAll();
            foreach ($rows as $r) {
                $satisfactionStats[(int)$r['note']] = (int)$r['nb'];
                $satisfactionTotal += (int)$r['nb'];
            }
        } catch (Throwable $e) {}
        ?>
        <?php if ($satisfactionTotal > 0): ?>
        <section>
            <h2>Satisfaction (<small><?= $satisfactionTotal ?> réponse<?= $satisfactionTotal > 1 ? 's' : '' ?></small>)</h2>
            <table>
                <thead><tr><th>Note</th><th>Libellé</th><th>Réponses</th><th>%</th></tr></thead>
                <tbody>
                    <?php
                    $labels = [5 => 'Excellent', 4 => 'Très bien', 3 => 'Bien', 2 => 'Moyen', 1 => 'Insatisfaisant'];
                    foreach ([5,4,3,2,1] as $n):
                        $nb = $satisfactionStats[$n] ?? 0;
                        $pct = $satisfactionTotal > 0 ? round($nb / $satisfactionTotal * 100) : 0;
                    ?>
                    <tr>
                        <td><strong><?= $n ?>/5</strong></td>
                        <td><?= $labels[$n] ?></td>
                        <td><?= $nb ?></td>
                        <td>
                            <div style="background:#e2e8f0;border-radius:4px;height:10px;width:120px;display:inline-block;vertical-align:middle;">
                                <div style="background:#2563eb;border-radius:4px;height:10px;width:<?= $pct ?>%;"></div>
                            </div>
                            <?= $pct ?>%
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php
            $weightedSum = 0;
            foreach ($satisfactionStats as $note => $nb) {
                $weightedSum += $note * $nb;
            }
            $moyenne = round($weightedSum / $satisfactionTotal, 1);
            ?>
            <p style="margin-top:.75rem;">Moyenne globale&nbsp;: <strong><?= $moyenne ?>/5</strong></p>
        </section>
        <?php endif; ?>

        <?php if (!empty($derniersInscrits)): ?>
        <section>
            <h2>Dernières inscriptions</h2>
            <table>
                <thead>
                    <tr>
                        <th>N° Inscription</th>
                        <th>Formation(s)</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($derniersInscrits as $ins): ?>
                        <tr>
                            <td><?= htmlspecialchars($ins['NumeroInsciption'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($ins['FormationsNoms'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($ins['Date_'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <?php endif; ?>

        <section>
            <h2>Accès rapide</h2>
            <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
                <a href="formations.php" class="btn btn-blue">📚 Formations</a>
                <a href="sessions.php"   class="btn btn-blue">📅 Sessions</a>
                <a href="stagiaires.php" class="btn btn-blue">👤 Stagiaires</a>
                <a href="../pages/satisfaction.php" target="_blank" class="btn btn-orange">⭐ Satisfaction</a>
                <a href="../index.php"   target="_blank" class="btn btn-gray">🌐 Site public</a>
            </div>
        </section>
    </div><!-- .admin-content -->
</div><!-- .admin-layout -->
</body>
</html>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       