<?php
/**
 * Utilitaires CSRF — MFC
 * Usage :
 *   - Dans un formulaire : echo csrf_field();
 *   - Dans le handler POST : if (!csrf_verify()) { /* rejeter * / }
 */

function csrf_token(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="'
         . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8')
         . '">';
}

function csrf_verify(): bool {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $submitted = (string)($_POST['csrf_token'] ?? '');
    $stored    = (string)($_SESSION['csrf_token'] ?? '');
    return $stored !== '' && hash_equals($stored, $submitted);
}
