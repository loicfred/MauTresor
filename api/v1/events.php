<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../../assets/obj/Event.php";

use assets\obj\Event;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(Event::getAll());
}
