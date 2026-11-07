<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

$localConfig = [];
$localConfigPath = APP_ROOT . '/config/local.php';
if (is_file($localConfigPath)) {
    $localConfig = require $localConfigPath;
    if (!is_array($localConfig)) {
        throw new RuntimeException('La configuration locale doit retourner un tableau PHP.');
    }
}

$isReplit = (bool) getenv('REPLIT_DEV_DOMAIN');
$appEnv = (string) ($localConfig['app_env'] ?? getenv('APP_ENV') ?: ($isReplit ? 'development' : 'production'));
$isDevelopment = $appEnv !== 'production';
$appBaseUrl = rtrim((string) ($localConfig['base_url'] ?? getenv('APP_BASE_URL') ?: ''), '/');

$dbConfig = $localConfig['db'] ?? [];
$dbDriver = (string) ($dbConfig['driver'] ?? getenv('DB_DRIVER') ?: ($isReplit ? 'sqlite' : 'mysql'));
$fapshiConfig = $localConfig['fapshi'] ?? [];
$fapshiBaseUrl = rtrim((string) ($fapshiConfig['base_url'] ?? getenv('FAPSHI_BASE_URL') ?: 'https://sandbox.fapshi.com'), '/');
$fapshiLive = parse_url($fapshiBaseUrl, PHP_URL_HOST) === 'live.fapshi.com';
$fapshiConfig = [
    'apiuser' => (string) ($fapshiConfig['apiuser'] ?? getenv($fapshiLive ? 'FAPSHI_LIVE_API_USER' : 'FAPSHI_API_USER') ?: ''),
    'apikey' => (string) ($fapshiConfig['apikey'] ?? getenv($fapshiLive ? 'FAPSHI_LIVE_API_KEY' : 'FAPSHI_API_KEY') ?: ''),
    'webhook_secret' => (string) ($fapshiConfig['webhook_secret'] ?? getenv($fapshiLive ? 'FAPSHI_LIVE_WEBHOOK_SECRET' : 'FAPSHI_WEBHOOK_SECRET') ?: ''),
    'base_url' => $fapshiBaseUrl,
];

if (PHP_SAPI !== 'cli') {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    if ($secure) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    $secureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_name('gigasurf_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secureCookie,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once APP_ROOT . '/includes/functions.php';
require_once APP_ROOT . '/php/Fapshi.php';

function db(): PDO
{
    static $pdo = null;
    global $dbDriver, $dbConfig, $isReplit;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if ($dbDriver === 'sqlite') {
        if (!$isReplit && (($dbConfig['driver'] ?? '') !== 'sqlite')) {
            throw new RuntimeException('SQLite est réservé à la prévisualisation Replit. Configurez MySQL pour la production.');
        }
        $storage = APP_ROOT . '/storage';
        if (!is_dir($storage) && !mkdir($storage, 0750, true) && !is_dir($storage)) {
            throw new RuntimeException('Impossible de créer le dossier de stockage local.');
        }
        $pdo = new PDO('sqlite:' . $storage . '/gigasurf.sqlite', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
    } elseif ($dbDriver === 'mysql') {
        $host = (string) ($dbConfig['host'] ?? getenv('DB_HOST') ?: '');
        $name = (string) ($dbConfig['name'] ?? getenv('DB_NAME') ?: '');
        $user = (string) ($dbConfig['user'] ?? getenv('DB_USER') ?: '');
        $password = (string) ($dbConfig['password'] ?? getenv('DB_PASSWORD') ?: '');
        $charset = (string) ($dbConfig['charset'] ?? 'utf8mb4');
        if ($host === '' || $name === '' || $user === '') {
            throw new RuntimeException('MySQL n’est pas configuré. Copiez config/local.example.php vers config/local.php et renseignez les paramètres de la base.');
        }
        $pdo = new PDO(
            "mysql:host={$host};dbname={$name};charset={$charset}",
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } else {
        throw new RuntimeException('Pilote de base de données non pris en charge.');
    }

    initializeDatabase($pdo);
    return $pdo;
}

function initializeDatabase(PDO $pdo): void
{
    $sqlite = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
    $id = $sqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY';
    $text = $sqlite ? 'TEXT' : 'VARCHAR(255)';
    $longText = $sqlite ? 'TEXT' : 'TEXT';
    $shortText = $sqlite ? 'TEXT' : 'VARCHAR(500)';
    $int = $sqlite ? 'INTEGER' : 'INT';
    $foreignInt = $sqlite ? 'INTEGER' : 'INT UNSIGNED';

    $pdo->exec("CREATE TABLE IF NOT EXISTS packages (
        id {$id}, operator {$text} NOT NULL, name {$text} NOT NULL, price {$int} NOT NULL,
        data_label {$text} NOT NULL, validity_days {$int} NOT NULL, description {$shortText} NOT NULL DEFAULT '',
        active {$int} NOT NULL DEFAULT 1, sort_order {$int} NOT NULL DEFAULT 0,
        created_at {$text} NOT NULL, updated_at {$text} NOT NULL
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id {$id}, public_code {$text} NOT NULL UNIQUE, package_id {$foreignInt} NULL,
        package_name {$text} NOT NULL, operator {$text} NOT NULL, amount {$int} NOT NULL,
        data_label {$text} NOT NULL, validity_days {$int} NOT NULL,
        customer_name {$text} NOT NULL, recipient_phone {$text} NOT NULL, payer_phone {$text} NOT NULL,
        payment_operator {$text} NOT NULL, payment_status {$text} NOT NULL DEFAULT 'CREATED',
        delivery_status {$text} NOT NULL DEFAULT 'not_delivered',
        fapshi_trans_id {$text} NULL, fapshi_external_id {$text} NOT NULL,
        fapshi_user_id {$text} NOT NULL, fapshi_revenue {$int} NULL,
        created_at {$text} NOT NULL, updated_at {$text} NOT NULL, paid_at {$text} NULL, delivered_at {$text} NULL
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id {$id}, username {$text} NOT NULL UNIQUE, password_hash {$text} NOT NULL,
        created_at {$text} NOT NULL, updated_at {$text} NOT NULL
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id {$id}, identity_hash {$text} NOT NULL, attempted_at {$text} NOT NULL
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
        id {$id}, order_id {$foreignInt} NULL, customer_name {$text} NOT NULL, rating {$int} NOT NULL,
        body {$longText} NOT NULL, visible {$int} NOT NULL DEFAULT 0, created_at {$text} NOT NULL,
        FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE SET NULL
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key {$text} NOT NULL PRIMARY KEY, setting_value {$longText} NOT NULL
    )");

    if ($sqlite) {
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_orders_status_created ON orders(payment_status, created_at)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_orders_phone_code ON orders(recipient_phone, public_code)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_reviews_visible ON reviews(visible, created_at)');
    } else {
        $indexes = [
            'idx_orders_status_created' => 'orders(payment_status, created_at)',
            'idx_orders_phone_code' => 'orders(recipient_phone, public_code)',
            'idx_reviews_visible' => 'reviews(visible, created_at)',
        ];
        foreach ($indexes as $index => $tableAndColumns) {
            $check = $pdo->prepare('SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND index_name = ?');
            $check->execute([$index]);
            if ((int) $check->fetchColumn() === 0) {
                $pdo->exec("CREATE INDEX {$index} ON {$tableAndColumns}");
            }
        }
    }

    $count = (int) $pdo->query('SELECT COUNT(*) FROM packages')->fetchColumn();
    if ($count === 0) {
        seedPackages($pdo);
    }
}

function seedPackages(PDO $pdo): void
{
    $now = date('Y-m-d H:i:s');
    $packages = [
        ['Orange', '5 Go', 1100, '5 Go', 30, 'Forfait internet Orange valable 30 jours.', 1],
        ['Orange', '7 Go', 1600, '7 Go', 30, 'Forfait internet Orange valable 30 jours.', 2],
        ['Orange', '11 Go', 2200, '11 Go', 30, 'Forfait internet Orange valable 30 jours.', 3],
        ['Orange', '15 Go', 3300, '15 Go', 30, 'Forfait internet Orange valable 30 jours.', 4],
        ['Orange', 'Illimité 15 jours', 5500, 'Illimité', 15, 'Forfait illimité Orange valable 15 jours.', 5],
        ['Orange', 'Illimité 30 jours', 10500, 'Illimité', 30, 'Forfait illimité Orange valable 30 jours.', 6],
        ['MTN', '5 Go', 1200, '5 Go', 30, 'Forfait internet MTN valable 30 jours.', 1],
        ['MTN', '8 Go', 1700, '8 Go', 30, 'Forfait internet MTN valable 30 jours.', 2],
        ['MTN', '12 Go', 2300, '12 Go', 30, 'Forfait internet MTN valable 30 jours.', 3],
        ['MTN', '18 Go', 3100, '18 Go', 30, 'Forfait internet MTN valable 30 jours.', 4],
        ['MTN', 'Illimité 15 jours', 5400, 'Illimité', 15, 'Forfait illimité MTN valable 15 jours.', 5],
        ['MTN', 'Illimité 30 jours', 9500, 'Illimité', 30, 'Forfait illimité MTN valable 30 jours.', 6],
        ['Camtel', 'blue One S', 2000, 'Illimité · 1 Mb/s+ · 4 000 unités · 100 SMS', 7, 'Appels, SMS et internet tout-en-un.', 1],
        ['Camtel', 'blue One M', 3000, 'Illimité · 2 Mb/s · 6 000 unités · 200 SMS', 30, 'Appels, SMS et internet tout-en-un.', 2],
        ['Camtel', 'blue One L', 5000, 'Illimité · 3 Mb/s · 10 000 unités · 300 SMS', 30, 'Appels, SMS et internet tout-en-un.', 3],
        ['Camtel', 'blue One XL', 10000, 'Illimité · 4 Mb/s · 20 000 unités · 400 SMS', 30, 'Appels, SMS et internet tout-en-un.', 4],
        ['Camtel', 'blue One XXL', 20000, 'Illimité · 5 Mb/s · 40 000 unités · 500 SMS', 30, 'Appels, SMS et internet tout-en-un.', 5],
    ];
    $stmt = $pdo->prepare('INSERT INTO packages (operator, name, price, data_label, validity_days, description, active, sort_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?)');
    foreach ($packages as $package) {
        $stmt->execute([...$package, $now, $now]);
    }
}

function fapshi(): Fapshi
{
    global $fapshiConfig;
    return new Fapshi($fapshiConfig);
}

function fetchPackage(int $id, bool $activeOnly = true): ?array
{
    $sql = 'SELECT * FROM packages WHERE id = ?' . ($activeOnly ? ' AND active = 1' : '');
    $stmt = db()->prepare($sql);
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function findOrderByCodeAndPhone(string $code, string $phone): ?array
{
    $stmt = db()->prepare('SELECT * FROM orders WHERE public_code = ? AND recipient_phone = ? LIMIT 1');
    $stmt->execute([strtoupper(trim($code)), normalizePhone($phone)]);
    $row = $stmt->fetch();
    return $row ?: null;
}
