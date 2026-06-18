<?php
$pageTitle = 'Connexion - MFC';
$basePath = '..';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/header.php';

$error = '';

function tableHasPasswordColumn(PDO $pdo, string $dbName, string $table): bool {
    $stmt = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = 'PasswordHash' LIMIT 1");
    $stmt->execute([$dbName, $table]);
    return (bool)$stmt->fetchColumn();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';
    $code = trim((string)($_POST['code'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if (!in_array($role, ['stagiaire', 'formateur'], true)) {
        $error = 'Rôle invalide.';
    } elseif ($code === '' || $email === '') {
        $error = 'Code/Matricule et email sont requis.';
    } else {
        try {
            if ($role === 'stagiaire') {
                $table   = 'stagiaires';
                $codeCol = 'CodeStargiaire';
            } else {
                $table   = 'formateurs';
                $codeCol = 'Matricule';
            }

            $hasPwd = tableHasPasswordColumn($pdo, $dbName, $table);
            $columns = "$codeCol, Nom, Prenom, Email" . ($hasPwd ? ", PasswordHash" : "");
            $stmt = $pdo->prepare("SELECT $columns FROM $table WHERE $codeCol = ? AND Email = ? LIMIT 1");
            $stmt->execute([$code, $email]);
            $row = $stmt->fetch();

            if (!$row) {
                $error = 'Identifiants introuvables.';
            } elseif ($hasPwd && !password_verify($password, (string)($row['PasswordHash'] ?? ''))) {
                $error = 'Mot de passe incorrect.';
            } else {
                $_SESSION['user_role'] = $role;
                $_SESSION['user_code'] = $code;
                $_SESSION['user_nom'] = $row['Nom'] ?? '';
                $_SESSION['user_prenom'] = $row['Prenom'] ?? '';
                header('Location: espace.php');
                exit;
            }
        } catch (Throwable $e) {
            $error = 'Erreur de connexion.';
        }
    }
}
?>
<main>
    <h2>Connexion</h2>
    <?php if ($error !== ''): ?>
        <p class="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php else: ?>
        <p class="muted">Choisissez votre rôle et identifiez-vous. Le mot de passe est requis uniquement si configuré en base.</p>
    <?php endif; ?>
    <form method="post">
        <?= csrf_field() ?>
        <label for="role">Rôle</label>
        <select id="role" name="role" required>
            <option value="stagiaire">Stagiaire</option>
            <option value="formateur">Formateur</option>
        </select>
        <label for="code">Code stagiaire / Matricule</label>
        <input id="code" name="code" type="text" required>
        <label for="email">Email</label>
        <input id="email" name="email" type="email" required>
        <label for="password">Mot de passe (si configuré)</label>
        <input id="password" name="password" type="password">
        <button type="submit">Se connecter</button>
   