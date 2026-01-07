<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin : *");

require_once __DIR__ . "/../../../config/obj/Place.php";

use assets\obj\Place;

function getPlaces() {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo json_encode(Place::getAll());
    }
}

getPlaces();