<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin : *");

require_once __DIR__ . "/../../../config/obj/Event.php";

use assets\obj\Event;

function getEvents() {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo json_encode(Event::getAll());
    }
}

getEvents();