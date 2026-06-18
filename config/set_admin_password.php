<?php
/**
 * Utilitaire de génération de hash bcrypt pour le mot de passe admin MFC.
 *
 * USAGE :
 *   1. Ouvrir ce fichier dans XAMPP via http://localhost/site_mfc/config/set_admin_password.php
 *   2. Copier le hash généré
 *   3. Définir la variable d'environnement MFC_ADMIN_PASS_HASH dans Apache :
 *      → Dans httpd.conf ou .htaccess : SetEnv MFC_ADMIN_PASS_HASH "$2y$12$xxxx..."
 *   4. SUPPRIMER ce fichier du serveur une fois configuré.
 *
 * SÉCURITÉ : Ne jamais laisser ce fichier accessible en production.
 */

// Seulement accessible en local / dev
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    die('Accès refusé.');
}

$password = $_POST['password'] ?? '';
$hash = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (strlen($password) < 8) {
        $error = 'Le mot de passe doit faire au moins 8 caractères.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Configuration mot de passe admin — MFC</title>
    <style>
        body { font-family: monospace; max-width: 700px; margin: 2rem auto; padding: 1rem; background: #0f172a; color: #e2e8f0; }
        h1 { color: #60a5fa; }
        input[type=password] { width: 100%; padding: .6rem; border-radius: 6px; border: 1px solid #334155; background: #1e293b; color: #e2e8f0; font: inherit; margin: .5rem 0 1rem; }
        button { padding: .6rem 1.4rem; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; }
        .hash { background: #1e293b; border: 1px solid #334155; padding: 1rem; border-radius: 6px; word-break: break-all; color: #34d399; margin-top: 1rem; }
        .warning { background: #7f1d1d; padding: .75rem 1rem; border-radius: 6px; color: #fca5a5; margin-bottom: 1rem; }
        .htaccess { background: #1e293b; border: 1px solid #64748b; padding: 1rem; border-radius: 6px; color: #93c5fd; margin-top: 1rem; white-space: pre; }
    </style>
</head>
<body>
    <h1>🔐 Générateur de hash bcrypt — Admin MFC</h1>
    <div class="warning">⚠️ Outil de développement — supprimer ce fichier après configuration.</div>

    <form method="post">
        <label>Nouveau mot de passe admin :</label>
        <input type="password" name="password" minlength="8" required placeholder="Minimum 8 caractères">
        <button type="submit">Générer le hash</button>
    </form>

    <?php if ($error !== ''): ?>
        <p style="color:#f87171;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($hash !== ''): ?>
        <h2>Hash généré :</h2>
        <div class="hash"><?= htmlspecialchars($hash, ENT_QUOTES, 'UTF-8') ?></div>

        <h2>Configuration Apache (httpd.conf ou .htaccess) :</h2>
        <div class="htaccess">SetEnv MFC_ADMIN_PASS_HASH "<?= htmlspecialchars($hash, ENT_QUOTES, 'UTF-8') ?>"</div>

        <p>Une fois défini, vérifiez dans <code>admin/login.php</code> que <code>getenv('MFC_ADMIN_PASS_HASH')</code> retourne votre hash.</p>
        <p><strong>Hash actuel de 'mfc2026' (pour tests) :</strong><br>
        <small style="color:#94a3b8;">$2y$12$NmrxqMKDQiJD2VPfEHYak.K43sh1hDepp6Y8R6ocK0.vNPihbRe4C</small></p>
    <?php endif; ?>
</body>
</html>
