<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$pageTitle = $pageTitle ?? 'MFC';
$basePath = $basePath ?? '';
$basePath = trim($basePath);
$basePath = rtrim($basePath, '/');
$prefix = $basePath === '' ? '' : $basePath . '/';
$scriptPath = trim($_SERVER['SCRIPT_NAME'] ?? '', '/');
$rootPrefix = $scriptPath !== '' ? '/' . explode('/', $scriptPath)[0] . '/' : '/';
$cssHref = $rootPrefix . 'assets/css/style.css';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars($cssHref, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <header id="main-header">
        <div class="header-inner">
            <a href="<?= $prefix ?>index.php" class="logo-link">
                <div class="logo-symbol">MFC</div>
                <div class="logo-text">
                    <h1>MFC</h1>
                    <span>Maison de la Formation Continue</span>
                </div>
            </a>
            <button class="hamburger" id="hamburger" aria-label="Menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
            <nav id="main-nav">
                <ul>
                    <li><a href="<?= $prefix ?>index.php">Accueil</a></li>
                    <li><a href="<?= $prefix ?>pages/qui-sommes-nous.php">Qui sommes-nous ?</a></li>
                    <li><a href="<?= $prefix ?>pages/formations.php">Formations</a></li>
                    <li><a href="<?= $prefix ?>pages/formateurs.php">Formateurs</a></li>
                    <li><a href="<?= $prefix ?>pages/inscription.php">Inscription</a></li>
                    <li><a href="<?= $prefix ?>pages/contact.php">Contact</a></li>
                    <?php if (!empty($_SESSION['user_role'])): ?>
                        <li><a href="<?= $prefix ?>pages/espace.php">Mon espace</a></li>
                        <li><a href="<?= $prefix ?>pages/logout.php">Déconnexion</a></li>
                    <?php else: ?>
                        <li><a href="<?= $prefix ?>pages/connexion.php">Connexion</a></li>
                    <?php endif; ?>
                </ul>
         