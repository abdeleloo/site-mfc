<?php
session_start();

$pageTitle = 'Connexion admin - MFC';
$allowedIdentifiants = [
    'GDIRECTION',
    'GINFO',
    'GTECHNICIENS',
    'GSTAGIAIRES',
    'GFORMATEURS',
    'GADMINISTRATIF',
    'GCOMPTA',
    'GRH',
    'GCADRES',
    'GLILLE',
    'GNANTES',
    'GNICE',
    'GLYON',
    'GBORDEAUX',
    'ST00',
    'ST09',
    'ST20',
    'ST29',
    'ST30',
    'ST39',
    'FORS00',
    'FORS09',
    'FORS20',
    'FORS39'
];

$loginAttempts = $_SESSION['login_attempts'] ?? 0;
$loginLocked   = $_SESSION['login_locked']   ?? false;
$error = '';

// Authentification :
//   Mode production  → variable d'env MFC_ADMIN_PASS_HASH (hash bcrypt)
//   Mode démo/local  → constante MFC_ADMIN_PASS_DEMO définie dans config/db.php
require_once __DIR__ . '/../config/db.php';
$passwordHash = getenv('MFC_ADMIN_PASS_HASH') ?: '';
$demoMode     = ($passwordHash === '');
$info = $demoMode
    ? 'Mode démo — mot de passe : mfc2026'
    : 'Mode sécurisé (hash bcrypt). Verrouillage après 3 tentatives.';

if ($loginLocked) {
    $error = 'Compte verrouillé après 3 tentatives. Fermez et rouvrez le navigateur.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$loginLocked) {
    $identifiant = strtoupper(trim($_POST['identifiant'] ?? ''));
    $password    = (string)($_POST['password'] ?? '');

    if ($identifiant === '' || $password === '') {
        $error = 'Identifiant et mot de passe requis.';
    } elseif (!in_array($identifiant, $allowedIdentifiants, true)) {
        $loginAttempts++;
        $_SESSION['login_attempts'] = $loginAttempts;
        $error = 'Identifiant invalide.';
    } else {
        // Vérification du mot de passe selon le mode actif
        $loginOk = $demoMode
            ? ($password === (defined('MFC_ADMIN_PASS_DEMO') ? MFC_ADMIN_PASS_DEMO : 'mfc2026'))
            : password_verify($password, $passwordHash);

        if ($loginOk) {
            $_SESSION['admin_identifiant'] = $identifiant;
            $_SESSION['login_attempts']    = 0;
            $_SESSION['login_locked']      = false;
            header('Location: index.php');
            exit;
        } else {
            $loginAttempts++;
            $_SESSION['login_attempts'] = $loginAttempts;
            $error = 'Mot de passe incorrect.';
        }
    }

    if ($loginAttempts >= 3) {
        $_SESSION['login_locked'] = true;
        $loginLocked = true;
        $error = 'Compte verrouillé après 3 tentatives.';
    }
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
<body class="login-page">
    <main>
        <h1>Connexion administrateur</h1>
        <?php if ($error !== ''): ?>
            <p class="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php elseif ($info !== ''): ?>
            <p class="muted"><?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form method="post">
            <label for="identifiant">Identifiant</label>
            <input id="identifiant" name="identifiant" type="text" required>
            <label for="password">Mot de passe</label>
            <input id="password" name="password" type="password" required>
            <button type="submit">Se connecter</button>
    </main>
</body>
</html>
