<?php
require_once __DIR__ . "/../../config/auth.php";
global $segments;
header("Content-Type: application/json");

require_once __DIR__ . "/../../config/api.php";

require_once __DIR__ . "/../../assets/obj/Notification.php";
use assets\obj\Notification;

$segments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checksForLogin();
    if ($segments[2] === 'readAll') {
        foreach (Notification::getByUser($_SESSION['user_id']) as $notif):
            $notif->isRead = true;
            $notif->Update();
        endforeach;
        echo json_encode(["message" => "All notifications read successfully.", "code" => "200"]);
    }
}