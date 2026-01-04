<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../../private/obj/Place.php";

use assets\obj\Place;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(Place::getAll());
}
