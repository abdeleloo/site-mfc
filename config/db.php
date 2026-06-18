<?php
$dbHost    = getenv('MFC_DB_HOST') ?: '127.0.0.1';
$dbName    = getenv('MFC_DB_NAME') ?: 'mfc';
$dbUser    = getenv('MFC_DB_USER') ?: 'root';
$dbPass    = getenv('MFC_DB_PASS') ?: '';
$dbCharset = 'utf8mb4';

// Mot de passe admin pour le mode démo local (WAMP).
// En production, définir la variable d'environnement MFC_ADMIN_PASS_HASH avec un hash bcrypt.
// Pour générer : php -r "echo password_hash('votre_mdp', PASSWORD_DEFAULT);"
define('MFC_ADMIN_PASS_DEMO', 'mfc2026');

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";
$pdo = new PDO($dsn, $dbUser, $dbPass, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
