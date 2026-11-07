<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
if (isAdmin()) {
    redirect('Admin/index.php');
}
if (!adminExists()) {
    redirect('Admin/setup.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $key = (string) (getenv('SESSION_SECRET') ?: getenv('DB_PASSWORD') ?: 'gigasurf-local-key');
    $identity = hash_hmac('sha256', $ip . '|' . lowerText($username), $key);
    $cutoff = date('Y-m-d H:i:s', time() - 900);
    $cleanup = db()->prepare('DELETE FROM login_attempts WHERE attempted_at < ?');
    $cleanup->execute([$cutoff]);
    $countStmt = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE identity_hash = ? AND attempted_at >= ?');
    $countStmt->execute([$identity, $cutoff]);
    if ((int) $countStmt->fetchColumn() >= 5) {
        $error = 'Trop de tentatives. Réessayez dans 15 minutes.';
    } else {
        $stmt = db()->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password_hash'])) {
            $delete = db()->prepare('DELETE FROM login_attempts WHERE identity_hash = ?');
            $delete->execute([$identity]);
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            redirect('Admin/index.php');
        }
        $insert = db()->prepare('INSERT INTO login_attempts (identity_hash, attempted_at) VALUES (?, ?)');
        $insert->execute([$identity, date('Y-m-d H:i:s')]);
        $error = 'Identifiant ou mot de passe incorrect.';
    }
}
require_once __DIR__ . '/_layout.php';
adminHeader('Connexion administrateur', '', true);
?>
<section class="admin-auth-card">
    <span class="admin-auth-kicker">ESPACE SÉCURISÉ</span>
    <h1>Bon retour</h1>
    <p>Connectez-vous pour gérer les forfaits et les commandes.</p>
    <?php if ($error !== ''): ?><div class="admin-alert error" role="alert"><?= h($error) ?></div><?php endif; ?>
    <form method="post" class="admin-form">
        <?= csrf_field() ?>
        <label>Identifiant<input name="username" autocomplete="username" required maxlength="40" autofocus></label>
        <label>Mot de passe<input type="password" name="password" autocomplete="current-password" required></label>
        <button class="admin-primary-button" type="submit">Se connecter</button>
    </form>
    <a class="admin-back-link" href="../index.php">Retour au site</a>
</section>
<?php adminFooter(true); ?>
