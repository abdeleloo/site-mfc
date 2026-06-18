<?php
$pageTitle = 'Formateurs - MFC';
$basePath = '..';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/header.php';

$formateurs = [];
$formateursError = '';
try {
    $stmt = $pdo->query('SELECT Matricule, Nom, Prenom, `Spécialité` AS Specialite, Email FROM formateurs ORDER BY Nom');
    $formateurs = $stmt->fetchAll();
} catch (Throwable $e) {
    $formateursError = 'Impossible de charger les formateurs.';
}
?>
<main>
    <h2>Notre équipe</h2>
    <p>La MFC s'appuie sur une équipe pluridisciplinaire de formateurs experts, de managers pédagogiques et de responsables techniques répartis sur ses 5 centres (Lille, Bordeaux, Lyon, Nantes, Nice).</p>

    <h3>Direction générale</h3>
    <div class="grid grid-2">
        <div class="card accent-blue">
            <h4>Aimé EFCE</h4>
            <p><strong>Directeur Général</strong></p>
            <span class="badge badge-blue">Direction</span>
        </div>
        <div class="card accent-blue">
            <h4>Ahmed TATRONCHE</h4>
            <p><strong>Directeur Général Formation</strong></p>
            <span class="badge badge-blue">Direction</span> <span class="badge badge-green">Qualité</span>
        </div>
        <div class="card accent-blue">
            <h4>Benoît FRIC</h4>
            <p><strong>Directeur Administratif et Financier</strong></p>
            <span class="badge badge-blue">Direction</span> <span class="badge badge-orange">Finance</span>
        </div>
        <div class="card accent-blue">
            <h4>Nathalie GARDEAVOUS</h4>
            <p><strong>Directrice des Ressources Humaines</strong></p>
            <span class="badge badge-blue">Direction</span> <span class="badge badge-purple">RH</span>
        </div>
        <div class="card accent-blue">
            <h4>Pierre BUS</h4>
            <p><strong>Directeur Système d'Information</strong></p>
            <span class="badge badge-blue">Direction</span> <span class="badge badge-green">Informatique</span>
        </div>
        <div class="card accent-blue">
            <h4>Stéphanie BENCH</h4>
            <p><strong>Directrice Marketing &amp; Ventes</strong></p>
            <span class="badge badge-blue">Direction</span> <span class="badge badge-orange">Marketing</span>
        </div>
    </div>

    <h3>Pôle Bureautique</h3>
    <p>Responsable : <strong>Albin LOTUS</strong></p>
    <div class="trainer-list">
        <?php
        $poleBur = [
            ['Albin', 'LOTUS',       'Responsable Cellule Bureautique', ['Pédagogie', 'Management']],
            ['Sandrine', 'EMECENE',  'Formatrice Bureautique',          ['Bureautique']],
            ['Ali',     'PRO',       'Formateur Bureautique',           ['Bureautique']],
            ['Julie',   'AKCESSE',   'Formatrice Bureautique',          ['Bureautique']],
            ['Pauline', 'POUSSETTE', 'Formatrice Bureautique',          ['Bureautique']],
            ['Paul',    'HOCHON',    'Formateur Bureautique',           ['Bureautique']],
        ];
        foreach ($poleBur as [$prenom, $nom, $poste, $tags]): ?>
        <div class="trainer-card">
            <div class="trainer-img"><?= strtoupper(mb_substr($prenom,0,1).mb_substr($nom,0,1)) ?></div>
            <h4><?= htmlspecialchars("$prenom $nom", ENT_QUOTES,'UTF-8') ?></h4>
            <p><strong><?= htmlspecialchars($poste, ENT_QUOTES,'UTF-8') ?></strong></p>
            <?php foreach ($tags as $tag): ?>
                <span class="badge badge-orange"><?= htmlspecialchars($tag, ENT_QUOTES,'UTF-8') ?></span>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <h3>Pôle Informatique</h3>
    <p>Responsable : <strong>Denis MERISE</strong></p>
    <div class="trainer-list">
        <?php
        $poleInfo = [
            ['Denis',  'MERISE',   'Responsable Cellule Informatique', ['Management', 'Pédagogie']],
            ['Alain',  'UMELLE',   'Consultant / Formateur',           ['Développement', 'BDD']],
            ['Victor', 'DEGANTT',  'Consultant / Formateur',           ['Développement']],
            ['Gaëlle', 'FLUX',     'Formatrice Développement',         ['Développement', 'Web']],
            ['Jean',   'DATA',     'Consultant Data / BI',             ['Data', 'Analyse']],
            ['Igor',   'VUKULIC',  'Formateur Systèmes',               ['Systèmes', 'Linux']],
        ];
        foreach ($poleInfo as [$prenom, $nom, $poste, $tags]): ?>
        <div class="trainer-card">
            <div class="trainer-img"><?= strtoupper(mb_substr($prenom,0,1).mb_substr($nom,0,1)) ?></div>
            <h4><?= htmlspecialchars("$prenom $nom", ENT_QUOTES,'UTF-8') ?></h4>
            <p><strong><?= htmlspecialchars($poste, ENT_QUOTES,'UTF-8') ?></strong></p>
            <?php foreach ($tags as $tag): ?>
                <span class="badge badge-blue"><?= htmlspecialchars($tag, ENT_QUOTES,'UTF-8') ?></span>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <h3>Pôle Technique</h3>
    <p>Responsable : <strong>Pierre CISCO</strong></p>
    <div class="trainer-list">
        <?php
        $poleTech = [
            ['Pierre',   'CISCO',    'Responsable Cellule Technique',     ['Management', 'Réseaux']],
            ['Jamal',    'OUEB',     'Formateur Réseaux / Web',           ['Réseaux', 'Web']],
            ['Luc',      'HACK',     'Formateur Sécurité',                ['Sécurité', 'Cyber']],
            ['Jean',     'EUSTACHE', 'Formateur Infrastructure',          ['Réseaux', 'Systèmes']],
            ['Carl',     'ZLATAN',   'Formateur Cloud',                   ['Cloud', 'Virtualisation']],
            ['Aurélien', 'GANTZ',    'Formateur Systèmes Unix',           ['Linux', 'Systèmes']],
        ];
        foreach ($poleTech as [$prenom, $nom, $poste, $tags]): ?>
        <div class="trainer-card">
            <div class="trainer-img"><?= strtoupper(mb_substr($prenom,0,1).mb_substr($nom,0,1)) ?></div>
            <h4><?= htmlspecialchars("$prenom $nom", ENT_QUOTES,'UTF-8') ?></h4>
            <p><strong><?= htmlspecialchars($poste, ENT_QUOTES,'UTF-8') ?></strong></p>
            <?php foreach ($tags as $tag): ?>
                <span class="badge badge-green"><?= htmlspecialchars($tag, ENT_QUOTES,'UTF-8') ?></span>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($formateurs)): ?>
    <h3>Intervenants inscrits en base</h3>
    <p class="muted">Formateurs référencés dans le système d'information MFC.</p>
    <div class="trainer-list">
        <?php foreach ($formateurs as $formateur): ?>
            <?php
            $initials = strtoupper(mb_substr($formateur['Prenom'] ?? '', 0, 1) . mb_substr($formateur['Nom'] ?? '', 0, 1));
            $specialite = (string)($formateur['Specialite'] ?? '');
            $specKey = mb_strtolower($specialite);
            $tags = [];
            if (str_contains($specKey, 'réseau') || str_contains($specKey, 'reseau')) $tags[] = ['Réseaux', 'badge-blue'];
            if (str_contains($specKey, 'sécurité') || str_contains($specKey, 'securite') || str_contains($specKey, 'cyber')) $tags[] = ['Sécurité', 'badge-blue'];
            if (str_contains($specKey, 'cloud')) $tags[] = ['Cloud', 'badge-green'];
            if (str_contains($specKey, 'dev') || str_contains($specKey, 'web') || str_contains($specKey, 'php') || str_contains($specKey, 'java')) $tags[] = ['Développement', 'badge-green'];
            if (str_contains($specKey, 'bureau') || str_contains($specKey, 'excel') || str_contains($specKey, 'word')) $tags[] = ['Bureautique', 'badge-orange'];
            if (empty($tags)) $tags[] = ['Formation', 'badge-orange'];
            ?>
            <div class="trainer-card">
                <div class="trainer-img"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></div>
                <h4><?= htmlspecialchars(trim(($formateur['Prenom'] ?? '') . ' ' . ($formateur['Nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></h4>
                <p><strong><?= htmlspecialchars($specialite !== '' ? $specialite : 'Formateur', ENT_QUOTES, 'UTF-8') ?></strong></p>
                <?php foreach ($tags as [$label, $class]): ?>
                    <span class="badge <?= htmlspecialchars($class, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php elseif ($formateursError !== ''): ?>
        <p class="alert"><?= htmlspecialchars($formateursError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
