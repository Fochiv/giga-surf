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
if (!$order || (!isAdmin() && (int) ($_SESSION['tracked_order_id'] ?? 0) !== (int) $order['id'])) {
    http_response_code(404);
    exit('Commande introuvable.');
}
if (refreshOrderPayment($order)) {
    flash('success', 'Le statut du paiement a été vérifié directement auprès de Fapshi.');
} else {
    flash('warning', 'Le statut n’a pas changé ou Fapshi est momentanément indisponible. Réessayez dans quelques instants.');
}
redirect($returnTo === 'admin' ? 'Admin/commandes.php' : 'mes-commandes.php');
