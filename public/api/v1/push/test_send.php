<?php
declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/../../../../vendor/autoload.php';
$pushConfig = require __DIR__ . '/../../../../config/push.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

// DB connection
$pdo = new PDO(
    'mysql:host=localhost;port=3307;dbname=treasurehunt;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Get latest subscription
$sub = $pdo->query("SELECT * FROM push_subscriptions ORDER BY id DESC LIMIT 1")->fetch();

if (!$sub) {
    http_response_code(404);
    echo json_encode(['error' => 'No subscription found']);
    exit;
}

$subscription = Subscription::create([
    'endpoint' => $sub['endpoint'],
    'keys' => [
        'p256dh' => $sub['p256dh'],
        'auth' => $sub['auth'],
    ],
]);

$webPush = new WebPush([
    'VAPID' => [
        'subject' => 'mailto:admin@mautresor.mu',
        'publicKey'  => 'BNZVm5Ld0Qfv9EIjHC-bFQznMh15pxqEumH5UHYM1ckx5xaqIDWMg3yh_--iIeOwkmO4pT3H7cVx8iLzBvLeLJ0',
        'privateKey' => 'lSogz8iPujYidQMj6B8wezN_CNBjipjYPh560CNIH_s',
    ],
]);

$payload = json_encode([
    'title' => '🎉 MauTresor',
    'body'  => 'Push notifications are working!',
    'url'   => '/',
]);

$report = $webPush->sendOneNotification($subscription, $payload);

echo json_encode([
    'success' => $report->isSuccess(),
    'reason'  => $report->getReason(),
]);
