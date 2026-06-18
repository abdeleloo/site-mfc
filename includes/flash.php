<?php
/**
 * Flash message helpers — store/retrieve one-shot messages in session.
 * Usage:
 *   flash_set('success', 'Votre inscription a été enregistrée.');
 *   // then redirect
 *   header('Location: ...');  exit;
 *
 *   // In the destination page (after include header):
 *   flash_render();
 */

function flash_set(string $type, string $message): void {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $flash = $_SESSION['_flash'] ?? null;
    unset($_SESSION['_flash']);
    return $flash;
}

function flash_render(): void {
    $flash = flash_get();
    if ($flash === null) return;
    $type = $flash['type'] === 'success' ? 'flash-success' : 'flash-error';
    $icon = $flash['type'] === 'success' ? '✓' : '✗';
    echo '<div class="' . $type . '">'
        . '<strong>' . $icon . '</strong> '
        . htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8')
        . '</div>';
}
