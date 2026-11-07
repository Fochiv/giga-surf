<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdmin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $paused = isset($_POST['sales_paused']) ? '1' : '0';
    $noticeFr = trim((string) ($_POST['notice_fr'] ?? ''));
    $noticeEn = trim((string) ($_POST['notice_en'] ?? ''));
    if (textLength($noticeFr) > 300 || textLength($noticeEn) > 300) {
        flash('error', 'Les annonces sont limitées à 300 caractères.');
    } else {
        $save = db()->prepare('UPDATE settings SET setting_value = ? WHERE setting_key = ?');
        $add = db()->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)');
        foreach (['sales_paused' => $paused, 'notice_fr' => $noticeFr, 'notice_en' => $noticeEn] as $key => $value) {
            $save->execute([$value, $key]);
            if ($save->rowCount() === 0) {
                $check = db()->prepare('SELECT COUNT(*) FROM settings WHERE setting_key = ?');
                $check->execute([$key]);
                if ((int) $check->fetchColumn() === 0) $add->execute([$key, $value]);
            }
        }
        flash('success', 'Paramètres enregistrés.');
        redirect('Admin/parametres.php');
    }
}
$settings = [];
foreach (db()->query('SELECT setting_key, setting_value FROM settings')->fetchAll() as $row) $settings[$row['setting_key']] = $row['setting_value'];
$fapshiReady = fapshi()->isConfigured();
require_once __DIR__ . '/_layout.php';
adminHeader('Paramètres', 'settings');
?>
<section class="admin-two-col">
    <article class="admin-panel">
        <div class="admin-panel-heading"><div><span class="admin-eyebrow">BOUTIQUE</span><h2>Disponibilité et annonces</h2></div></div>
        <form method="post" class="admin-form">
            <?= csrf_field() ?>
            <label class="admin-checkbox"><input type="checkbox" name="sales_paused" value="1" <?= ($settings['sales_paused'] ?? '0') === '1' ? 'checked' : '' ?>> Suspendre temporairement les commandes</label>
            <label class="full-field">Annonce en français<textarea name="notice_fr" maxlength="300" rows="3"><?= h($settings['notice_fr'] ?? '') ?></textarea></label>
            <label class="full-field">Annonce en anglais<textarea name="notice_en" maxlength="300" rows="3"><?= h($settings['notice_en'] ?? '') ?></textarea></label>
            <div class="admin-form-buttons"><button class="admin-primary-button" type="submit">Enregistrer les paramètres</button></div>
        </form>
    </article>
    <article class="admin-panel">
        <div class="admin-panel-heading"><div><span class="admin-eyebrow">PAIEMENTS</span><h2>Connexion Fapshi</h2></div></div>
        <div class="admin-connection-state <?= $fapshiReady ? 'connected' : 'disconnected' ?>"><span class="connection-dot"></span><div><strong><?= $fapshiReady ? 'Identifiants configurés' : 'Identifiants manquants' ?></strong><small><?= $fapshiReady ? 'Les demandes de paiement peuvent être envoyées.' : 'Les paiements resteront désactivés tant que les nouvelles clés ne sont pas ajoutées aux Secrets Replit.' ?></small></div></div>
        <p class="admin-muted">Les identifiants API et le secret webhook sont chargés depuis la configuration serveur et ne sont jamais affichés ici.</p>
        <ol class="admin-setup-list"><li>Renouvelez les identifiants API dans votre espace Fapshi.</li><li>Ajoutez les nouvelles valeurs dans les Secrets Replit, ou dans un fichier de configuration privé sur Hostinger.</li><li>Configurez l’URL webhook de Fapshi avec l’adresse de ce site suivie de <code>/webhook.php</code>.</li></ol>
        <a class="admin-secondary-button inline-button" href="https://docs.fapshi.com/fr/api-reference/endpoint/webhook" target="_blank" rel="noopener">Documentation des webhooks</a>
    </article>
</section>
<?php adminFooter(); ?>
