<?php
declare(strict_types=1);

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function textLength(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function lowerText(string $value): string
{
    return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
}

function url(string $path = ''): string
{
    global $appBaseUrl;
    $path = '/' . ltrim($path, '/');
    return ($appBaseUrl !== '' ? $appBaseUrl : '') . $path;
}

function redirect(string $path): never
{
    header('Location: ' . url($path), true, 303);
    exit;
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrfToken()) . '">';
}

function verifyCsrf(): void
{
    $token = (string) ($_POST['csrf_token'] ?? '');
    if ($token === '' || !hash_equals(csrfToken(), $token)) {
        http_response_code(419);
        exit('Votre session a expiré. Revenez en arrière et réessayez.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function consumeFlashes(): array
{
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return is_array($items) ? $items : [];
}

function currentLanguage(): string
{
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'], true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    return $_SESSION['lang'] ?? 'fr';
}

function t(string $key): string
{
    static $translations = [
        'fr' => [
            'brand' => 'Giga Surf', 'home' => 'Accueil', 'plans' => 'Forfaits', 'orders' => 'Mes commandes',
            'language' => 'Langue', 'french' => 'Français', 'english' => 'English',
            'orange' => 'Orange', 'mtn' => 'MTN', 'camtel' => 'Camtel / Blue',
            'how_title' => 'Comment ça marche', 'step1' => 'Choisissez votre opérateur et votre forfait.',
            'step2' => 'Indiquez le numéro à recharger.', 'step3' => 'Effectuez le paiement en toute sécurité.',
            'choose_operator' => 'Choisissez votre opérateur', 'choose_plan' => 'Choisissez votre forfait',
            'buy' => 'Acheter', 'validity' => 'Valable', 'days' => 'jours', 'all' => 'Tous',
            'daily' => 'Journaliers', 'weekly' => 'Hebdomadaires', 'monthly' => 'Mensuels',
            'secure_payment' => 'Paiement sécurisé', 'fast_delivery' => 'Livraison rapide',
            'no_account' => 'Sans compte client', 'follow_order' => 'Suivre une commande',
            'no_packages' => 'Aucun forfait disponible pour le moment.',
            'checkout' => 'Finaliser la commande', 'name' => 'Votre nom', 'recipient' => 'Numéro à recharger',
            'payer' => 'Numéro Mobile Money pour payer', 'wallet' => 'Opérateur de paiement',
            'order_code' => 'Référence de commande', 'phone' => 'Numéro de téléphone',
            'track' => 'Suivre ma commande', 'payment_pending' => 'Paiement en attente',
            'paid' => 'Paiement confirmé', 'delivered' => 'Forfait livré',
            'not_delivered' => 'En attente de livraison', 'failed' => 'Paiement échoué',
            'pending' => 'En attente', 'menu' => 'Menu',
        ],
        'en' => [
            'brand' => 'Giga Surf', 'home' => 'Home', 'plans' => 'Bundles', 'orders' => 'My orders',
            'language' => 'Language', 'french' => 'Français', 'english' => 'English',
            'orange' => 'Orange', 'mtn' => 'MTN', 'camtel' => 'Camtel / Blue',
            'how_title' => 'How it works', 'step1' => 'Choose your operator and bundle.',
            'step2' => 'Enter the number to top up.', 'step3' => 'Pay securely.',
            'choose_operator' => 'Choose your operator', 'choose_plan' => 'Choose your bundle',
            'buy' => 'Buy', 'validity' => 'Valid for', 'days' => 'days', 'all' => 'All',
            'daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly',
            'secure_payment' => 'Secure payment', 'fast_delivery' => 'Fast delivery',
            'no_account' => 'No customer account', 'follow_order' => 'Track an order',
            'no_packages' => 'No bundles are available right now.',
            'checkout' => 'Complete your order', 'name' => 'Your name', 'recipient' => 'Number to top up',
            'payer' => 'Mobile Money number to pay', 'wallet' => 'Payment operator',
            'order_code' => 'Order reference', 'phone' => 'Phone number',
            'track' => 'Track my order', 'payment_pending' => 'Payment pending',
            'paid' => 'Payment confirmed', 'delivered' => 'Bundle delivered',
            'not_delivered' => 'Awaiting delivery', 'failed' => 'Payment failed',
            'pending' => 'Pending', 'menu' => 'Menu',
        ],
    ];
    $lang = currentLanguage();
    return $translations[$lang][$key] ?? $translations['fr'][$key] ?? $key;
}

function normalizePhone(string $phone): string
{
    $phone = preg_replace('/[\s().-]+/', '', trim($phone)) ?? '';
    if (str_starts_with($phone, '+237')) {
        $phone = substr($phone, 4);
    } elseif (str_starts_with($phone, '237') && strlen($phone) === 12) {
        $phone = substr($phone, 3);
    }
    return $phone;
}

function validCameroonPhone(string $phone): bool
{
    return (bool) preg_match('/^6[0-9]{8}$/', normalizePhone($phone));
}

function formatXaf(int|float|string|null $amount): string
{
    return number_format((float) $amount, 0, ',', ' ') . ' FCFA';
}

function formatDate(?string $date): string
{
    if (!$date) {
        return '—';
    }
    $timestamp = strtotime($date);
    return $timestamp ? date('d/m/Y à H:i', $timestamp) : '—';
}

function isAdmin(): bool
{
    return !empty($_SESSION['admin_id']);
}

function requireAdmin(): void
{
    if (!isAdmin()) {
        flash('error', 'Connectez-vous pour accéder à l’espace admin.');
        redirect('Admin/login.php');
    }
}

function adminExists(): bool
{
    return (int) db()->query('SELECT COUNT(*) FROM admin_users')->fetchColumn() > 0;
}

function paymentStatusLabel(string $status): string
{
    return match (strtoupper($status)) {
        'SUCCESSFUL' => 'Paiement confirmé',
        'FAILED' => 'Paiement échoué',
        'EXPIRED' => 'Paiement expiré',
        'PENDING', 'CREATED' => 'Paiement en attente',
        'UNKNOWN' => 'Vérification requise',
        default => $status,
    };
}

function updateOrderFromFapshi(array $event): bool
{
    $transId = (string) ($event['transId'] ?? '');
    $externalId = (string) ($event['externalId'] ?? '');
    $amount = (int) ($event['amount'] ?? 0);
    if ($transId === '' || $externalId === '' || $amount < 100 || !isset($event['status'])) {
        return false;
    }

    $stmt = db()->prepare('SELECT * FROM orders WHERE fapshi_external_id = ? LIMIT 1');
    $stmt->execute([$externalId]);
    $order = $stmt->fetch();
    if (!$order || (int) $order['amount'] !== $amount) {
        return false;
    }

    $status = strtoupper((string) $event['status']);
    if (!in_array($status, ['CREATED', 'PENDING', 'SUCCESSFUL', 'FAILED', 'EXPIRED'], true)) {
        return false;
    }
    $paidAt = $order['paid_at'];
    if ($status === 'SUCCESSFUL') {
        $confirmedAt = (string) ($event['dateConfirmed'] ?? '');
        $confirmedTime = $confirmedAt !== '' ? strtotime($confirmedAt) : false;
        $paidAt = $confirmedTime !== false ? date('Y-m-d H:i:s', $confirmedTime) : date('Y-m-d H:i:s');
    }
    $revenue = isset($event['revenue']) ? (int) $event['revenue'] : null;
    $update = db()->prepare('UPDATE orders SET fapshi_trans_id = ?, payment_status = ?, fapshi_revenue = COALESCE(?, fapshi_revenue), paid_at = ?, updated_at = ? WHERE id = ?');
    $update->execute([$transId, $status, $revenue, $paidAt, date('Y-m-d H:i:s'), $order['id']]);
    return true;
}

function refreshOrderPayment(array $order): bool
{
    $transId = (string) ($order['fapshi_trans_id'] ?? '');
    if ($transId === '') {
        return false;
    }
    $result = fapshi()->payment_status($transId);
    if (($result['statusCode'] ?? 0) !== 200
        || (string) ($result['externalId'] ?? '') !== (string) $order['fapshi_external_id']
        || (int) ($result['amount'] ?? 0) !== (int) $order['amount']
        || (string) ($result['transId'] ?? '') !== $transId) {
        return false;
    }
    return updateOrderFromFapshi($result);
}

function orderTrackingStatus(array $order): string
{
    if (($order['payment_status'] ?? '') === 'SUCCESSFUL') {
        return ($order['delivery_status'] ?? '') === 'delivered' ? t('delivered') : t('not_delivered');
    }
    return paymentStatusLabel((string) ($order['payment_status'] ?? 'CREATED'));
}

function adminNav(string $active): array
{
    return [
        'index.php' => ['label' => 'Vue d’ensemble', 'icon' => 'grid', 'active' => $active === 'dashboard'],
        'commandes.php' => ['label' => 'Commandes', 'icon' => 'receipt', 'active' => $active === 'orders'],
        'forfaits.php' => ['label' => 'Forfaits', 'icon' => 'package', 'active' => $active === 'packages'],
        'avis.php' => ['label' => 'Avis clients', 'icon' => 'message', 'active' => $active === 'reviews'],
        'parametres.php' => ['label' => 'Paramètres', 'icon' => 'settings', 'active' => $active === 'settings'],
    ];
}
