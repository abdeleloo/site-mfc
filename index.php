<?php
$pageTitle = 'Accueil - MFC';
$basePath = '';
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/header.php';

// Chiffres clés dynamiques depuis la BDD
$nbFormations = 0;
$nbStagiaires = 0;
$nbSessions   = 0;
$nbFormateurs = 0;
$satisfaction = null;
try {
    $nbFormations = (int)$pdo->query('SELECT COUNT(*) FROM formations')->fetchColumn();
    $nbStagiaires = (int)$pdo->query('SELECT COUNT(*) FROM stagiaires')->fetchColumn();
    $nbSessions   = (int)$pdo->query('SELECT COUNT(*) FROM session')->fetchColumn();
    $nbFormateurs = (int)$pdo->query('SELECT COUNT(*) FROM formateurs')->fetchColumn();
    $row = $pdo->query('SELECT ROUND(AVG(note),1) AS moy FROM satisfaction')->fetch();
    if ($row && $row['moy'] !== null) {
        $satisfaction = (float)$row['moy'];
    }
} catch (Throwable $e) {}

// Formations vedette (3 max)
$formationsVedette = [];
try {
    $formationsVedette = $pdo->query(
        "SELECT CodeFormation, Nom, Categorie, Types
         FROM formations ORDER BY Nom LIMIT 3"
    )->fetchAll();
} catch (Throwable $e) {}
?>

<!-- ── Hero Section ── -->
<section class="hero">
    <div class="hero-inner">
        <span class="hero-badge">BTS SIO SLAM — Projet E6</span>
        <h2>La voie de la connaissance</h2>
        <p>Maison de la Formation Continue — Centre de référence pour la formation professionnelle des adultes depuis 1998.</p>
        <div class="hero-actions">
            <a href="pages/formations.php" class="btn-hero btn-hero-primary">Découvrir les formations</a>
            <a href="pages/inscription.php" class="btn-hero btn-hero-secondary">S'inscrire</a>
        </div>
        <div class="hero-stats">
            <div class="hero-stat">
                <strong data-count="<?= $nbFormations > 0 ? $nbFormations : 50 ?>" data-suffix="+">0</strong>
                <span>Formations</span>
            </div>
            <div class="hero-stat">
                <strong data-count="<?= $nbStagiaires > 0 ? $nbStagiaires : 5000 ?>" data-suffix="+">0</strong>
                <span>Stagiaires</span>
            </div>
            <div class="hero-stat">
                <strong data-count="<?= $nbFormateurs > 0 ? $nbFormateurs : 18 ?>" data-suffix="">0</strong>
                <span>Formateurs experts</span>
            </div>
            <?php if ($satisfaction !== null): ?>
            <div class="hero-stat">
                <strong><?= number_format($satisfaction, 1, ',', '') ?>/5</strong>
                <span>Satisfaction</span>
            </div>
            <?php else: ?>
            <div class="hero-stat">
                <strong data-count="5" data-suffix=" centres">0</strong>
                <span>Villes en France</span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<main class="wide">
    <section class="page-section">
        <h2 class="page-title section-title">Votre partenaire formation</h2>
        <div class="grid grid-2">
            <div class="card">
                <h3>Depuis 1998</h3>
                <p>La MFC est un espace de formation entièrement réservé aux adultes. Notre organisation permet d'optimiser les temps de formation et de maximiser l'impact pédagogique.</p>
                <div class="highlight">
                    <p><strong>« La voie de la connaissance »</strong> — La formation est un investissement, pas une dépense.</p>
                </div>
            </div>
            <div class="card">
                <h3>Notre méthode</h3>
                <p>Notre culture pédagogique repose sur la capitalisation des savoirs fondamentaux et des savoir-faire développés en permanence grâce à notre cellule de veille et de recherche.</p>
                <p><a href="pages/qui-sommes-nous.php">En savoir plus →</a></p>
            </div>
        </div>
    </section>

    <section class="page-section">
        <h3 class="section-title">Nos 3 pôles de formation</h3>
        <div class="grid grid-3">
            <div class="card accent-orange">
                <h4>🖥️ Pôle Bureautique</h4>
                <p>Word, Excel, PowerPoint, Access et outils collaboratifs. Du débutant à l'expert.</p>
                <span class="badge badge-orange">Bureautique</span>
            </div>
            <div class="card accent-blue">
                <h4>💻 Pôle Informatique</h4>
                <p>Développement web, bases de données, data science et intelligence artificielle.</p>
                <span class="badge badge-blue">Informatique &amp; Digital</span>
            </div>
            <div class="card accent-green">
                <h4>🔧 Pôle Technique</h4>
                <p>Réseaux, cybersécurité, cloud, virtualisation, systèmes Linux/Windows.</p>
                <span class="badge badge-green">Technique &amp; Réseaux</span>
            </div>
        </div>
    </section>

    <section class="page-section">
        <h3 class="section-title">Formations disponibles</h3>
        <div class="news-grid">
            <?php if (!empty($formationsVedette)):
                $badgeMap = [
                    'Informatique & Digital' => 'badge-blue',
                    'Bureautique'            => 'badge-orange',
                    'Technique & Réseaux'    => 'badge-green',
                ];
                foreach ($formationsVedette as $f):
                    $cat    = $f['Categorie'] ?? $f['Types'] ?? '';
                    $bClass = $badgeMap[$cat] ?? 'badge-blue';
            ?>
            <article class="card">
                <h4>
                    <a href="pages/formation_detail.php?code=<?= htmlspecialchars($f['CodeFormation'], ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($f['Nom'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </h4>
                <span class="badge <?= htmlspecialchars($bClass, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($cat !== '' ? $cat : 'Formation', ENT_QUOTES, 'UTF-8') ?>
                </span>
                <p style="margin-top:.6rem;"><a href="pages/inscription.php?formation=<?= htmlspecialchars($f['CodeFormation'], ENT_QUOTES, 'UTF-8') ?>">S'inscrire →</a></p>
            </article>
            <?php endforeach; else: ?>
            <article class="card">
                <h4>Catalogue formations</h4>
                <p>Découvrez notre catalogue complet sur les pôles Bureautique, Informatique et Technique.</p>
                <a href="pages/formations.php" class="badge badge-blue">Voir le catalogue →</a>
            </article>
            <article class="card">
                <h4>Inscription en ligne</h4>
                <p>Inscrivez-vous directement en ligne. Nos équipes prennent en charge votre dossier sous 48h.</p>
                <a href="pages/inscription.php" class="badge badge-green">S'inscrire →</a>
            </article>
            <?php endif; ?>
        </div>
        <p style="margin-top:1rem;text-align:right;">
            <a href="pages/formations.php">