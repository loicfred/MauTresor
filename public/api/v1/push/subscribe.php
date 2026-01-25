<?php
// public/api/v1/push/subscribe.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// Allow POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// Read JSON body
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}

/**
 * Support:
 *  A) Full PushSubscription JSON: { endpoint, keys: { p256dh, auth }, contentEncoding? }
 *  B) (Optional) Flat legacy: { endpoint, p256dh, auth }
 */
$endpoint = $data['endpoint'] ?? null;
$p256dh   = $data['keys']['p256dh'] ?? ($data['p256dh'] ?? null);
$auth     = $data['keys']['auth']   ?? ($data['auth'] ?? null);

if (!$endpoint || !$p256dh || !$auth) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => 'Missing fields',
        'got' => [
            'endpoint' => (bool)$endpoint,
            'p256dh'   => (bool)$p256dh,
            'auth'     => (bool)$auth
        ],
        // useful during testing:
        'received' => $data
    ]);
    exit;
}

$contentEncoding = $data['contentEncoding'] ?? ($data['content_encoding'] ?? 'aesgcm');

// OPTIONAL: attach user_id if passed (better to get from auth/session, but ok for now)
$userId = null;
if (isset($data['user_id']) && is_numeric($data['user_id'])) {
    $userId = (int)$data['user_id'];
}

try {
    // ✅ Change these to your real DB credentials
    $dbHost = 'localhost';
    $dbName = 'treasurehunt';
    $dbUser = 'root';
    $dbPass = '';     // WAMP default often empty
    $dbPort = 3307;   // your MariaDB shows 3307

    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Upsert by endpoint (endpoint must have UNIQUE index in table)
    $sql = "
        INSERT INTO push_subscriptions (user_id, endpoint, p256dh, auth, content_encoding)
        VALUES (:user_id, :endpoint, :p256dh, :auth, :content_encoding)
        ON DUPLICATE KEY UPDATE
            user_id = VALUES(user_id),
            p256dh = VALUES(p256dh),
            auth = VALUES(auth),
            content_encoding = VALUES(content_encoding)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $userId,
        ':endpoint' => $endpoint,
        ':p256dh' => $p256dh,
        ':auth' => $auth,
        ':content_encoding' => $contentEncoding,
    ]);

    echo json_encode(['ok' => true, 'message' => 'Subscription saved']);
} catch (Throwable $e) {
    error_log("PUSH SUBSCRIBE ERROR: " . $e->getMessage());

    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
}
