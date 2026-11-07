<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Suivre mes commandes';
$currentPage = 'orders';
$trackedPhone = normalizePhone((string) ($_SESSION['tracked_phone'] ?? ''));

if (!validCameroonPhone($trackedPhone) && !empty($_SESSION['tracked_order_id'])) {
    $legacyStmt = db()->prepare('SELECT recipient_phone FROM orders WHERE id = ? LIMIT 1');
    $legacyStmt->execute([(int) $_SESSION['tracked_order_id']]);
    $legacyPhone = normalizePhone((string) ($legacyStmt->fetchColumn() ?: ''));
    if (validCameroonPhone($legacyPhone)) {
        $trackedPhone = $legacyPhone;
        $_SESSION['tracked_phone'] = $trackedPhone;
    }
}

$orders = [];
if (validCameroonPhone($trackedPhone)) {
    $stmt = db()->prepare('SELECT * FROM orders WHERE recipient_phone = ? ORDER BY id DESC LIMIT 20');
    $stmt->execute([$trackedPhone]);
    $orders = $stmt->fetchAll();
}

$statusLabels = [
    'pending' => ['En attente de paiement', 'Awaiting payment', 'pending'],
    'paid' => ['Paiement confirmé · livraison en cours', 'Payment confirmed · delivery in progress', 'paid'],
    'delivered' => ['Forfait livré', 'Bundle delivered', 'delivered'],
    'failed' => ['Échec du paiement', 'Payment failed', 'failed'],
];
$pageFlash = null;
require __DIR__ . '/includes/header.php';
?>
<section class="shell catalog-head">
  <a class="back-link" href="<?= h(url('index.php')) ?>"><?= giga_svg('back') ?><span data-fr="Retour à l’accueil" data-en="Back home">Retour à l’accueil</span></a>
  <div class="operator-title"><div><span class="eyebrow" data-fr="Toujours au courant" data-en="Stay in the loop">Toujours au courant</span><h1 data-fr="Suivre mes commandes" data-en="Track my orders">Suivre mes commandes</h1><p data-fr="Saisissez le numéro à recharger pour retrouver les commandes qui lui sont associées." data-en="Enter the top-up number to find its associated orders.">Saisissez le numéro à recharger pour retrouver les commandes qui lui sont associées.</p></div></div>
</section>
<section class="shell form-layout">
  <form class="form-panel" method="post" action="<?= h(url('track-order.php')) ?>">
    <h2 data-fr="Recherche par numéro" data-en="Find orders by phone">Recherche par numéro</h2>
    <div class="field">
      <label for="phone_number" data-fr="Numéro à recharger" data-en="Top-up phone number">Numéro à recharger</label>
      <input id="phone_number" name="phone_number" type="tel" inputmode="tel" required pattern="[+0-9 ]{9,16}" maxlength="16" autocomplete="tel" placeholder="6XX XXX XXX" value="<?= h($trackedPhone) ?>">
      <small data-fr="Aucune référence de commande n’est nécessaire." data-en="You do not need an order reference.">Aucune référence de commande n’est nécessaire.</small>
    </div>
    <?= csrf_field() ?>
    <button class="button" type="submit"><?= giga_svg('search') ?><span data-fr="Rechercher mes commandes" data-en="Find my orders">Rechercher mes commandes</span></button>
  </form>
  <aside class="summary-panel">
    <h2 data-fr="Comment ça marche ?" data-en="How it works">Comment ça marche ?</h2>
    <div class="summary-item"><span>01</span><strong data-fr="Saisissez le numéro qui doit recevoir le forfait." data-en="Enter the number that should receive the bundle.">Saisissez le numéro qui doit recevoir le forfait.</strong></div>
    <div class="summary-item"><span>02</span><strong data-fr="Consultez les statuts de toutes les commandes liées à ce numéro." data-en="See the status of every order linked to that number.">Consultez les statuts de toutes les commandes liées à ce numéro.</strong></div>
    <p class="safe-note"><?= giga_svg('shield') ?><span data-fr="Après confirmation du paiement, le vendeur recharge le numéro et met à jour le suivi." data-en="After payment is confirmed, the seller tops up the number and updates its status.">Après confirmation du paiement, le vendeur recharge le numéro et met à jour le suivi.</span></p>
  </aside>
</section>
<?php if ($orders): ?>
<section class="shell order-track-section" aria-live="polite">
  <div class="section-heading tracking-heading">
    <div><span class="eyebrow" data-fr="Suivi" data-en="Tracking">Suivi</span><h2 data-fr="<?= count($orders) ?> commande(s) trouvée(s)" data-en="<?= count($orders) ?> order(s) found"><?= count($orders) ?> commande(s) trouvée(s)</h2></div>
  </div>
  <?php foreach ($orders as $order):
    $status = $order['payment_status'] === 'SUCCESSFUL'
        ? (($order['delivery_status'] ?? '') === 'delivered' ? 'delivered' : 'paid')
        : (in_array($order['payment_status'], ['FAILED', 'EXPIRED'], true) ? 'failed' : 'pending');
    $displayStatus = $statusLabels[$status];
    $paymentLabels = match (strtoupper((string) $order['payment_status'])) {
        'SUCCESSFUL' => ['Paiement confirmé', 'Payment confirmed'],
        'FAILED' => ['Paiement échoué', 'Payment failed'],
        'EXPIRED' => ['Paiement expiré', 'Payment expired'],
        default => ['En attente', 'Pending'],
    };
    $deliveryLabels = ($order['delivery_status'] ?? '') === 'delivered'
        ? ['Livré', 'Delivered']
        : ['En attente de livraison', 'Awaiting delivery'];
  ?>
  <article class="order-result">
    <div class="order-result-head">
      <div><h2 data-fr="Commande" data-en="Order">Commande</h2><span class="order-ref"><?= h($order['public_code']) ?></span></div>
      <span class="status <?= h($displayStatus[2]) ?>"><span data-fr="<?= h($displayStatus[0]) ?>" data-en="<?= h($displayStatus[1]) ?>"><?= h($displayStatus[0]) ?></span></span>
    </div>
    <div class="summary-item"><span data-fr="Forfait" data-en="Bundle">Forfait</span><strong><?= h($order['operator'] . ' · ' . $order['package_name']) ?></strong></div>
    <div class="summary-item"><span data-fr="Numéro à recharger" data-en="Top-up number">Numéro à recharger</span><strong><?= h($order['recipient_phone']) ?></strong></div>
    <div class="summary-item"><span data-fr="Montant" data-en="Amount">Montant</span><strong><?= h(formatXaf($order['amount'])) ?></strong></div>
    <div class="summary-item"><span data-fr="Paiement" data-en="Payment">Paiement</span><strong><span data-fr="<?= h($paymentLabels[0]) ?>" data-en="<?= h($paymentLabels[1]) ?>"><?= h($paymentLabels[0]) ?></span></strong></div>
    <div class="summary-item"><span data-fr="Livraison" data-en="Delivery">Livraison</span><strong><span data-fr="<?= h($deliveryLabels[0]) ?>" data-en="<?= h($deliveryLabels[1]) ?>"><?= h($deliveryLabels[0]) ?></span></strong></div>
    <?php if ($order['payment_status'] === 'SUCCESSFUL' && $order['delivery_status'] !== 'delivered'): ?>
      <p class="safe-note"><?= giga_svg('clock') ?><span data-fr="Votre paiement est confirmé. Votre commande est en cours de traitement." data-en="Your payment is confirmed. Your order is being processed.">Votre paiement est confirmé. Votre commande est en cours de traitement.</span></p>
    <?php elseif (!in_array($order['payment_status'], ['SUCCESSFUL', 'FAILED', 'EXPIRED'], true) && !empty($order['fapshi_trans_id'])): ?>
      <form method="post" action="<?= h(url('verify-payment.php')) ?>" class="track-refresh">
        <?= csrf_field() ?><input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
        <button class="button ghost small" type="submit" data-fr="Actualiser le statut" data-en="Refresh status">Actualiser le statut</button>
      </form>
    <?php endif; ?>
    <p class="safe-note"><?= giga_svg('clock') ?><span><span data-fr="Commande créée le " data-en="Order placed on ">Commande créée le </span><?= h(formatDate($order['created_at'])) ?></span></p>
    <?php if ($order['delivery_status'] === 'delivered'):
      $reviewCheck = db()->prepare('SELECT COUNT(*) FROM reviews WHERE order_id = ?');
      $reviewCheck->execute([$order['id']]);
      $reviewAlreadySent = (int) $reviewCheck->fetchColumn() > 0;
    ?>
      <?php if (!$reviewAlreadySent): ?>
      <form method="post" action="<?= h(url('submit-review.php')) ?>" class="review-form">
        <h3 data-fr="Votre avis nous aide" data-en="Your feedback helps">Votre avis nous aide</h3>
        <label for="review-rating-<?= (int) $order['id'] ?>" data-fr="Votre note" data-en="Your rating">Votre note</label>
        <select id="review-rating-<?= (int) $order['id'] ?>" name="rating" required><option value="5">5 / 5</option><option value="4">4 / 5</option><option value="3">3 / 5</option><option value="2">2 / 5</option><option value="1">1 / 5</option></select>
        <label for="review-body-<?= (int) $order['id'] ?>" data-fr="Votre commentaire" data-en="Your comment">Votre commentaire</label>
        <textarea id="review-body-<?= (int) $order['id'] ?>" name="body" rows="3" maxlength="1000" minlength="5" required></textarea>
        <?= csrf_field() ?><input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
        <button class="button ghost small" type="submit" data-fr="Envoyer mon avis" data-en="Send feedback">Envoyer mon avis</button>
      </form>
      <?php endif; ?>
    <?php endif; ?>
  </article>
  <?php endforeach; ?>
</section>
<?php elseif ($trackedPhone !== ''): ?>
<section class="shell order-track-section" aria-live="polite">
  <div class="empty-state"><?= giga_svg('search') ?><h2 data-fr="Aucune commande trouvée" data-en="No orders found">Aucune commande trouvée</h2><p data-fr="Vérifiez le numéro à recharger et réessayez." data-en="Check the top-up number and try again.">Vérifiez le numéro à recharger et réessayez.</p></div>
</section>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
