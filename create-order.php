<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('forfaits.php');
}
verifyCsrf();
$submittedCheckoutToken = (string) ($_POST['checkout_token'] ?? '');
if ($submittedCheckoutToken === '' || empty($_SESSION['checkout_token']) || !hash_equals((string) $_SESSION['checkout_token'], $submittedCheckoutToken)) {
    flash('warning', 'Cette demande de paiement a déjà été envoyée ou a expiré. Vérifiez vos commandes avant de réessayer.');
    redirect('mes-commandes.php');
}

global $fapshiConfig;
$packageId = filter_input(INPUT_POST, 'package_id', FILTER_VALIDATE_INT);
$name = trim((string) ($_POST['customer_name'] ?? ''));
$recipientPhone = normalizePhone((string) ($_POST['recipient_phone'] ?? ''));
$payerPhone = normalizePhone((string) ($_POST['payer_phone'] ?? ''));
$paymentOperator = (string) ($_POST['payment_operator'] ?? '');
$package = $packageId ? fetchPackage((int) $packageId) : null;

if (!$package) {
    flash('error', 'Ce forfait n’est plus disponible. Veuillez en choisir un autre.');
    redirect('forfaits.php');
}
$salesPausedStmt = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
$salesPausedStmt->execute(['sales_paused']);
if ($salesPausedStmt->fetchColumn() === '1') {
    flash('warning', 'Les commandes sont momentanément suspendues. Réessayez plus tard.');
    redirect('forfaits.php');
}
if (textLength($name) < 2 || textLength($name) > 100) {
    flash('error', 'Veuillez saisir un nom valide.');
    redirect('commande.php?forfait=' . (int) $package['id']);
}
if (!validCameroonPhone($recipientPhone) || !validCameroonPhone($payerPhone)) {
    flash('error', 'Utilisez un numéro camerounais valide au format 6XXXXXXXX.');
    redirect('commande.php?forfait=' . (int) $package['id']);
}
if (!in_array($paymentOperator, ['Orange Money', 'MTN Mobile Money'], true)) {
    flash('error', 'Choisissez Orange Money ou MTN Mobile Money.');
    redirect('commande.php?forfait=' . (int) $package['id']);
}
if (!fapshi()->isConfigured()) {
    flash('error', 'Le paiement est momentanément indisponible. Réessayez plus tard ou contactez le vendeur.');
    redirect('commande.php?forfait=' . (int) $package['id']);
}

unset($_SESSION['checkout_token']);
$publicCode = 'GS-' . strtoupper(bin2hex(random_bytes(5)));
$now = date('Y-m-d H:i:s');
$insert = db()->prepare(
    "INSERT INTO orders (
        public_code, package_id, package_name, operator, amount, data_label, validity_days,
        customer_name, recipient_phone, payer_phone, payment_operator, payment_status, delivery_status,
        fapshi_external_id, fapshi_user_id, created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'CREATED', 'not_delivered', '', '', ?, ?)"
);
$insert->execute([
    $publicCode,
    $package['id'],
    $package['name'],
    $package['operator'],
    $package['price'],
    $package['data_label'],
    $package['validity_days'],
    $name,
    $recipientPhone,
    $payerPhone,
    $paymentOperator,
    $now,
    $now,
]);
$orderId = (int) db()->lastInsertId();
$_SESSION['tracked_phone'] = $recipientPhone;
$_SESSION['tracked_order_id'] = $orderId;
$externalId = 'GS' . $orderId . strtoupper(bin2hex(random_bytes(6)));
$userId = 'gigasurf-' . $orderId;
$update = db()->prepare('UPDATE orders SET fapshi_external_id = ?, fapshi_user_id = ?, updated_at = ? WHERE id = ?');
$update->execute([$externalId, $userId, $now, $orderId]);

$response = fapshi()->direct_pay([
    'amount' => (int) $package['price'],
    'phone' => $payerPhone,
    'name' => $name,
    'userId' => $userId,
    'externalId' => $externalId,
    'message' => 'Giga Surf — ' . $package['operator'] . ' ' . $package['name'],
]);

$transId = (string) ($response['transId'] ?? '');
$statusCode = (int) ($response['statusCode'] ?? 0);
if ($statusCode === 200 && preg_match('/^[a-zA-Z0-9]{8,10}$/', $transId)) {
    $update = db()->prepare("UPDATE orders SET fapshi_trans_id = ?, payment_status = 'PENDING', updated_at = ? WHERE id = ?");
    $update->execute([$transId, date('Y-m-d H:i:s'), $orderId]);
    $_SESSION['tracked_order_id'] = $orderId;
    flash('success', 'Demande de paiement envoyée. Validez-la sur votre téléphone, puis actualisez le suivi.');
    redirect('mes-commandes.php');
}

$status = $statusCode === 0 ? 'UNKNOWN' : 'FAILED';
$update = db()->prepare('UPDATE orders SET payment_status = ?, updated_at = ? WHERE id = ?');
$update->execute([$status, date('Y-m-d H:i:s'), $orderId]);
if ($status === 'UNKNOWN') {
    flash('warning', 'La réponse de paiement n’a pas pu être confirmée. Ne relancez pas le paiement avant d’avoir vérifié votre suivi.');
    redirect('mes-commandes.php');
}
flash('error', 'Le paiement n’a pas pu démarrer. Vérifiez le numéro Mobile Money et réessayez avec une nouvelle commande.');
redirect('commande.php?forfait=' . (int) $package['id']);
