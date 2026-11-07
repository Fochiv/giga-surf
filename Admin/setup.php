<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (adminExists()) {
    redirect('Admin/login.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $username = trim((string) ($_POST['username'] ?? 'admin'));
    $password = (string) ($_POST['password'] ?? '');
    $confirmation = (string) ($_POST['password_confirm'] ?? '');
    if (!preg_match('/^[a-zA-Z0-9._-]{3,40}$/', $username)) {
        $error = 'Choisissez un identifiant de 3 à 40 caractères (lettres, chiffres, point, tiret ou tiret bas).';
    } elseif (strlen($password) < 12) {
        $error = 'Le nouveau mot de passe doit contenir au moins 12 caractères.';
    } elseif (!hash_equals($password, $confirmation)) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        try {
            db()->beginTransaction();
            $claim = db()->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)');
            $claim->execute(['admin_setup_claim', 'claimed']);
            if (adminExists()) {
                throw new RuntimeException('An administrator account already exists.');
            }
            $stmt = db()->prepare('INSERT INTO admin_users (username, password_hash, created_at, updated_at) VALUES (?, ?, ?, ?)');
            $now = date('Y-m-d H:i:s');
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $now, $now]);
            db()->commit();
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) db()->lastInsertId();
            $_SESSION['admin_username'] = $username;
            redirect('Admin/index.php');
        } catch (PDOException $e) {
            if (db()->inTransaction()) db()->rollBack();
            error_log('Giga Surf first admin setup failed: ' . $e->getMessage());
            $error = 'La création n’a pas abouti. Vérifiez que la base de données est disponible, puis réessayez.';
        } catch (Throwable $e) {
            if (db()->inTransaction()) db()->rollBack();
            $error = 'Le compte admin a déjà été créé ou la configuration doit être vérifiée.';
        }
    }
}
require_once __DIR__ . '/_layout.php';
adminHeader('Créer le compte admin', '', true);
?>
<section class="admin-auth-card">
    <span class="admin-auth-kicker">PREMIÈRE CONFIGURATION</span>
    <h1>Créer votre accès sécurisé</h1>
    <p>Ce formulaire disparaîtra après la création du premier compte administrateur.</p>
    <?php if ($error !== ''): ?><div class="admin-alert error"><?= h($error) ?></div><?php endif; ?>
    <form method="post" class="admin-form">
        <?= csrf_field() ?>
        <label>Identifiant<input name="username" value="<?= h($_POST['username'] ?? 'admin') ?>" autocomplete="username" required minlength="3" maxlength="40"></label>
        <label>Nouveau mot de passe<input type="password" name="password" autocomplete="new-password" required minlength="12"></label>
        <label>Confirmer le mot de passe<input type="password" name="password_confirm" autocomplete="new-password" required minlength="12"></label>
        <button class="admin-primary-button" type="submit">Créer le compte admin</button>
    </form>
    <a class="admin-back-link" href="../index.php">Retour au site</a>
</section>
<?php adminFooter(true); ?>
