<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdmin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $action = (string) ($_POST['action'] ?? '');
    $stmt = db()->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
    $stmt->execute([(int) $orderId]);
    $order = $stmt->fetch();
    if ($order && $action === 'deliver' && $order['payment_status'] === 'SUCCESSFUL') {
        $update = db()->prepare("UPDATE orders SET delivery_status = 'delivered', delivered_at = ?, updated_at = ? WHERE id = ? AND payment_status = 'SUCCESSFUL'");
        $now = date('Y-m-d H:i:s');
        $update->execute([$now, $now, $order['id']]);
        flash('success', 'Commande marquée comme livrée. Le client verra le nouveau statut dans son suivi.');
    } elseif ($order && $action === 'verify') {
        if (refreshOrderPayment($order)) flash('success', 'Le statut a été vérifié auprès de Fapshi.');
        else flash('warning', 'Le paiement n’a pas pu être confirmé ou son statut est inchangé.');
    } else {
        flash('error', 'Action impossible. Vérifiez que le paiement est confirmé avant la livraison.');
    }
    redirect('Admin/commandes.php?filter=' . rawurlencode((string) ($_POST['filter'] ?? 'paid')));
}
$filter = (string) ($_GET['filter'] ?? 'paid');
$search = trim((string) ($_GET['q'] ?? ''));
$conditions = [];
$params = [];
if ($filter === 'paid') {
    $conditions[] = "payment_status = 'SUCCESSFUL'";
} elseif ($filter === 'failed') {
    $conditions[] = "payment_status IN ('FAILED', 'EXPIRED')";
} elseif ($filter === 'pending') {
    $conditions[] = "payment_status IN ('CREATED', 'PENDING', 'UNKNOWN')";
}
if ($search !== '') {
    $conditions[] = '(public_code LIKE ? OR customer_name LIKE ? OR recipient_phone LIKE ?)';
    $term = '%' . $search . '%';
    array_push($params, $term, $term, $term);
}
$sql = 'SELECT * FROM orders' . ($conditions ? ' WHERE ' . implode(' AND ', $conditions) : '') . ' ORDER BY created_at DESC LIMIT 200';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
require_once __DIR__ . '/_layout.php';
adminHeader('Commandes', 'orders');
?>
<section class="admin-panel">
    <div class="admin-filter-row">
        <div class="admin-tabs">
            <?php foreach (['paid' => 'Payées', 'pending' => 'En attente', 'failed' => 'Échouées', 'all' => 'Toutes'] as $key => $label): ?>
                <a class="<?= $filter === $key ? 'active' : '' ?>" href="?filter=<?= h($key) ?>"><?= h($label) ?></a>
            <?php endforeach; ?>
        </div>
        <form class="admin-search" method="get"><input type="hidden" name="filter" value="<?= h($filter) ?>"><label class="visually-hidden" for="order-search">Rechercher une commande</label><input id="order-search" name="q" value="<?= h($search) ?>" placeholder="Référence, client, téléphone"><button type="submit">Rechercher</button></form>
    </div>
    <p class="admin-muted"><?= $filter === 'paid' ? 'La liste principale affiche uniquement les paiements confirmés.' : 'Résultats filtrés pour la gestion des commandes.' ?></p>
    <?php if (!$orders): ?><p class="admin-empty">Aucune commande dans cette vue.</p>
    <?php else: ?>
    <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Commande / client</th><th>Forfait</th><th>Paiement</th><th>Montant</th><th>Date</th><th>Action</th></tr></thead><tbody>
    <?php foreach ($orders as $order): ?>
      <tr>
        <td><strong><?= h($order['public_code']) ?></strong><small><?= h($order['customer_name']) ?> · recharge <?= h($order['recipient_phone']) ?></small><small>Paiement <?= h($order['payment_operator']) ?> · <?= h($order['payer_phone']) ?></small></td>
        <td><?= h($order['operator'] . ' · ' . $order['package_name']) ?><small><?= h(formatXaf($order['amount'])) ?></small></td>
        <td><span class="admin-status <?= strtolower(h($order['payment_status'])) ?>"><?= h(paymentStatusLabel($order['payment_status'])) ?></span><small><?= h($order['fapshi_trans_id'] ?: 'Référence Fapshi en attente') ?></small></td>
        <td><?= h(formatXaf($order['amount'])) ?></td>
        <td><?= h(formatDate($order['created_at'])) ?></td>
        <td class="admin-actions">
          <?php if ($order['payment_status'] === 'SUCCESSFUL' && $order['delivery_status'] !== 'delivered'): ?>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>"><input type="hidden" name="filter" value="<?= h($filter) ?>"><input type="hidden" name="action" value="deliver"><button class="admin-primary-button compact" type="submit">Livrer</button></form>
          <?php elseif ($order['delivery_status'] === 'delivered'): ?><span class="admin-status delivered">Livré</span>
          <?php elseif (in_array($order['payment_status'], ['PENDING', 'CREATED', 'UNKNOWN'], true) && $order['fapshi_trans_id']): ?>
            <form method="post" action="../verify-payment.php"><?= csrf_field() ?><input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>"><input type="hidden" name="return_to" value="admin"><button class="admin-secondary-button compact" type="submit">Vérifier</button></form>
          <?php else: ?><span class="admin-muted">—</span><?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div>
    <?php endif; ?>
</section>
<?php adminFooter(); ?>
