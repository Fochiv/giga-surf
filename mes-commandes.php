<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Suivre mes commandes';
$currentPage = 'orders';
$order = null;
$orderId = filter_var($_SESSION['tracked_order_id'] ?? null, FILTER_VALIDATE_INT);
if ($orderId) {
    $stmt = db()->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch() ?: null;
}
$status = null;
if ($order) {
    $status = $order['payment_status'] === 'SUCCESSFUL'
        ? (($order['delivery_status'] ?? '') === 'delivered' ? 'delivered' : 'paid')
        : (in_array($order['payment_status'], ['FAILED', 'EXPIRED'], true) ? 'failed' : 'pending');
}
$statusLabels = [
    'pending' => ['En attente de paiement', 'Awaiting payment', 'pending'],
    'paid' => ['Paiement confirmé · livraison en cours', 'Payment confirmed · delivery in progress', 'paid'],
    'delivered' => ['Forfait livré', 'Bundle delivered', 'delivered'],
    'failed' => ['Échec du paiement', 'Payment failed', 'failed'],
];
$displayStatus = $statusLabels[$status ?? 'pending'];
$pageFlash = null;
require __DIR__ . '/includes/header.php';
?>
<section class="shell catalog-head">
  <a class="back-link" href="<?= h(url('index.php')) ?>"><?= giga_svg('back') ?><span data-fr="Retour à l’accueil" data-en="Back home">Retour à l’accueil</span></a>
  <div class="operator-title"><div><span class="eyebrow" data-fr="Toujours au courant" data-en="Stay in the loop">Toujours au courant</span><h1 data-fr="Suivre une commande" data-en="Track an order">Suivre une commande</h1><p data-fr="Retrouvez votre commande avec sa référence et le numéro utilisé." data-en="Find your order with its reference and the phone number used.">Retrouvez votre commande avec sa référence et le numéro utilisé.</p></div></div>
</section>
<section class="shell form-layout">
  <form class="form-panel" method="post" action="<?= h(url('track-order.php')) ?>">
    <h2 data-fr="Vos informations de suivi" data-en="Your tracking details">Vos informations de suivi</h2>
    <div class="field">
      <label for="order_reference" data-fr="Référence de commande" data-en="Order reference">Référence de commande</label>
      <input id="order_reference" name="order_reference" required autocomplete="off" placeholder="Ex. GS-A1B2C3D4E5" data-fr-placeholder="Ex. GS-A1B2C3D4E5" data-en-placeholder="e.g. GS-A1B2C3D4E5">
      <small data-fr="La référence est affichée après la création de la commande." data-en="Your reference is shown after the order is created.">La référence est affichée après la création de la commande.</small>
    </div>
    <div class="field">
      <label for="phone_number" data-fr="Numéro à recharger" data-en="Top-up phone number">Numéro à recharger</label>
      <input id="phone_number" name="phone_number" type="tel" inputmode="tel" required pattern="[+0-9 ]{9,16}" maxlength="16" autocomplete="tel" placeholder="6XX XXX XXX">
    </div>
    <?= csrf_field() ?>
    <button class="button" type="submit"><?= giga_svg('search') ?><span data-fr="Rechercher ma commande" data-en="Find my order">Rechercher ma commande</span></button>
  </form>
  <aside class="summary-panel">
    <h2 data-fr="Comment ça marche ?" data-en="How it works">Comment ça marche ?</h2>
    <div class="summary-item"><span>01</span><strong data-fr="Saisissez la référence reçue après l’achat." data-en="Enter the reference you received after purchase.">Saisissez la référence reçue après l’achat.</strong></div>
    <div class="summary-item"><span>02</span><strong data-fr="Confirmez le numéro qui recevra le forfait." data-en="Confirm the number that will receive the bundle.">Confirmez le numéro qui recevra le forfait.</strong></div>
    <p class="safe-note"><?= giga_svg('shield') ?><span data-fr="Après confirmation du paiement, le vendeur recharge le numéro et marque la commande comme livrée." data-en="After payment is confirmed, the seller tops up the number and marks the order delivered.">Après confirmation du paiement, le vendeur recharge le numéro et marque la commande comme livrée.</span></p>
  </aside>
</section>
<?php if ($order): ?>
<section class="shell order-track-section" aria-live="polite">
  <article class="order-result">
    <div class="order-result-head">
      <div><h2 data-fr="Commande trouvée" data-en="Order found">Commande trouvée</h2><span class="order-ref"><?= h($order['public_code']) ?></span></div>
      <span class="status <?= h($displayStatus[2]) ?>"><?= h($displayStatus[0]) ?></span>
    </div>
    <div class="summary-item"><span data-fr="Forfait" data-en="Bundle">Forfait</span><strong><?= h($order['operator'] . ' · ' . $order['package_name']) ?></strong></div>
    <div class="summary-item"><span data-fr="Numéro rechargé" data-en="Top-up number">Numéro rechargé</span><strong><?= h($order['recipient_phone']) ?></strong></div>
    <div class="summary-item"><span data-fr="Montant" data-en="Amount">Montant</span><strong><?= h(formatXaf($order['amount'])) ?></strong></div>
    <div class="summary-item"><span data-fr="Paiement" data-en="Payment">Paiement</span><strong><?= h(paymentStatusLabel($order['payment_status'])) ?></strong></div>
    <div class="summary-item"><span data-fr="Livraison" data-en="Delivery">Livraison</span><strong><?= h($order['delivery_status'] === 'delivered' ? t('delivered') : t('not_delivered')) ?></strong></div>
    <?php if ($order['payment_status'] === 'SUCCESSFUL' && $order['delivery_status'] !== 'delivered'): ?>
      <p class="safe-note"><?= giga_svg('clock') ?><span data-fr="Votre paiement est confirmé. Le vendeur va effectuer le rechargement et mettre à jour ce suivi." data-en="Your payment is confirmed. The seller will top up the number and update this status.">Votre paiement est confirmé. Le vendeur va effectuer le rechargement et mettre à jour ce suivi.</span></p>
    <?php elseif (!in_array($order['payment_status'], ['SUCCESSFUL', 'FAILED', 'EXPIRED'], true)): ?>
      <form method="post" action="<?= h(url('verify-payment.php')) ?>" class="track-refresh">
        <?= csrf_field() ?><input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
        <button class="button ghost small" type="submit" data-fr="Actualiser le paiement" data-en="Refresh payment status">Actualiser le paiement</button>
      </form>
    <?php endif; ?>
    <p class="safe-note"><?= giga_svg('clock') ?><span><?= h('Commande créée le ' . formatDate($order['created_at'])) ?></span></p>
    <?php if ($order['delivery_status'] === 'delivered'): ?>
      <?php
        $reviewCheck = db()->prepare('SELECT COUNT(*) FROM reviews WHERE order_id = ?');
        $reviewCheck->execute([$order['id']]);
        $reviewAlreadySent = (int) $reviewCheck->fetchColumn() > 0;
      ?>
      <?php if (!$reviewAlreadySent): ?>
      <form method="post" action="<?= h(url('submit-review.php')) ?>" class="review-form">
        <h3 data-fr="Votre avis nous aide" data-en="Your feedback helps">Votre avis nous aide</h3>
        <label for="review-rating" data-fr="Votre note" data-en="Your rating">Votre note</label>
        <select id="review-rating" name="rating" required><option value="5">5 / 5</option><option value="4">4 / 5</option><option value="3">3 / 5</option><option value="2">2 / 5</option><option value="1">1 / 5</option></select>
        <label for="review-body" data-fr="Votre commentaire" data-en="Your comment">Votre commentaire</label>
        <textarea id="review-body" name="body" rows="3" maxlength="1000" minlength="5" required></textarea>
        <?= csrf_field() ?><input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
        <button class="button ghost small" type="submit" data-fr="Envoyer mon avis" data-en="Send feedback">Envoyer mon avis</button>
      </form>
      <?php endif; ?>
    <?php endif; ?>
  </article>
</section>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
