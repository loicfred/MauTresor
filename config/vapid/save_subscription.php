<?php
global $pdo;
require_once __DIR__ . '/../database.php';
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['subscription'])) {
    echo json_encode(['success' => false, 'message' => 'No subscription data']);
    exit;
}

$subscription = $data['subscription'];
$endpoint = $subscription['endpoint'];
$p256dh = $subscription['keys']['p256dh'];
$auth = $subscription['keys']['auth'];

try {
    // Check if subscription already exists
    $checkStmt = $pdo->prepare("SELECT id FROM treasurehunt.push_subscriptions WHERE endpoint = :endpoint");
    $checkStmt->execute([':endpoint' => $endpoint]);

    if ($checkStmt->rowCount() > 0) {
        // Update existing subscription
        $stmt = $pdo->prepare("
            UPDATE treasurehunt.push_subscriptions 
            SET user_id = :user_id, p256dh = :p256dh, auth = :auth, updated_at = NOW(), is_active = TRUE 
            WHERE endpoint = :endpoint
        ");
    } else {
        // Insert new subscription
        $stmt = $pdo->prepare("
            INSERT INTO treasurehunt.push_subscriptions (user_id, endpoint, p256dh, auth) 
            VALUES (:user_id, :endpoint, :p256dh, :auth)
        ");
    }

    $stmt->execute([
        ':user_id' => $userId,
        ':endpoint' => $endpoint,
        ':p256dh' => $p256dh,
        ':auth' => $auth
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Subscription saved successfully',
        'subscription_id' => $pdo->lastInsertId()
    ]);

} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>