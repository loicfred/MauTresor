<?php
global $segments;
header("Content-Type: application/json");

require_once __DIR__ . "/../../config/api.php";

require_once __DIR__ . "/../../assets/obj/Event.php";

use assets\obj\Event;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $segments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));

    isValid($segments);
    $object = Event::getByID($segments[2]);
    isFound($object);

    echo json_encode($object);
}