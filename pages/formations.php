<?php
$pageTitle = 'Formations - MFC';
$basePath = '..';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/header.php';

$formations      = [];
$categories      = [];
$sessions        = [];
$formationError  = '';
$hasCategorie    = false;
$totalFormations = 0;
$perPage         = 6;
$page            = max(1, (int)($_GET['page'] ?? 1));
$hasTypes = false;
$hasPrix = false;
$hasDuree = false;
$hasNiveau = false;
$hasDescription = false;
$hasSessionCodeFormation = false;
$hasSessionLieu = false;
$hasSessionPlaces = false;
try {
    $stmt = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'formations' AND COLUMN_NAME IN ('Categorie', 'Types', 'Prix', 'DureeJours', 'Niveau', 'Description')");
    $stmt->execute([$dbName]);
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $hasCategorie = in_array('Categorie', $columns, true);
    $hasTypes = in_array('Types', $columns, true);
    $hasPrix = in_array('Prix', $columns, true);
    $hasDuree = in_array('DureeJours', $columns, true);
    $hasNiveau = in_array('Niveau', $columns, true);
    $hasDescription = in_array('Description', $columns, true);

    $selectColumns = ['CodeFormation', 'Nom'];
    if ($hasDescription) {
        $selectColumns[] = 'Description';
    }
    if ($hasDuree) {
        $selectColumns[] = 'DureeJours';
    }
    if ($hasPrix) {
        $selectColumns[] = 'Prix';
    }
    if ($hasNiveau) {
        $selectColumns[] = 'Niveau';
    }
    if ($hasCategorie) {
        $selectColumns[] = 'Categorie';
    }
    if ($hasTypes) {
        $selectColumns[] = 'Types';
    }
    // Comptage total pour la pagination
    $totalFormations = (int)$pdo->query('SELECT COUNT(*) FROM formations')->fetchColumn();
    $totalPages = max(1, (int)ceil($totalFormations / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    $stmt = $pdo->query(
        'SELECT ' . implode(', ', $selectColumns) .
        ' FROM formations ORDER BY Nom LIMIT ' . $perPage . ' OFFSET ' . $offset
    );
    $formations = $stmt->fetchAll();

    $categoryColumn = $hasCategorie ? 'Categorie' : ($hasTypes ? 'Types' : null);
    if ($categoryColumn) {
        $stmt = $pdo->query("SELECT DISTINCT {$categoryColumn} AS Cat FROM Formations WHERE {$categoryColumn} IS NOT NULL AND {$categoryColumn} <> '' ORDER BY {$categoryColumn}");
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    $stmt = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'session' AND COLUMN_NAME IN ('CodeFormation','Lieu','PlacesRestantes','PlacesTotal')");
    $stmt->execute([$dbName]);
    $sessionColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $hasSessionCodeFormation = in_array('CodeFormation', $sessionColumns, true);
    $hasSessionLieu = in_array('Lieu', $sessionColumns, true);
    $hasSessionPlaces = in_array('PlacesRestantes', $sessionColumns, true) || in_array('PlacesTotal', $sessionColumns, true);

    if ($hasSessionCodeFormation) {
        $sql = "SELECT s.`NuméroSession` AS NumeroSession,
                       s.`CodeFormation` AS CodeFormation,
                       s.`DateDebut` AS DateDebut,
                       s.`DateFin` AS DateFin" .
            ($hasSessionLieu ? ", s.`Lieu` AS Lieu" : "") .
            (in_array('PlacesRestantes', $sessionColumns, true) ? ", s.`PlacesRestantes` AS PlacesRestantes" : "") .
            (in_array('PlacesTotal', $sessionColumns, true) ? ", s.`PlacesTotal` AS PlacesTotal" : "") .
            ", f.`Nom` AS FormationNom
                FROM `Session` s
                JOIN `Formations` f ON f.`CodeFormation` = s.`CodeFormation`
                ORDER BY s.`DateDebut`";
        $stmt = $pdo->query($sql);
        $sessions = $stmt->fetchAll();
    }
} catch (Throwable $e) {
    $formationError = 'Impossible de charger les formations.';
}

$typeBadges = [
    // Pôles officiels MFC (d'après les documents)
    'bureautique'  => ['label' => 'Bureautique',              'class' => 'badge-orange'],
    'informatique' => ['label' => 'Informatique & Digital',   'class' => 'badge-blue'],
    'technique'    => ['label' => 'Technique & Réseaux',      'class' => 'badge-green'],
    // Domaines transversaux
    'management'   => ['label' => 'Management & Leadership',  'class' => 'badge-green'],
    'softskills'   => ['label' => 'Soft Skills',              'class' => 'badge-orange'],
    'langues'      => ['label' => 'Langues',                  'class' => 'badge-purple'],
    'sectoriel'    => ['label' => 'Formations sectorielles',  'class' => 'badge-green'],
    'surmesure'    => ['label' => 'Sur mesure',               'class' => 'badge-blue'],
    // Compatibilité données BDD existantes
    'dev'          => ['label' => 'Développement',            'class' => 'badge-blue'],
    'seminaire'    => ['label' => 'Séminaire',                'class' => 'badge-green'],
    'institution'  => ['label' => 'Institution',              'class' => 'badge-orange'],
];
?>
<main>
    <h2>Nos formations</h2>
    <form method="get" data-filter="formations">
        <label for="domaine">Domaine</label>
        <?php
        $filterDomaine = trim((string)($_GET['domaine'] ?? ''));
        $filterMotcle  = trim((string)($_GET['motcle'] ?? ''));
        ?>
        <select id="domaine" name="domaine">
            <option value="">Tous</option>
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <?php $key = strtolower(preg_replace('/\s+/', '', $category)); ?>
                    <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                        <?= $filterDomaine === $key ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="bureautique" <?= $filterDomaine === 'bureautique' ? 'selected' : '' ?>>Bureautique</option>
                <option value="informatique" <?= $filterDomaine === 'informatique' ? 'selected' : '' ?>>Informatique &amp; Digital</option>
                <option value="technique" <?= $filterDomaine === 'technique' ? 'selected' : '' ?>>Technique &amp; Réseaux</option>
                <option value="management" <?= $filterDomaine === 'management' ? 'selected' : '' ?>>Management &amp; Leadership</option>
                <option value="softskills" <?= $filterDomaine === 'softskills' ? 'selected' : '' ?>>Soft Skills</option>
                <option value="langues" <?= $filterDomaine === 'langues' ? 'selected' : '' ?>>Langues</option>
                <option value="sectoriel" <?= $filterDomaine === 'sectoriel' ? 'selected' : '' ?>>Formations sectorielles</option>
                <option value="surmesure" <?= $filterDomaine === 'surmesure' ? 'selected' : '' ?>>Sur mesure</option>
                <option value="dev" <?= $filterDomaine === 'dev' ? 'selected' : '' ?>>Développement</option>
                <option value="seminaire" <?= $filterDomaine === 'seminaire' ? 'selected' : '' ?>>Séminaire</option>
                <option value="institution" <?= $filterDomaine === 'institution' ? 'selected' : '' ?>>Institution</option>
            <?php endif; ?>
        </select>
        <label for="motcle">Mot-clé</label>
        <input id="motcle" name="motcle" type="text" value="<?= htmlspecialchars($filterMotcle, ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit">Filtrer</button>
    </form>
    <h3>Notre méthode pédagogique</h3>
    <p>Cette expertise s'est construite à partir de retours d'expérience de nos consultants experts et pédagogues et grâce à notre cellule de veille et de recherche sur les meilleures pratiques d'apprentissage.</p>
    <p>« La voie de la connaissance » incarne la culture pédagogique de la MFC et s'enrichit des nouvelles technologies pour s'adapter aux nouveaux enjeux des entreprises et des stagiaires.</p>
    <h3>Centres &amp; capacités</h3>
    <div class="grid grid-2">
        <div class="card">
            <h4>Lille <span class="badge badge-blue">Siège</span></h4>
            <p>250 postes informatiques, 30 salles de formation, laboratoire réseau.</p>
        </div>
        <div class="card">
            <h4>Bordeaux</h4>
            <p>120 postes informatiques, 15 salles, espace coworking.</p>
        </div>
        <div class="card">
            <h4>Lyon</h4>
            <p>180 postes informatiques, 20 salles, lab sécurité.</p>
        </div>
        <div class="card">
            <h4>Nantes</h4>
            <p>100 postes informatiques, 12 salles, salle VR.</p>
        </div>
        <div class="card">
            <h4>Nice</h4>
            <p>80 postes informatiques, 10 salles de formation.</p>
        </div>
    </div>
    <h3>Catalogue des formations</h3>
    <div class="formation-list" id="formation-list">
        <?php if ($formationError !== ''): ?>
            <p><?= htmlspecialchars($formationError, ENT_QUOTES, 'UTF-8') ?></p>
        <?php elseif (empty($formations)): ?>
            <p>Aucune formation disponible pour le moment.</p>
        <?php else: ?>
            <?php foreach ($formations as $formation): ?>
                <?php
                $rawCategory = (string)($formation['Categorie'] ?? $formation['Types'] ?? '');
                $typeKey     = strtolower(preg_replace('/\s+/', '', trim($rawCategory)));
                // Si la catégorie avec espaces/accents ne correspond pas à une clé badge,
                // on essaie le champ Types (valeur courte normalisée : 'informatique', 'technique', 'bureautique'…)
                if (!isset($typeBadges[$typeKey])) {
                    $typesKey = strtolower(trim((string)($formation['Types'] ?? '')));
                    if (isset($typeBadges[$typesKey])) {
                        $typeKey = $typesKey;
                    }
                }
                $badge = $typeBadges[$typeKey] ?? ['label' => $rawCategory !== '' ? $rawCategory : 'Programme', 'class' => 'badge-blue'];
                $level = (string)($formation['Niveau'] ?? '');
                $duration = $formation['DureeJours'] ?? null;
                $price = $formation['Prix'] ?? null;
                $desc = (string)($formation['Description'] ?? '');
                ?>
                <div class="formation-item" data-domain="<?= htmlspecialchars($typeKey, ENT_QUOTES, 'UTF-8') ?>" data-name="<?= htmlspecialchars($formation['Nom'], ENT_QUOTES, 'UTF-8') ?>">
                    <h4><a href="formation_detail.php?code=<?= htmlspecialchars($formation['CodeFormation'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($formation['Nom'], ENT_QUOTES, 'UTF-8') ?></a></h4>
                    <p><?= htmlspecialchars($desc !== '' ? $desc : 'Programme proposé par le centre MFC.', ENT_QUOTES, 'UTF-8') ?></p>
                    <span class="badge <?= htmlspecialchars($badge['class'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($badge['label'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if ($level !== ''): ?>
                        <span class="badge badge-blue"><?= htmlspecialchars($level, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                    <?php if ($duration !== null && (string)$duration !== ''): ?>
                        <span class="badge badge-green"><?= htmlspecialchars((string)$duration, ENT_QUOTES, 'UTF-8') ?> jours</span>
                    <?php endif; ?>
                    <?php if ($price !== null && (string)$price !== ''): ?>
                        <span class="badge badge-orange"><?= htmlspecialchars(number_format((float)$price, 0, ',', ' '), ENT_QUOTES, 'UTF-8') ?> €</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (!$formationError && ($totalFormations ?? 0) > $perPage): ?>
    <?php
    $totalPages = max(1, (int)ceil($totalFormations / $perPage));
    $buildUrl = function(int $p) use ($filterDomaine, $filterMotcle): string {
        $params = ['page' => $p];
        if ($filterDomaine !== '') $params['domaine'] = $filterDomaine;
        if ($filterMotcle  !== '') $params['motcle']  = $filterMotcle;
        return '?' . http_build_query($params);
    };
    ?>
    <nav style="display:flex;justify-content:center;gap:.5rem;align-items:center;margin:1.5rem 0;">
        <?php if ($page > 1): ?>
            <a href="<?= htmlspecialchars($buildUrl($page - 1), ENT_QUOTES, 'UTF-8') ?>"
               style="padding:.4rem .9rem;border:1px solid #cbd5e1;border-radius:6px;text-decoration:none;color:#1e3a8a;">← Précédent</a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="<?= htmlspecialchars($buildUrl($p), ENT_QUOTES, 'UTF-8') ?>"
               style="padding:.4rem .9rem;border:1px solid <?= $p === $page ? '#1e3a8a' : '#cbd5e1' ?>;border-radius:6px;text-decoration:none;
                      background:<?= $p === $page ? '#1e3a8a' : 'transparent' ?>;color:<?= $p === $page ? '#fff' : '#1e3a8a' ?>;"><?= $p ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="<?= htmlspecialchars($buildUrl($page + 1), ENT_QUOTES, 'UTF-8') ?>"
               style="padding:.4rem .9rem;border:1px solid #cbd5e1;border-radius:6px;text-decoration:none;color:#1e3a8a;">Suivant →</a>
        <?php endif; ?>
        <span style="font-size:.85rem;color:#64748b;">
            Page <?= $page ?> / <?= $totalPages ?> (<?= $totalFormations ?> formations)
        </span>
    </nav>
    <?php endif; ?>

    <h3>Calendrier des sessions</h3>
    <table class="session-table">
        <thead>
            <tr>
                <th>Formation</th>
                <th>Dates</th>
                <th>Durée</th>
                <th>Lieu</th>
                <th>Places</th>
                <th>Prix</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($sessions)): ?>
                <?php foreach ($sessions as $session): ?>
                    <?php
                    $formationRow = null;
                    foreach ($formations as $f) {
                        if (($f['CodeFormation'] ?? '') === ($session['CodeFormation'] ?? '')) {
                            $formationRow = $f;
                            break;
                        }
                    }
                    $dur = $formationRow['DureeJours'] ?? null;
                    $pr = $formationRow['Prix'] ?? null;
                    $places = '';
                    if (array_key_exists('PlacesRestantes', $session) && array_key_exists('PlacesTotal', $session) && $session['PlacesTotal'] !== null && $session['PlacesTotal'] !== '') {
                        $places = (string)($session['PlacesRestantes'] ?? '') . '/' . (string)$session['PlacesTotal'];
                    } elseif (array_key_exists('PlacesRestantes', $session)) {
                        $places = (string)$session['PlacesRestantes'];
                    }
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($session['FormationNom'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(($session['DateDebut'] ?? '') . ' → ' . ($session['DateFin'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($dur !== null && (string)$dur !== '' ? ((string)$dur . ' j') : '—', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($session['Lieu'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($places !== '' ? $places : '—', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($pr !== null && (string)$pr !== '' ? number_format((float)$pr, 0, ',', ' ') . ' €' : '—', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><a href="../pages/inscription.php?session=<?= htmlspecialchars((string)($session['NumeroSession'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-blue" style="font-size:.8rem;padding:.3rem .7rem;">S'inscrire</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;color:#64748b;">Aucune session planifiée pour le moment.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
