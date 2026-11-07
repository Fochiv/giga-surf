<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Forfaits mobiles';
$currentPage = 'plans';
$operatorNames = ['orange' => 'Orange', 'mtn' => 'MTN', 'camtel' => 'Camtel Blue', 'blue' => 'Camtel Blue'];
$logos = ['orange' => 'logo-orange.png', 'mtn' => 'logo-mtn.png', 'camtel' => 'logo-camtel.jpg', 'blue' => 'logo-camtel.jpg'];
$selectedOperator = strtolower((string)($_GET['operator'] ?? ''));
$operatorLabel = $operatorNames[$selectedOperator] ?? 'Tous les opérateurs';
$allPackages = db()->query('SELECT * FROM packages WHERE active = 1 ORDER BY operator, sort_order, price')->fetchAll();
$allPackages = array_map(static function (array $plan): array {
    $plan['operator'] = strtolower((string) $plan['operator']);
    $plan['data'] = $plan['data_label'];
    $plan['validity'] = (int) $plan['validity_days'] . ' jours';
    $plan['category'] = (int) $plan['validity_days'] <= 7 ? 'weekly' : ((int) $plan['validity_days'] <= 15 ? 'fortnight' : 'monthly');
    return $plan;
}, $allPackages);
$visiblePackages = array_values(array_filter($allPackages, function ($plan) use ($selectedOperator, $operatorNames) {
    $op = strtolower(trim((string)($plan['operator'] ?? '')));
    if (in_array($op, ['camtel blue', 'blue camtel', 'camtel-blue'], true)) $op = 'camtel';
    return !$selectedOperator || $op === $selectedOperator || ($selectedOperator === 'blue' && $op === 'camtel');
}));
require __DIR__ . '/includes/header.php';
?>
<section class="shell catalog-head">
  <a class="back-link" href="<?= h(url('index.php')) ?>"><?= giga_svg('back') ?><span data-fr="Retour à l’accueil" data-en="Back home">Retour à l’accueil</span></a>
  <div class="operator-title">
    <?php if (isset($logos[$selectedOperator])): ?><img src="<?= h(url($logos[$selectedOperator])) ?>" alt="<?= h($operatorLabel) ?>"><?php else: ?><span class="operator-logo"><?= giga_svg('wifi') ?></span><?php endif; ?>
    <div><h1><?= h($operatorLabel === 'Tous les opérateurs' ? 'Forfaits mobiles' : 'Forfaits ' . $operatorLabel) ?></h1><p data-fr="Choisissez l’offre qui vous convient." data-en="Choose the plan that works for you.">Choisissez l’offre qui vous convient.</p></div>
  </div>
</section>
<section class="shell">
  <div class="filter-bar" role="group" aria-label="Filtrer par durée">
    <button type="button" class="filter-chip is-active" data-filter="all" aria-pressed="true" data-fr="Tous" data-en="All">Tous</button>
    <button type="button" class="filter-chip" data-filter="weekly" aria-pressed="false" data-fr="Hebdomadaires" data-en="Weekly">Hebdomadaires</button>
    <button type="button" class="filter-chip" data-filter="fortnight" aria-pressed="false" data-fr="15 jours" data-en="15 days">15 jours</button>
    <button type="button" class="filter-chip" data-filter="monthly" aria-pressed="false" data-fr="Mensuels" data-en="Monthly">Mensuels</button>
  </div>
  <?php if (!$visiblePackages): ?>
    <div class="empty-state"><?= giga_svg('search') ?><h2 data-fr="Aucun forfait trouvé" data-en="No bundles found">Aucun forfait trouvé</h2><p data-fr="Les forfaits de cet opérateur ne sont pas disponibles pour le moment." data-en="Bundles for this network are not available right now.">Les forfaits de cet opérateur ne sont pas disponibles pour le moment.</p><a class="button ghost small" href="<?= h(url('forfaits.php')) ?>" data-fr="Voir tous les forfaits" data-en="See all bundles">Voir tous les forfaits</a></div>
  <?php else: ?>
  <div class="package-grid">
    <?php foreach ($visiblePackages as $index => $plan):
      $op = strtolower((string)($plan['operator'] ?? 'orange'));
      $op = $op === 'blue' ? 'camtel' : $op;
      $name = (string)($plan['name'] ?? $plan['title'] ?? $plan['data'] ?? 'Forfait');
      $dataValue = (string)($plan['data'] ?? $plan['volume'] ?? $name);
      $validity = (string)($plan['validity'] ?? $plan['duration'] ?? '');
      $price = (float)($plan['price'] ?? 0);
      $category = (string)($plan['category'] ?? ((stripos($validity, '7') !== false) ? 'weekly' : ((stripos($validity, '15') !== false) ? 'fortnight' : 'monthly')));
      $id = (string)($plan['id'] ?? $plan['package_id'] ?? '');
      $description = (string)($plan['description'] ?? '');
    ?>
      <article class="package-card fade-in" data-category="<?= h($category) ?>" style="--operator-color:<?= $op === 'orange' ? '#f47a16' : ($op === 'mtn' ? '#e1bd18' : '#173f7a') ?>; animation-delay:<?= min($index * .04, .28) ?>s">
      <div class="package-meta"><div><h2 class="package-size"><?= h($dataValue) ?></h2><?php if ($description !== ''): ?><p class="package-desc"><?= h($description) ?></p><?php endif; ?></div></div>
      <p class="validity"><?= giga_svg('clock') ?><span data-fr="Valable <?= h($validity) ?>" data-en="Valid for <?= h($validity) ?>">Valable <?= h($validity) ?></span></p>
      <div class="package-bottom"><span class="price"><?= number_format($price, 0, ',', ' ') ?> FCFA</span>
        <a class="button small" href="<?= h(url('commande.php?plan=' . rawurlencode($id) . '&operator=' . rawurlencode($op))) ?>"><span data-fr="Acheter" data-en="Buy">Acheter</span><?= giga_svg('arrow') ?></a>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <div class="empty-state filter-empty" data-filter-empty role="status" hidden>
    <?= giga_svg('clock') ?>
    <h2 data-empty-title data-fr="Aucun forfait hebdomadaire disponible pour le moment." data-en="No weekly bundles are available right now.">Aucun forfait hebdomadaire disponible pour le moment.</h2>
    <p data-empty-copy data-fr="Veuillez réessayer plus tard." data-en="Please try again later.">Veuillez réessayer plus tard.</p>
  </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
