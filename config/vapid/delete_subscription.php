<?php
global $pdo;
require_once __DIR__ . '/../database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['endpoint'])) {
    echo json_encode(['success' => false, 'message' => 'No endpoint provided']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE push_subscriptions SET is_active = FALSE WHERE endpoint = :endpoint");
    $stmt->execute([':endpoint' => $data['endpoint']]);

    echo json_encode(['success' => true, 'message' => 'Subscription deactivated']);

} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>