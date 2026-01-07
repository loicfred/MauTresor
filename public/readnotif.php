<?php
require __DIR__ . "/../config/auth.php";
header("Content-Type: application/json");

require __DIR__ . "/../assets/obj/Notification.php";

use assets\obj\Notification;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    foreach (Notification::getOfUser($_SESSION['user_id']) as $notif):
        $notif->isRead = true;
        $notif->Update();
    endforeach;
    echo json_encode(["message" => "OK", "code" => "200"]);
} else {
    header("Location: /");
}