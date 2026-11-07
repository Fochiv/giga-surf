<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Finaliser ma commande';
$currentPage = '';
$allPackages = db()->query('SELECT * FROM packages WHERE active = 1 ORDER BY operator, sort_order, price')->fetchAll();
$selectedId = (string)($_GET['plan'] ?? $_POST['package_id'] ?? '');
$selected = null;
foreach ($allPackages as $candidate) {
    if ((string)($candidate['id'] ?? '') === $selectedId) { $selected = $candidate; break; }
}
$selectedPrice = (float)($selected['price'] ?? 0);
$selectedName = $selected ? (string)($selected['name'] ?? $selected['title'] ?? $selected['data'] ?? 'Forfait') : '';
$selectedOperator = $selected ? (string)($selected['operator'] ?? '') : '';
global $fapshiConfig;
$paymentConfigured = fapshi()->isConfigured();
$sandboxPayments = str_contains(lowerText((string) ($fapshiConfig['base_url'] ?? '')), 'sandbox.fapshi.com');
if (empty($_SESSION['checkout_token'])) {
    $_SESSION['checkout_token'] = bin2hex(random_bytes(32));
}
$checkoutToken = (string) $_SESSION['checkout_token'];
require __DIR__ . '/includes/header.php';
?>
<section class="shell catalog-head">
  <a class="back-link" href="<?= h(url('forfaits.php' . (!empty($_GET['operator']) ? '?operator=' . rawurlencode((string)$_GET['operator']) : ''))) ?>"><?= giga_svg('back') ?><span data-fr="Retour aux forfaits" data-en="Back to bundles">Retour aux forfaits</span></a>
  <div class="operator-title"><div><span class="eyebrow" data-fr="Presque connecté" data-en="Almost connected">Presque connecté</span><h1 data-fr="Finalisez votre commande" data-en="Complete your order">Finalisez votre commande</h1><p data-fr="Vérifiez les informations avant de continuer." data-en="Check your details before continuing.">Vérifiez les informations avant de continuer.</p></div></div>
</section>
<section class="shell form-layout">
  <form class="form-panel" method="post" action="<?= h(url('create-order.php')) ?>">
    <input type="hidden" name="checkout_token" value="<?= h($checkoutToken) ?>">
    <h2 data-fr="Vos informations" data-en="Your details">Vos informations</h2>
    <?php if (!empty($errors) && is_array($errors)): ?>
      <div class="form-error" role="alert"><strong data-fr="Vérifiez les champs ci-dessous." data-en="Please check the fields below.">Vérifiez les champs ci-dessous.</strong><ul><?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?><div class="form-error" role="alert"><?= h($error) ?></div><?php endif; ?>
    <div class="field">
      <label for="package_id" data-fr="Forfait choisi" data-en="Selected bundle">Forfait choisi</label>
      <select id="package_id" name="package_id" required data-plan-select>
        <option value="" data-price-label="—" <?= !$selected ? 'selected' : '' ?> data-fr="Choisir un forfait" data-en="Select a bundle">Choisir un forfait</option>
        <?php foreach ($allPackages as $plan):
          $id = (string)($plan['id'] ?? $plan['package_id'] ?? '');
          $name = (string)($plan['name'] ?? $plan['title'] ?? $plan['data'] ?? 'Forfait');
          $operatorName = (string)($plan['operator'] ?? '');
          $price = (float)($plan['price'] ?? 0);
          $label = number_format($price, 0, ',', ' ') . ' FCFA';
        ?>
        <option value="<?= h($id) ?>" data-price-label="<?= h($label) ?>" <?= $selectedId === $id ? 'selected' : '' ?>><?= h(trim($operatorName . ' · ' . $name)) ?> — <?= h($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field-grid">
      <div class="field"><label for="customer_name" data-fr="Votre nom" data-en="Your name">Votre nom</label><input id="customer_name" name="customer_name" autocomplete="name" required maxlength="100" value="<?= h($_POST['customer_name'] ?? '') ?>" placeholder="Ex. Amina M." data-fr-placeholder="Ex. Amina M." data-en-placeholder="e.g. Amina M."></div>
      <div class="field"><label for="recipient_number" data-fr="Numéro à recharger" data-en="Number to top up">Numéro à recharger</label><input id="recipient_number" name="recipient_phone" type="tel" inputmode="tel" autocomplete="tel" required pattern="[+0-9 ]{9,16}" maxlength="16" value="<?= h($_POST['recipient_phone'] ?? '') ?>" placeholder="6XX XXX XXX"><small data-fr="Le numéro qui recevra le forfait." data-en="The phone number that will receive the bundle.">Le numéro qui recevra le forfait.</small></div>
    </div>
    <div class="field">
      <label data-fr="Opérateur de paiement" data-en="Payment operator">Opérateur de paiement</label>
      <div class="radio-row">
        <label class="radio-card"><input type="radio" name="payment_operator" value="MTN Mobile Money" required <?= ($_POST['payment_operator'] ?? '') === 'MTN Mobile Money' ? 'checked' : '' ?>><img src="<?= h(url('logo-mtn.png')) ?>" alt=""><span>MTN MoMo</span></label>
        <label class="radio-card"><input type="radio" name="payment_operator" value="Orange Money" required <?= ($_POST['payment_operator'] ?? '') === 'Orange Money' ? 'checked' : '' ?>><img src="<?= h(url('logo-orange.png')) ?>" alt=""><span>Orange Money</span></label>
      </div>
      <small data-fr="Le numéro de paiement doit correspondre à l’opérateur choisi. Il peut être différent de l’opérateur du forfait." data-en="The payment number must match the selected network. It can be different from the bundle operator.">Le numéro de paiement doit correspondre à l’opérateur choisi. Il peut être différent de l’opérateur du forfait.</small>
    </div>
    <div class="field"><label for="payment_number" data-fr="Numéro utilisé pour le paiement" data-en="Payment phone number">Numéro utilisé pour le paiement</label><input id="payment_number" name="payer_phone" type="tel" inputmode="tel" autocomplete="tel" required pattern="[+0-9 ]{9,16}" maxlength="16" value="<?= h($_POST['payer_phone'] ?? '') ?>" placeholder="6XX XXX XXX"><small data-fr="Une demande de paiement sera envoyée sur ce numéro. Gardez le téléphone à portée de main." data-en="A payment request will be sent to this number. Keep your phone nearby.">Une demande de paiement sera envoyée sur ce numéro. Gardez le téléphone à portée de main.</small></div>
    <?= csrf_field() ?>
    <?php if (!$paymentConfigured): ?><p class="payment-unavailable" role="status" data-fr="Le paiement est momentanément indisponible. Réessayez plus tard." data-en="Payments are temporarily unavailable. Please try again later.">Le paiement est momentanément indisponible. Réessayez plus tard.</p><?php endif; ?>
    <button class="button" type="submit" <?= $paymentConfigured ? '' : 'disabled' ?>><span data-fr="Continuer vers le paiement" data-en="Continue to payment">Continuer vers le paiement</span><?= giga_svg('arrow') ?></button>
  </form>
  <aside class="summary-panel">
    <h2 data-fr="Récapitulatif" data-en="Order summary">Récapitulatif</h2>
    <div class="summary-item"><span data-fr="Forfait" data-en="Bundle">Forfait</span><strong data-summary-plan><?= h($selectedName ?: '—') ?></strong></div>
    <div class="summary-item"><span data-fr="Opérateur" data-en="Network">Opérateur</span><strong><?= h($selectedOperator ?: '—') ?></strong></div>
    <div class="summary-item"><span data-fr="Total à payer" data-en="Total due">Total à payer</span><strong class="summary-total" data-summary-price><?= $selected ? number_format($selectedPrice, 0, ',', ' ') . ' FCFA' : '—' ?></strong></div>
    <p class="safe-note"><?= giga_svg('lock') ?><span data-fr="Vos informations servent uniquement au traitement et au suivi de cette commande." data-en="Your details are used only to process and track this order.">Vos informations servent uniquement au traitement et au suivi de cette commande.</span></p>
  </aside>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
