<?php
require_once __DIR__ . '/includes/bootstrap.php';
global $isDevelopment;
$salesPaused = false;
$notice = '';
$supportWhatsapp = '237678519844';
$publicReviews = [];
try {
    $settingStmt = db()->prepare('SELECT setting_key, setting_value FROM settings WHERE setting_key IN (?, ?, ?, ?)');
    $settingStmt->execute(['sales_paused', 'notice_fr', 'notice_en', 'support_whatsapp']);
    foreach ($settingStmt->fetchAll() as $setting) {
        if ($setting['setting_key'] === 'sales_paused') $salesPaused = $setting['setting_value'] === '1';
        if ($setting['setting_key'] === (currentLanguage() === 'en' ? 'notice_en' : 'notice_fr')) $notice = (string) $setting['setting_value'];
        if ($setting['setting_key'] === 'support_whatsapp') {
            $digits = preg_replace('/\D+/', '', (string) $setting['setting_value']) ?? '';
            if (str_starts_with($digits, '237')) $digits = substr($digits, 3);
            if (validCameroonPhone($digits)) $supportWhatsapp = '237' . $digits;
        }
    }
    $publicReviews = db()->query('SELECT customer_name, rating, body, created_at FROM reviews WHERE visible = 1 ORDER BY created_at DESC LIMIT 3')->fetchAll();
} catch (Throwable $e) {
    error_log('Giga Surf storefront settings load failed: ' . $e->getMessage());
}
$pageTitle = 'Internet mobile, simplement';
$currentPage = 'home';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="shell fade-in">
    <span class="eyebrow" data-fr="Internet mobile, simplement" data-en="Mobile internet, made simple">Internet mobile, simplement</span>
    <h1 data-fr="Votre prochain <span class='accent'>giga</span> est tout près." data-en="Your next <span class='accent'>gigabyte</span> is just a tap away." data-i18n-html>Votre prochain <span class="accent">giga</span> est tout près.</h1>
    <p class="hero-copy" data-fr="Choisissez un forfait, indiquez le numéro à recharger et payez en toute simplicité. Pas de compte à créer." data-en="Choose a bundle, enter the number to top up and pay simply. No account needed.">Choisissez un forfait, indiquez le numéro à recharger et payez en toute simplicité. Pas de compte à créer.</p>
    <a class="button" href="#operateurs"><span data-fr="Trouver mon forfait" data-en="Find my bundle">Trouver mon forfait</span><?= giga_svg('arrow') ?></a>
  </div>
</section>
<section class="section" id="operateurs">
  <div class="shell">
    <?php if ($salesPaused): ?><div class="notice" role="status"><strong data-fr="Les commandes sont momentanément suspendues." data-en="Orders are temporarily paused.">Les commandes sont momentanément suspendues.</strong></div><?php endif; ?>
    <?php if ($notice !== ''): ?><div class="notice" role="status"><?= h($notice) ?></div><?php endif; ?>
    <div class="section-heading fade-in">
      <div><span class="eyebrow" data-fr="Choisissez votre réseau" data-en="Choose your network">Choisissez votre réseau</span><h2 data-fr="Quel est votre opérateur ?" data-en="Which network do you use?">Quel est votre opérateur ?</h2><p data-fr="Des forfaits pour rester en contact, où que vous soyez." data-en="Bundles to keep you connected, wherever you are.">Des forfaits pour rester en contact, où que vous soyez.</p></div>
    </div>
    <div class="operator-grid">
      <a href="<?= h(url('forfaits.php?operator=orange')) ?>" class="operator-card orange fade-in">
        <div class="operator-top"><img class="operator-logo" src="<?= h(url('logo-orange.png')) ?>" alt="Orange"><span class="operator-arrow"><?= giga_svg('arrow') ?></span></div>
        <div><h3>Orange</h3><p data-fr="Explorer les forfaits" data-en="Explore bundles">Explorer les forfaits</p></div>
      </a>
      <a href="<?= h(url('forfaits.php?operator=mtn')) ?>" class="operator-card mtn fade-in delay-1">
        <div class="operator-top"><img class="operator-logo" src="<?= h(url('logo-mtn.png')) ?>" alt="MTN"><span class="operator-arrow"><?= giga_svg('arrow') ?></span></div>
        <div><h3>MTN</h3><p data-fr="Explorer les forfaits" data-en="Explore bundles">Explorer les forfaits</p></div>
      </a>
      <a href="<?= h(url('forfaits.php?operator=camtel')) ?>" class="operator-card camtel fade-in delay-2">
        <div class="operator-top"><img class="operator-logo" src="<?= h(url('logo-camtel.jpg')) ?>" alt="Camtel Blue"><span class="operator-arrow"><?= giga_svg('arrow') ?></span></div>
        <div><h3>Camtel Blue</h3><p data-fr="Explorer les forfaits" data-en="Explore bundles">Explorer les forfaits</p></div>
      </a>
    </div>
    <a class="button ghost small" href="<?= h(url('forfaits.php')) ?>" data-fr="Voir tous les forfaits" data-en="Browse all bundles">Voir tous les forfaits</a>
    <div class="trust-row">
      <div class="trust-item"><?= giga_svg('shield') ?><span data-fr="Paiement sécurisé" data-en="Secure payment">Paiement sécurisé</span></div>
      <div class="trust-item"><?= giga_svg('bolt') ?><span data-fr="Livraison rapide" data-en="Fast delivery">Livraison rapide</span></div>
      <div class="trust-item"><?= giga_svg('user') ?><span data-fr="Sans compte client" data-en="No account needed">Sans compte client</span></div>
    </div>
  </div>
</section>
<section class="how-section">
  <div class="shell">
    <div class="section-heading"><div><h2 data-fr="Comment ça marche" data-en="How it works">Comment ça marche</h2></div></div>
    <div class="steps">
      <article class="step"><span class="step-number">1</span><div><h3 data-fr="Choisissez votre forfait" data-en="Choose your bundle">Choisissez votre forfait</h3><p data-fr="Comparez les offres Orange, MTN et Camtel Blue." data-en="Compare offers from Orange, MTN and Camtel Blue.">Comparez les offres Orange, MTN et Camtel Blue.</p></div></article>
      <article class="step"><span class="step-number">2</span><div><h3 data-fr="Indiquez le numéro" data-en="Enter the phone number">Indiquez le numéro</h3><p data-fr="Vérifiez soigneusement le numéro à recharger." data-en="Double-check the number you want to top up.">Vérifiez soigneusement le numéro à recharger.</p></div></article>
      <article class="step reveal-target" tabindex="0" role="button" aria-label="Étape 3" data-admin-reveal><span class="step-number">3</span><div><h3 data-fr="Payez, nous livrons" data-en="Pay, then we deliver">Payez, nous livrons</h3><p data-fr="Suivez votre commande avec sa référence unique." data-en="Track your order using its unique reference.">Suivez votre commande avec sa référence unique.</p></div></article>
    </div>
    <div class="admin-reveal" data-admin-link hidden><a href="<?= h(url('Admin/login.php')) ?>" data-fr="Accès administrateur" data-en="Administrator access">Accès administrateur</a></div>
  </div>
</section>
<?php if ($publicReviews): ?>
<section class="section review-section">
  <div class="shell">
    <div class="section-heading"><div><span class="eyebrow" data-fr="Ils nous font confiance" data-en="Customer feedback">Ils nous font confiance</span><h2 data-fr="Les avis de nos clients" data-en="What customers say">Les avis de nos clients</h2></div></div>
    <div class="review-grid">
      <?php foreach ($publicReviews as $review): ?>
      <article class="review-card"><div class="review-stars" aria-label="<?= (int) $review['rating'] ?> sur 5"><?= str_repeat('★', (int) $review['rating']) ?><span><?= str_repeat('☆', 5 - (int) $review['rating']) ?></span></div><p><?= nl2br(h($review['body'])) ?></p><strong><?= h($review['customer_name']) ?></strong><small><?= h(date('d/m/Y', strtotime($review['created_at']))) ?></small></article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<a class="support-float" href="https://wa.me/<?= h($supportWhatsapp) ?>?text=Bonjour%20Giga%20Surf%2C%20j%E2%80%99ai%20besoin%20d%E2%80%99aide." target="_blank" rel="noopener noreferrer" aria-label="Contacter l’assistance Giga Surf sur WhatsApp" title="WhatsApp">
  <?= giga_svg('headset') ?>
</a>
<?php require __DIR__ . '/includes/footer.php'; ?>
