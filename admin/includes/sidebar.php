<?php
// Determine which admin page is active for nav highlighting
$currentFile = basename($_SERVER['SCRIPT_NAME'] ?? '');
$nav = function(string $file, string $label, string $icon = '') use ($currentFile): void {
    $active = $currentFile === $file ? ' class="active"' : '';
    echo '<a href="' . $file . '"' . $active . '>' . ($icon ? $icon . ' ' : '') . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
};
$identifiant = $_SESSION['admin_identifiant'] ?? '';
?>
<aside class="admin-sidebar">
    <div class="sidebar-brand">
        <strong>MFC Admin</strong>
        <span>Espace administration</span>
    </div>
    <nav class="sidebar-nav">
        <div class="sidebar-section">Principal</div>
        <?php $nav('index.php',      'Tableau de bord', '🏠'); ?>
        <div class="sidebar-section">Gestion</div>
        <?php $nav('formations.php', 'Formations',       '📚'); ?>
        <?php $nav('sessions.php',   'Sessions',         '📅'); ?>
        <?php $nav('stagiaires.php', 'Stagiaires',       '👤'); ?>
        <div class="sidebar-section">Site public</div>
        <a href="../index.php" target="_blank">🌐 Voir le site</a>
        <a href="../pages/satisfaction.php" target="_blank">⭐ Satisfaction</a>
    </nav>
    <div class="sidebar-footer">
        <?php if ($identifiant !== ''): ?>
            <span style="display:block;color:#9ca3af;font-size:.76rem;padding:.2rem .85rem .6rem;">
                Connecté : <strong style="color:#c7d2fe;"><?= htmlspecialchars($identifiant, ENT_QUOTES, 'UTF-8') ?></strong>
            </span>
        <?php endif; ?>
        <a href="logout.php">🚪 Déconnexion</a>
    </div>
</aside>
