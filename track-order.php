<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('mes-commandes.php');
}
verifyCsrf();

$phone = normalizePhone((string) ($_POST['phone_number'] ?? $_POST['phone'] ?? ''));
if (!validCameroonPhone($phone)) {
    unset($_SESSION['tracked_phone'], $_SESSION['tracked_order_id']);
    flash('error', 'Saisissez un numéro camerounais valide.');
    redirect('mes-commandes.php');
}

$stmt = db()->prepare('SELECT id FROM orders WHERE recipient_phone = ? ORDER BY id DESC LIMIT 20');
$stmt->execute([$phone]);
$orderIds = array_map('intval', array_column($stmt->fetchAll(), 'id'));
if (!$orderIds) {
    unset($_SESSION['tracked_phone'], $_SESSION['tracked_order_id']);
    flash('error', 'Aucune commande ne correspond à ce numéro.');
    redirect('mes-commandes.php');
}

$_SESSION['tracked_phone'] = $phone;
$_SESSION['tracked_order_id'] = $orderIds[0];
redirect('mes-commandes.php');
