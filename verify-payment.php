<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('mes-commandes.php');
}
verifyCsrf();
$orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$returnTo = (string) ($_POST['return_to'] ?? 'tracking');
if ($returnTo === 'admin') {
    requireAdmin();
}
$stmt = db()->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
$stmt->execute([(int) $orderId]);
$order = $stmt->fetch();
$trackedPhone = normalizePhone((string) ($_SESSION['tracked_phone'] ?? ''));
if (!$order || (!isAdmin() && (!validCameroonPhone($trackedPhone) || normalizePhone((string) $order['recipient_phone']) !== $trackedPhone))) {
    http_response_code(404);
    exit('Commande introuvable.');
}
if (refreshOrderPayment($order)) {
    flash('success', 'Le statut du paiement a été actualisé.');
} else {
    flash('warning', 'Le statut du paiement n’a pas changé ou le service est momentanément indisponible. Réessayez dans quelques instants.');
}
redirect($returnTo === 'admin' ? 'Admin/commandes.php' : 'mes-commandes.php');
