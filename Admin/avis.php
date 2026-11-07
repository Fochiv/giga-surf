<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdmin();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = (string) ($_POST['action'] ?? '');
    $id = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
    if ($action === 'delete' && $id) {
        $stmt = db()->prepare('DELETE FROM reviews WHERE id = ?');
        $stmt->execute([$id]);
        flash('success', 'Avis supprimé.');
        redirect('Admin/avis.php');
    }
    if ($action === 'visibility' && $id) {
        $stmt = db()->prepare('UPDATE reviews SET visible = CASE WHEN visible = 1 THEN 0 ELSE 1 END WHERE id = ?');
        $stmt->execute([$id]);
        flash('success', 'La visibilité de l’avis a été modifiée.');
        redirect('Admin/avis.php');
    }
    if ($action === 'save') {
        $name = trim((string) ($_POST['customer_name'] ?? ''));
        $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
        $body = trim((string) ($_POST['body'] ?? ''));
        $visible = isset($_POST['visible']) ? 1 : 0;
        if (textLength($name) < 2 || textLength($name) > 100 || !$rating || $rating < 1 || $rating > 5 || textLength($body) < 5 || textLength($body) > 1000) {
            $error = 'Vérifiez le nom, la note et le texte de l’avis.';
        } elseif ($id) {
            $stmt = db()->prepare('UPDATE reviews SET customer_name = ?, rating = ?, body = ?, visible = ? WHERE id = ?');
            $stmt->execute([$name, $rating, $body, $visible, $id]);
            flash('success', 'Avis modifié.');
            redirect('Admin/avis.php');
        } else {
            $stmt = db()->prepare('INSERT INTO reviews (customer_name, rating, body, visible, created_at) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$name, $rating, $body, $visible, date('Y-m-d H:i:s')]);
            flash('success', 'Avis ajouté.');
            redirect('Admin/avis.php');
        }
    }
}
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
$editing = null;
if ($editId) {
    $stmt = db()->prepare('SELECT * FROM reviews WHERE id = ?');
    $stmt->execute([$editId]);
    $editing = $stmt->fetch() ?: null;
}
$reviews = db()->query('SELECT * FROM reviews ORDER BY visible ASC, created_at DESC LIMIT 300')->fetchAll();
require_once __DIR__ . '/_layout.php';
adminHeader('Avis clients', 'reviews');
?>
<section class="admin-two-col">
    <article class="admin-panel">
        <div class="admin-panel-heading"><div><span class="admin-eyebrow"><?= $editing ? 'MODIFICATION' : 'NOUVEL AVIS' ?></span><h2><?= $editing ? 'Modifier un avis' : 'Ajouter un avis' ?></h2></div></div>
        <?php if ($error !== ''): ?><div class="admin-alert error"><?= h($error) ?></div><?php endif; ?>
        <form method="post" class="admin-form">
            <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="review_id" value="<?= h($editing['id'] ?? '') ?>">
            <label>Nom affiché<input name="customer_name" maxlength="100" required value="<?= h($editing['customer_name'] ?? '') ?>"></label>
            <label>Note<select name="rating" required><?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>" <?= (int) ($editing['rating'] ?? 5) === $i ? 'selected' : '' ?>><?= $i ?> / 5</option><?php endfor; ?></select></label>
            <label class="full-field">Avis<textarea name="body" maxlength="1000" rows="4" required><?= h($editing['body'] ?? '') ?></textarea></label>
            <label class="admin-checkbox"><input type="checkbox" name="visible" value="1" <?= !isset($editing['visible']) || (int) $editing['visible'] === 1 ? 'checked' : '' ?>> Publié sur le site</label>
            <div class="admin-form-buttons"><button class="admin-primary-button" type="submit"><?= $editing ? 'Enregistrer' : 'Ajouter l’avis' ?></button><?php if ($editing): ?><a class="admin-secondary-button" href="avis.php">Annuler</a><?php endif; ?></div>
        </form>
    </article>
    <article class="admin-panel">
        <div class="admin-panel-heading"><div><span class="admin-eyebrow">MODÉRATION</span><h2><?= count($reviews) ?> avis</h2></div></div>
        <?php if (!$reviews): ?><p class="admin-empty">Les avis clients s’afficheront ici.</p><?php else: ?>
        <div class="admin-review-list"><?php foreach ($reviews as $review): ?>
          <div class="admin-review"><div class="admin-review-head"><div><strong><?= h($review['customer_name']) ?></strong><small><?= (int) $review['rating'] ?>/5 · <?= h(formatDate($review['created_at'])) ?></small></div><span class="admin-status <?= $review['visible'] ? 'successful' : 'pending' ?>"><?= $review['visible'] ? 'Publié' : 'À modérer' ?></span></div><p><?= nl2br(h($review['body'])) ?></p><div class="admin-package-actions"><a href="?edit=<?= (int) $review['id'] ?>">Modifier</a><form method="post"><?= csrf_field() ?><input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>"><input type="hidden" name="action" value="visibility"><button type="submit"><?= $review['visible'] ? 'Masquer' : 'Publier' ?></button></form><form method="post" onsubmit="return confirm('Supprimer cet avis ?')"><?= csrf_field() ?><input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>"><input type="hidden" name="action" value="delete"><button class="danger-link" type="submit">Supprimer</button></form></div></div>
        <?php endforeach; ?></div><?php endif; ?>
    </article>
</section>
<?php adminFooter(); ?>
