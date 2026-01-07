<?php
require_once __DIR__ . "/../../../config/auth.php";
global $segments;
header("Content-Type: application/json");
header("Access-Control-Allow-Origin : *");

require_once __DIR__ . "/../../../config/api.php";

require_once __DIR__ . "/../../../config/obj/Event.php";

use assets\obj\Event;
use assets\obj\Event_Participant;

$segments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
$id = $segments[3] ?? null;


function getEventById($id) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        isFound($id);
        isIDNum($id);
        $object = Event::getByID($id);
        isFound($object);
        echo json_encode($object);
    }
}

getEventById($id);