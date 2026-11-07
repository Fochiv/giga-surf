<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdmin();
require_once __DIR__ . '/_layout.php';

$now = date('Y-m-d H:i:s');
$today = date('Y-m-d 00:00:00');
$week = date('Y-m-d 00:00:00', strtotime('monday this week'));
$month = date('Y-m-01 00:00:00');
$metric = static function (string $since): array {
    $stmt = db()->prepare("SELECT COALESCE(SUM(amount), 0) AS gross, COALESCE(SUM(COALESCE(fapshi_revenue, ROUND(amount * 0.97))), 0) AS net FROM orders WHERE payment_status = 'SUCCESSFUL' AND paid_at >= ?");
    $stmt->execute([$since]);
    return $stmt->fetch() ?: ['gross' => 0, 'net' => 0];
};
$totalOrders = (int) db()->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$failedOrders = (int) db()->query("SELECT COUNT(*) FROM orders WHERE payment_status IN ('FAILED', 'EXPIRED')")->fetchColumn();
$pendingOrders = (int) db()->query("SELECT COUNT(*) FROM orders WHERE payment_status IN ('CREATED', 'PENDING', 'UNKNOWN')")->fetchColumn();
$paid = $metric('2000-01-01 00:00:00');
$day = $metric($today);
$weekMetric = $metric($week);
$monthMetric = $metric($month);
$operatorRevenue = db()->query("SELECT operator, COUNT(*) AS count, SUM(amount) AS revenue FROM orders WHERE payment_status = 'SUCCESSFUL' GROUP BY operator ORDER BY revenue DESC")->fetchAll();
$popularPackages = db()->query("SELECT package_name, operator, COUNT(*) AS count, SUM(amount) AS revenue FROM orders WHERE payment_status = 'SUCCESSFUL' GROUP BY package_name, operator ORDER BY count DESC, revenue DESC LIMIT 5")->fetchAll();
$statusCounts = db()->query('SELECT payment_status, COUNT(*) AS count FROM orders GROUP BY payment_status ORDER BY count DESC')->fetchAll();
$recentOrders = db()->query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 8')->fetchAll();
adminHeader('Vue d’ensemble', 'dashboard');
?>
<section class="admin-stat-grid">
    <article class="admin-stat-card"><span>Revenus bruts</span><strong><?= h(formatXaf($paid['gross'])) ?></strong><small>Tous les paiements confirmés</small></article>
    <article class="admin-stat-card"><span>Revenu net estimé</span><strong><?= h(formatXaf($paid['net'])) ?></strong><small>Après frais Fapshi estimés</small></article>
    <article class="admin-stat-card"><span>Commandes totales</span><strong><?= h($totalOrders) ?></strong><small><?= h($pendingOrders) ?> paiement(s) en attente</small></article>
    <article class="admin-stat-card"><span>Commandes échouées</span><strong><?= h($failedOrders) ?></strong><small>Échecs et paiements expirés</small></article>
</section>
<section class="admin-period-grid">
    <article class="admin-panel"><span class="admin-eyebrow">AUJOURD’HUI</span><h2><?= h(formatXaf($day['gross'])) ?></h2><p>Net estimé : <?= h(formatXaf($day['net'])) ?></p></article>
    <article class="admin-panel"><span class="admin-eyebrow">CETTE SEMAINE</span><h2><?= h(formatXaf($weekMetric['gross'])) ?></h2><p>Net estimé : <?= h(formatXaf($weekMetric['net'])) ?></p></article>
    <article class="admin-panel"><span class="admin-eyebrow">CE MOIS</span><h2><?= h(formatXaf($monthMetric['gross'])) ?></h2><p>Net estimé : <?= h(formatXaf($monthMetric['net'])) ?></p></article>
</section>
<section class="admin-two-col">
    <article class="admin-panel">
        <div class="admin-panel-heading"><div><span class="admin-eyebrow">VENTES CONFIRMÉES</span><h2>Revenu par opérateur</h2></div></div>
        <?php if (!$operatorRevenue): ?><p class="admin-empty">Les revenus s’afficheront après les premiers paiements confirmés.</p>
        <?php else: ?><div class="admin-simple-list"><?php foreach ($operatorRevenue as $row): ?><div><span><?= h($row['operator']) ?><small><?= (int) $row['count'] ?> commande(s)</small></span><strong><?= h(formatXaf($row['revenue'])) ?></strong></div><?php endforeach; ?></div><?php endif; ?>
    </article>
    <article class="admin-panel">
        <div class="admin-panel-heading"><div><span class="admin-eyebrow">POPULARITÉ</span><h2>Forfaits les plus vendus</h2></div></div>
        <?php if (!$popularPackages): ?><p class="admin-empty">Aucune vente confirmée pour le moment.</p>
        <?php else: ?><div class="admin-simple-list"><?php foreach ($popularPackages as $row): ?><div><span><?= h($row['operator'] . ' · ' . $row['package_name']) ?><small><?= (int) $row['count'] ?> vente(s)</small></span><strong><?= h(formatXaf($row['revenue'])) ?></strong></div><?php endforeach; ?></div><?php endif; ?>
    </article>
</section>
<section class="admin-two-col">
    <article class="admin-panel">
        <div class="admin-panel-heading"><div><span class="admin-eyebrow">SUIVI</span><h2>Statut des commandes</h2></div></div>
        <?php if (!$statusCounts): ?><p class="admin-empty">Aucune commande enregistrée.</p>
        <?php else: ?><div class="admin-simple-list"><?php foreach ($statusCounts as $row): ?><div><span><?= h(paymentStatusLabel($row['payment_status'])) ?></span><strong><?= (int) $row['count'] ?></strong></div><?php endforeach; ?></div><?php endif; ?>
    </article>
    <article class="admin-panel admin-quick-links">
        <div class="admin-panel-heading"><div><span class="admin-eyebrow">RACCOURCIS</span><h2>À faire ensuite</h2></div></div>
        <a href="commandes.php?filter=paid">Traiter les commandes payées <?= adminIcon('arrow') ?></a>
        <a href="forfaits.php">Mettre à jour les forfaits <?= adminIcon('arrow') ?></a>
    </article>
</section>
<section class="admin-panel">
    <div class="admin-panel-heading"><div><span class="admin-eyebrow">ACTIVITÉ RÉCENTE</span><h2>Historique des commandes</h2></div><a class="admin-text-link" href="commandes.php">Voir toutes</a></div>
    <?php if (!$recentOrders): ?><p class="admin-empty">Les commandes apparaîtront ici.</p>
    <?php else: ?>
    <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Référence</th><th>Client</th><th>Opérateur</th><th>Montant</th><th>Paiement</th><th>Date</th></tr></thead><tbody>
    <?php foreach ($recentOrders as $order): ?><tr><td><strong><?= h($order['public_code']) ?></strong></td><td><?= h($order['customer_name']) ?><small><?= h($order['recipient_phone']) ?></small></td><td><?= h($order['operator']) ?></td><td><?= h(formatXaf($order['amount'])) ?></td><td><span class="admin-status <?= strtolower(h($order['payment_status'])) ?>"><?= h(paymentStatusLabel($order['payment_status'])) ?></span></td><td><?= h(formatDate($order['created_at'])) ?></td></tr><?php endforeach; ?>
    </tbody></table></div>
    <?php endif; ?>
</section>
<?php adminFooter(); ?>
