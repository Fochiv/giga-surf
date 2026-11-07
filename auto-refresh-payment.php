<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

verifyCsrf();
$orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$trackedPhone = normalizePhone((string) ($_SESSION['tracked_phone'] ?? ''));
if (!$orderId || !validCameroonPhone($trackedPhone)) {
    http_response_code(404);
    echo json_encode(['ok' => false]);
    exit;
}

$stmt = db()->prepare('SELECT * FROM orders WHERE id = ? AND recipient_phone = ? LIMIT 1');
$stmt->execute([(int) $orderId, $trackedPhone]);
$order = $stmt->fetch();
if (!$order) {
    http_response_code(404);
    echo json_encode(['ok' => false]);
    exit;
}

$status = strtoupper((string) $order['payment_status']);
$terminal = in_array($status, ['SUCCESSFUL', 'FAILED', 'EXPIRED'], true);
$lastPoll = (int) ($_SESSION['payment_poll_at'][(int) $orderId] ?? 0);
if (!$terminal && !empty($order['fapshi_trans_id']) && time() - $lastPoll >= 10) {
    $_SESSION['payment_poll_at'][(int) $orderId] = time();
    refreshOrderPayment($order);
    $stmt = db()->prepare('SELECT payment_status FROM orders WHERE id = ? LIMIT 1');
    $stmt->execute([(int) $orderId]);
    $status = strtoupper((string) $stmt->fetchColumn());
}

echo json_encode([
    'ok' => true,
    'status' => $status,
    'terminal' => in_array($status, ['SUCCESSFUL', 'FAILED', 'EXPIRED'], true),
]);
