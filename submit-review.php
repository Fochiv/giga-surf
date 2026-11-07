<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('mes-commandes.php');
}
verifyCsrf();
$orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
$body = trim((string) ($_POST['body'] ?? ''));
$stmt = db()->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
$stmt->execute([(int) $orderId]);
$order = $stmt->fetch();
if (!$order || (int) ($_SESSION['tracked_order_id'] ?? 0) !== (int) $order['id'] || $order['delivery_status'] !== 'delivered') {
    http_response_code(403);
    exit('Cette commande ne peut pas recevoir d’avis.');
}
if (!$rating || $rating < 1 || $rating > 5 || textLength($body) < 5 || textLength($body) > 1000) {
    flash('error', 'Choisissez une note et écrivez un avis de 5 à 1 000 caractères.');
    redirect('mes-commandes.php');
}
$existing = db()->prepare('SELECT COUNT(*) FROM reviews WHERE order_id = ?');
$existing->execute([$order['id']]);
if ((int) $existing->fetchColumn() > 0) {
    flash('warning', 'Un avis a déjà été envoyé pour cette commande.');
    redirect('mes-commandes.php');
}
$insert = db()->prepare('INSERT INTO reviews (order_id, customer_name, rating, body, visible, created_at) VALUES (?, ?, ?, ?, 0, ?)');
$insert->execute([$order['id'], $order['customer_name'], $rating, $body, date('Y-m-d H:i:s')]);
flash('success', 'Merci pour votre avis. Il sera publié après modération.');
redirect('mes-commandes.php');
