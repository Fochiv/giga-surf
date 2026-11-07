<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('mes-commandes.php');
}
verifyCsrf();
$code = strtoupper(trim((string) ($_POST['order_code'] ?? $_POST['order_reference'] ?? '')));
$phone = (string) ($_POST['phone'] ?? $_POST['phone_number'] ?? '');
if (!preg_match('/^GS-[A-F0-9]{10}$/', $code) || !validCameroonPhone($phone)) {
    flash('error', 'Vérifiez la référence et le numéro utilisés pour la commande.');
    redirect('mes-commandes.php');
}
$order = findOrderByCodeAndPhone($code, $phone);
if (!$order) {
    flash('error', 'Aucune commande ne correspond à ces informations.');
    redirect('mes-commandes.php');
}
$_SESSION['tracked_order_id'] = (int) $order['id'];
redirect('mes-commandes.php');
