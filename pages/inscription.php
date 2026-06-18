<?php
$pageTitle = 'Inscription - MFC';
$basePath = '..';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/flash.php';

$formations = [];
$centres   = [];
$sessions  = [];
$formError  = '';
$formSuccess = '';
$hasSessionCodeFormation   = false;
$hasSessionPlacesRestantes = false;

// Pré-remplissage pour les stagiaires connectés
$prefill = [
    'code' => '', 'nom' => '', 'prenom' => '',
    'rue' => '', 'ville' => '', 'cp' => '',
    'tel' => '', 'email' => '', 'societe' => '',
];
if (($_SESSION['user_role'] ?? '') === 'stagiaire' && !empty($_SESSION['user_code'])) {
    try {
        $stmt = $pdo->prepare(
            'SELECT CodeStargiaire, Nom, Prenom, Email, Tel, Rue, Ville, Cp, `Societé` AS Societe
             FROM stagiaires WHERE CodeStargiaire = ? LIMIT 1'
        );
        $stmt->execute([$_SESSION['user_code']]);
        $row = $stmt->fetch();
        if ($row) {
            $prefill = [
                'code'    => $row['CodeStargiaire'] ?? '',
                'nom'     => $row['Nom'] ?? '',
                'prenom'  => $row['Prenom'] ?? '',
                'rue'     => $row['Rue'] ?? '',
                'ville'   => $row['Ville'] ?? '',
                'cp'      => $row['Cp'] ?? '',
                'tel'     => $row['Tel'] ?? '',
                'email'   => $row['Email'] ?? '',
                'societe' => $row['Societe'] ?? '',
            ];
        }
    } catch (Throwable $e) {
        // non bloquant
    }
}

try {
    $stmt = $pdo->query('SELECT CodeFormation, Nom FROM formations ORDER BY Nom');
    $formations = $stmt->fetchAll();

    $stmt = $pdo->query('SELECT CodeCentre, NomCentre FROM centre_de_formation ORDER BY NomCentre');
    $centres = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'session' AND COLUMN_NAME IN ('CodeFormation','PlacesRestantes')");
    $stmt->execute([$dbName]);
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $hasSessionCodeFormation = in_array('CodeFormation', $columns, true);
    $hasSessionPlacesRestantes = in_array('PlacesRestantes', $columns, true);

    if ($hasSessionCodeFormation) {
        $stmt = $pdo->query("SELECT s.`NuméroSession` AS NumeroSession,
                                    s.`CodeFormation` AS CodeFormation,
                                    s.`DateDebut` AS DateDebut,
                                    s.`DateFin` AS DateFin,
                                    s.`Lieu` AS Lieu" .
            ($hasSessionPlacesRestantes ? ", s.`PlacesRestantes` AS PlacesRestantes" : "") .
            ", f.`Nom` AS FormationNom
                               FROM `Session` s
                               JOIN `Formations` f ON f.`CodeFormation` = s.`CodeFormation`
                               ORDER BY s.`DateDebut`");
        $sessions = $stmt->fetchAll();
    }
} catch (Throwable $e) {
    $formError = 'Impossible de charger les formations.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $formError = 'Requête invalide. Veuillez recharger la page et réessayer.';
    }
    $code = trim((string)($_POST['code'] ?? ''));
    $nom = trim((string)($_POST['nom'] ?? ''));
    $prenom = trim((string)($_POST['prenom'] ?? ''));
    $rue = trim((string)($_POST['rue'] ?? ''));
    $ville = trim((string)($_POST['ville'] ?? ''));
    $cp = trim((string)($_POST['cp'] ?? ''));
    $tel = trim((string)($_POST['tel'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $societe = trim((string)($_POST['societe'] ?? ''));
    $formation     = trim((string)($_POST['formation'] ?? ''));
    $numeroSession = trim((string)($_POST['session'] ?? ''));
    $codeCentre    = trim((string)($_POST['centre'] ?? ''));

    if ($formError === '' && ($code === '' || $nom === '' || $prenom === '' || $email === '' || $formation === '')) {
        $formError = 'Merci de compléter les champs obligatoires (code, nom, prénom, email, formation).';
    } elseif ($formError === '') {
        try {
            $pdo->beginTransaction();

            // 1. Upsert stagiaire
            $stmt = $pdo->prepare(
                'INSERT INTO stagiaires (CodeStargiaire, Nom, Prenom, Rue, Ville, Cp, Tel, Email, `Societé`)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                   Nom=VALUES(Nom), Prenom=VALUES(Prenom), Rue=VALUES(Rue),
                   Ville=VALUES(Ville), Cp=VALUES(Cp), Tel=VALUES(Tel),
                   Email=VALUES(Email), `Societé`=VALUES(`Societé`)'
            );
            $stmt->execute([$code, $nom, $prenom, $rue, $ville, $cp, $tel, $email, $societe]);

            // 2. Rattachement stagiaire ↔ centre (asso_2) — si un centre est sélectionné
            if ($codeCentre !== '') {
                $stmt = $pdo->prepare(
                    'INSERT IGNORE INTO asso_2 (CodeStargiaire, CodeCentre) VALUES (?, ?)'
                );
                $stmt->execute([$code, $codeCentre]);
            }

            // 3. Fiche d'inscription
            $numeroInscription = 'INS' . date('YmdHis');
            $stmt = $pdo->prepare(
                'INSERT INTO fiche_inscription (NumeroInsciption, Date_) VALUES (?, ?)'
            );
            $stmt->execute([$numeroInscription, date('Y-m-d')]);

            // 4. Lien formation ↔ inscription (asso_5)
            $stmt = $pdo->prepare(
                'INSERT INTO asso_5 (CodeFormation, NumeroInsciption) VALUES (?, ?)'
            );
            $stmt->execute([$formation, $numeroInscription]);

            // 5. Lien session ↔ inscription (asso_7) — si session disponible
            if ($hasSessionCodeFormation && $numeroSession !== '') {
                $stmt = $pdo->prepare(
                    'INSERT INTO asso_7 (`NuméroSession`, NumeroInsciption) VALUES (?, ?)'
                );
                $stmt->execute([$numeroSession, $numeroInscription]);
            }

            $pdo->commit();
            flash_set('success', 'Votre inscription a été enregistrée avec succès (réf. ' . $numeroInscription . ').');
            header('Location: inscription.php');
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $formError = "Impossible d'enregistrer l'inscription. Vérifiez que votre code stagiaire est valide.";
        }
    }
}
require __DIR__ . '/../includes/header.php';
?>
<main>
    <h2>Inscription à une formation</h2>

    <section class="page-section">
        <h3>Avant votre inscription</h3>
        <p><strong>3 semaines avant le début de la formation</strong>, vous recevrez un dossier complet comprenant :</p>
        <ul>
            <li>Le programme détaillé de la formation</li>
            <li>Un plan d'accès au centre MFC concerné</li>
            <li>Les moyens de transport disponibles</li>
            <li>Les parkings les plus proches</li>
            <li>Les hébergements conseillés</li>
        </ul>
        <p>Le jour J, un <strong>petit-déjeuner d'accueil</strong> est servi à 8h. Les sessions se déroulent de 9h à 17h-18h (minimum 7 heures de formation par jour).</p>
    </section>

    <section class="page-section">
        <h3>Financement &amp; prise en charge</h3>
        <p>Nos formations peuvent être financées selon différents dispositifs :</p>
        <ul>
            <li><strong>Plan de développement des compétences</strong> de votre entreprise</li>
            <li><strong>OPCO</strong> (Opérateur de Compétences)</li>
            <li><strong>CPF</strong> (Compte Personnel de Formation)</li>
            <li><strong>Financement individuel</strong></li>
        </ul>
        <p>Contactez-nous pour étudier la solution la mieux adaptée à votre situation.</p>
    </section>

    <h3>Formulaire d'inscription</h3>
    <?php flash_render(); ?>
    <?php if ($formError !== ''): ?>
        <p class="alert"><?= htmlspecialchars($formError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form method="post">
        <?= csrf_field() ?>
        <?php
        $v = function(string $key) use ($prefill): string {
            return htmlspecialchars($_POST[$key] ?? $prefill[$key], ENT_QUOTES, 'UTF-8');
        };
        $isConnected = ($_SESSION['user_role'] ?? '') === 'stagiaire' && $prefill['code'] !== '';
        ?>
        <label for="code">Code stagiaire</label>
        <input id="code" name="code" type="text" required value="<?= $v('code') ?>"
               <?= $isConnected ? 'readonly style="background:#f1f5f9;"' : '' ?>>
        <label for="nom">Nom</label>
        <input id="nom" name="nom" type="text" required value="<?= $v('nom') ?>">
        <label for="prenom">Prénom</label>
        <input id="prenom" name="prenom" type="text" required value="<?= $v('prenom') ?>">
        <label for="rue">Rue</label>
        <input id="rue" name="rue" type="text" value="<?= $v('rue') ?>">
        <label for="ville">Ville</label>
        <input id="ville" name="ville" type="text" value="<?= $v('ville') ?>">
        <label for="cp">Code postal</label>
        <input id="cp" name="cp" type="text" value="<?= $v('cp') ?>">
        <label for="tel">Téléphone</label>
        <input id="tel" name="tel" type="text" value="<?= $v('tel') ?>">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" required value="<?= $v('email') ?>">
        <label for="societe">Société</label>
        <input id="societe" name="societe" type="text" value="<?= $v('societe') ?>">
        <label for="centre">Centre de formation</label>
        <select id="centre" name="centre">
            <option value="">— Sélectionner un centre (optionnel) —</option>
            <?php foreach ($centres as $c): ?>
                <option value="<?= htmlspecialchars($c['CodeCentre'], ENT_QUOTES, 'UTF-8') ?>"
                    <?= ($_POST['centre'] ?? '') === $c['CodeCentre'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['NomCentre'] ?? $c['CodeCentre'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="formation">Formation <span style="color:#dc2626;">*</span></label>
        <select id="formation" name="formation" required>
            <?php
            // Pré-sélection depuis l'URL (?formation=FOR007) ou depuis le POST
            $preselFormation = $_POST['formation'] ?? ($_GET['formation'] ?? '');
            foreach ($formations as $row):
            ?>
                <option value="<?= htmlspecialchars($row['CodeFormation'], ENT_QUOTES, 'UTF-8') ?>"
                    <?= $preselFormation === $row['CodeFormation'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['Nom'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($sessions)): ?>
            <label for="session">Session</label>
            <select id="session" name="session" required>
                <?php foreach ($sessions as $s): ?>
                    <?php
                    $label = ($s['FormationNom'] ?? '') . ' — ' . ($s['DateDebut'] ?? '') . ' → ' . ($s['DateFin'] ?? '');
                    if (!empty($s['Lieu'])) {
                        $label .= ' — ' . $s['Lieu'];
                    }
                    if (array_key_exists('PlacesRestantes', $s) && $s['PlacesRestantes'] !== null && $s['PlacesRestantes'] !== '') {
                        $label .= ' — places restantes : ' . $s['PlacesRestantes'];
                    }
                    ?>
                    <option value="<?= htmlspecialchars((string)($s['NumeroSession'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
        <button type="submit" style="margin-top:1rem;padding:.65rem 1.75rem;background:#1e3a8a;color:#fff;border:none;border-radius:8px;font-size:1rem;font-weight:600;cursor:pointer;">
            S'inscrire
        </button>
    </form>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
