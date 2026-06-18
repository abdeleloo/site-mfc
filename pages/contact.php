<?php
$pageTitle = 'Contact - MFC';
$basePath = '..';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/header.php';

$contactError = '';
$contactSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $contactError = 'Requête invalide. Veuillez recharger la page et réessayer.';
    } else {
    $nom     = trim((string)($_POST['nom'] ?? ''));
    $email   = trim((string)($_POST['email'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));

    if ($nom === '' || $email === '' || $message === '') {
        $contactError = 'Merci de remplir tous les champs.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contactError = 'Adresse email invalide.';
    } else {
        $contactSuccess = 'Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.';
    }
    } // end csrf check
}
?>
<main>
    <h2>Contactez-nous</h2>
    <div class="card" style="margin-bottom:1.5rem;">
        <h3>Contact commercial &amp; inscription</h3>
        <p>Pour toute demande concernant nos formations, l'inscription ou la prise en charge (OPCO, CPF…) :</p>
        <p>Email&nbsp;: <a href="mailto:contact@mfc.fr">contact@mfc.fr</a></p>
        <p>Directrice Marketing &amp; Ventes&nbsp;: <strong>Stéphanie BENCH</strong></p>
    </div>

    <h3>Nos 5 centres</h3>
    <div class="grid grid-2" style="margin-bottom:1.5rem;">
        <div class="card">
            <h4>Lille <span class="badge badge-blue">Siège</span></h4>
            <p>123 Rue de la Formation<br>59000 Lille</p>
            <p>Responsable SI&nbsp;: <strong>Pierre BUS</strong></p>
            <p>250 postes — 30 salles</p>
        </div>
        <div class="card">
            <h4>Bordeaux</h4>
            <p>45 Avenue des Technologies<br>33000 Bordeaux</p>
            <p>Responsable&nbsp;: <strong>Béatrice CHATEAU</strong></p>
            <p>120 postes — 15 salles</p>
        </div>
        <div class="card">
            <h4>Lyon</h4>
            <p>78 Rue de l'Innovation<br>69000 Lyon</p>
            <p>Responsable&nbsp;: <strong>Francesco POLI</strong></p>
            <p>180 postes — 20 salles</p>
        </div>
        <div class="card">
            <h4>Nantes</h4>
            <p>12 Boulevard du Digital<br>44000 Nantes</p>
            <p>Responsable&nbsp;: <strong>Zandezu DESU</strong></p>
            <p>100 postes — 12 salles</p>
        </div>
        <div class="card">
            <h4>Nice</h4>
            <p>8 Avenue de la Méditerranée<br>06000 Nice</p>
            <p>Responsable&nbsp;: <strong>Abder MOMO</strong></p>
            <p>80 postes — 10 salles</p>
        </div>
    </div>

    <h3>Qualité &amp; conformité</h3>
    <p>Sous la responsabilité d'<strong>Anicée BIENFAIT</strong> (Responsable Qualité), chaque formation est évaluée dans les <strong>7 jours</strong> suivant sa clôture, avec un plan d'action correctif si nécessaire.</p>
    <h3>Formulaire de contact</h3>
    <?php if ($contactError !== ''): ?>
        <p class="alert"><?= htmlspecialchars($contactError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php elseif ($contactSuccess !== ''): ?>
        <p class="muted"><?= htmlspecialchars($contactSuccess, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($contactSuccess === ''): ?>
    <form method="post">
        <?= csrf_field() ?>
        <label for="nom">Nom</label>
        <input id="nom" name="nom" type="text" required value="<?= htmlspecialchars($_POST['nom'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" required value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <label for="message">Message</label>
        <textarea id="message" name="message" rows="5" required>