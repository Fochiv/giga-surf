<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdmin();
$error = '';
$editing = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = (string) ($_POST['action'] ?? '');
    $id = filter_input(INPUT_POST, 'package_id', FILTER_VALIDATE_INT);
    if ($action === 'toggle' && $id) {
        $stmt = db()->prepare('UPDATE packages SET active = CASE WHEN active = 1 THEN 0 ELSE 1 END, updated_at = ? WHERE id = ?');
        $stmt->execute([date('Y-m-d H:i:s'), $id]);
        flash('success', 'Le statut du forfait a été modifié.');
        redirect('Admin/forfaits.php');
    }
    if ($action === 'delete' && $id) {
        $stmt = db()->prepare('DELETE FROM packages WHERE id = ?');
        $stmt->execute([$id]);
        flash('success', 'Forfait supprimé. Les commandes gardent leur récapitulatif historique.');
        redirect('Admin/forfaits.php');
    }
    if ($action === 'save') {
        $operator = (string) ($_POST['operator'] ?? '');
        $name = trim((string) ($_POST['name'] ?? ''));
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_INT);
        $dataLabel = trim((string) ($_POST['data_label'] ?? ''));
        $validity = filter_input(INPUT_POST, 'validity_days', FILTER_VALIDATE_INT);
        $description = trim((string) ($_POST['description'] ?? ''));
        $sortOrder = filter_input(INPUT_POST, 'sort_order', FILTER_VALIDATE_INT) ?: 0;
        if (!in_array($operator, ['Orange', 'MTN', 'Camtel'], true) || $name === '' || textLength($name) > 120 || !$price || $price < 100 || $price > 1000000 || $dataLabel === '' || textLength($dataLabel) > 180 || !$validity || $validity < 1 || $validity > 365 || textLength($description) > 500) {
            $error = 'Vérifiez l’opérateur, le nom, le prix, le volume et la durée de validité.';
            $editing = $_POST;
        } else {
            $now = date('Y-m-d H:i:s');
            if ($id) {
                $stmt = db()->prepare('UPDATE packages SET operator = ?, name = ?, price = ?, data_label = ?, validity_days = ?, description = ?, sort_order = ?, updated_at = ? WHERE id = ?');
                $stmt->execute([$operator, $name, $price, $dataLabel, $validity, $description, $sortOrder, $now, $id]);
                flash('success', 'Forfait modifié.');
            } else {
                $stmt = db()->prepare('INSERT INTO packages (operator, name, price, data_label, validity_days, description, active, sort_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?)');
                $stmt->execute([$operator, $name, $price, $dataLabel, $validity, $description, $sortOrder, $now, $now]);
                flash('success', 'Forfait créé et activé.');
            }
            redirect('Admin/forfaits.php');
        }
    }
}
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
if ($editing === null && $editId) {
    $stmt = db()->prepare('SELECT * FROM packages WHERE id = ?');
    $stmt->execute([$editId]);
    $editing = $stmt->fetch() ?: null;
}
$packages = db()->query('SELECT * FROM packages ORDER BY operator, sort_order, price')->fetchAll();
require_once __DIR__ . '/_layout.php';
adminHeader('Gestion des forfaits', 'packages');
?>
<section class="admin-two-col admin-package-layout">
    <article class="admin-panel">
        <div class="admin-panel-heading"><div><span class="admin-eyebrow"><?= $editing ? 'MODIFICATION' : 'NOUVELLE OFFRE' ?></span><h2><?= $editing ? 'Modifier le forfait' : 'Créer un forfait' ?></h2></div></div>
        <?php if ($error !== ''): ?><div class="admin-alert error"><?= h($error) ?></div><?php endif; ?>
        <form method="post" class="admin-form admin-package-form">
            <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="package_id" value="<?= h($editing['id'] ?? '') ?>">
            <label>Opérateur<select name="operator" required><option value="">Choisir</option><?php foreach (['Orange', 'MTN', 'Camtel'] as $op): ?><option value="<?= h($op) ?>" <?= ($editing['operator'] ?? '') === $op ? 'selected' : '' ?>><?= h($op === 'Camtel' ? 'Camtel Blue' : $op) ?></option><?php endforeach; ?></select></label>
            <label>Nom du forfait<input name="name" maxlength="120" required value="<?= h($editing['name'] ?? '') ?>" placeholder="Ex. blue One M"></label>
            <label>Prix (FCFA)<input type="number" name="price" min="100" max="1000000" step="1" required value="<?= h($editing['price'] ?? '') ?>"></label>
            <label>Volume / détails<input name="data_label" maxlength="180" required value="<?= h($editing['data_label'] ?? '') ?>" placeholder="Ex. 10 Go"></label>
            <label>Validité (jours)<input type="number" name="validity_days" min="1" max="365" required value="<?= h($editing['validity_days'] ?? 30) ?>"></label>
            <label>Ordre d’affichage<input type="number" name="sort_order" min="0" value="<?= h($editing['sort_order'] ?? 0) ?>"></label>
            <label class="full-field">Description courte<textarea name="description" maxlength="500" rows="3"><?= h($editing['description'] ?? '') ?></textarea></label>
            <div class="admin-form-buttons"><button class="admin-primary-button" type="submit"><?= $editing ? 'Enregistrer les changements' : 'Créer le forfait' ?></button><?php if ($editing): ?><a class="admin-secondary-button" href="forfaits.php">Annuler</a><?php endif; ?></div>
        </form>
    </article>
    <article class="admin-panel">
        <div class="admin-panel-heading"><div><span class="admin-eyebrow">CATALOGUE</span><h2><?= count($packages) ?> forfait(s)</h2></div></div>
        <?php if (!$packages): ?><p class="admin-empty">Aucun forfait dans le catalogue.</p><?php else: ?>
        <div class="admin-package-list"><?php foreach ($packages as $package): ?>
            <div class="admin-package-row">
                <div><span class="operator-pill <?= strtolower($package['operator']) ?>"><?= h($package['operator']) ?></span><strong><?= h($package['name']) ?></strong><small><?= h($package['data_label']) ?> · <?= (int) $package['validity_days'] ?> jours · <?= h(formatXaf($package['price'])) ?></small><small class="<?= $package['active'] ? 'active-text' : 'inactive-text' ?>"><?= $package['active'] ? 'Actif sur le site' : 'Masqué des clients' ?></small></div>
                <div class="admin-package-actions"><a href="?edit=<?= (int) $package['id'] ?>">Modifier</a><form method="post"><?= csrf_field() ?><input type="hidden" name="package_id" value="<?= (int) $package['id'] ?>"><input type="hidden" name="action" value="toggle"><button type="submit"><?= $package['active'] ? 'Désactiver' : 'Activer' ?></button></form><form method="post" onsubmit="return confirm('Supprimer ce forfait du catalogue ?')"><?= csrf_field() ?><input type="hidden" name="package_id" value="<?= (int) $package['id'] ?>"><input type="hidden" name="action" value="delete"><button class="danger-link" type="submit">Supprimer</button></form></div>
            </div>
        <?php endforeach; ?></div><?php endif; ?>
    </article>
</section>
<?php adminFooter(); ?>
