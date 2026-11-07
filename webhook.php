<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

global $fapshiConfig;
header('Content-Type: text/plain; charset=utf-8');
$expectedSecret = (string) ($fapshiConfig['webhook_secret'] ?? '');
$receivedSecret = (string) ($_SERVER['HTTP_X_WH_SECRET'] ?? '');
if ($expectedSecret === '' || $receivedSecret === '' || !hash_equals($expectedSecret, $receivedSecret)) {
    http_response_code(401);
    echo 'Unauthorized';
    exit;
}
$payload = json_decode((string) file_get_contents('php://input'), true);
$transId = is_array($payload) ? (string) ($payload['transId'] ?? '') : '';
if (!preg_match('/^[a-zA-Z0-9]{8,10}$/', $transId)) {
    http_response_code(400);
    echo 'Invalid transaction';
    exit;
}
$event = fapshi()->payment_status($transId);
if (($event['statusCode'] ?? 0) !== 200 || (string) ($event['transId'] ?? '') !== $transId) {
    http_response_code(502);
    echo 'Payment status could not be verified';
    exit;
}
if (!updateOrderFromFapshi($event)) {
    http_response_code(404);
    echo 'Order not found or payment did not match';
    exit;
}
http_response_code(200);
echo 'OK';
