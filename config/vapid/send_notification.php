<?php
global $pdo;
require_once __DIR__ . '/../database.php';

// Load VAPID keys
$vapidKeys = json_decode(file_get_contents('vapid_keys.json'), true);
$vapidPublicKey = $vapidKeys['publicKey'];
$vapidPrivateKey = $vapidKeys['privateKey'];

// Web Push library for PHP
// Install via: composer require minishlink/web-push
require 'vendor/autoload.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

function sendPushNotification($subscription, $payload) {
    global $vapidPublicKey, $vapidPrivateKey;

    $auth = [
        'VAPID' => [
            'subject' => 'mailto:admin@maupromner.mu', // Your contact email
            'publicKey' => $vapidPublicKey,
            'privateKey' => $vapidPrivateKey,
        ],
    ];

    $webPush = new WebPush($auth);

    $report = $webPush->sendOneNotification(
        Subscription::create([
            'endpoint' => $subscription['endpoint'],
            'publicKey' => $subscription['p256dh'],
            'authToken' => $subscription['auth'],
        ]),
        $payload
    );

    return $report;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['user_id']) || !isset($data['title']) || !isset($data['message'])) {
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        exit;
    }

    try {
        // Get user's subscriptions
        $stmt = $pdo->prepare("
            SELECT endpoint, p256dh, auth
            FROM treasurehunt.push_subscriptions
            WHERE user_id = :user_id AND is_active = TRUE
        ");
        $stmt->execute([':user_id' => $data['user_id']]);
        $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        $payload = json_encode([
            'title' => $data['title'],
            'body' => $data['message'],
            'icon' => '/icon-192x192.png',
            'url' => $data['url'] ?? '/'
        ]);

        foreach ($subscriptions as $subscription) {
            $report = sendPushNotification($subscription, $payload);
            $results[] = [
                'endpoint' => $subscription['endpoint'],
                'success' => $report->isSuccess(),
                'reason' => $report->getReason()
            ];

            // Log the notification
            $logStmt = $pdo->prepare("
                INSERT INTO treasurehunt.notification_logs (subscription_id, title, message, status)
                SELECT id, :title, :message, :status
                FROM treasurehunt.push_subscriptions
                WHERE endpoint = :endpoint
            ");
            $logStmt->execute([
                ':title' => $data['title'],
                ':message' => $data['message'],
                ':status' => $report->isSuccess() ? 'sent' : 'failed',
                ':endpoint' => $subscription['endpoint']
            ]);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Notifications sent',
            'results' => $results
        ]);

    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}