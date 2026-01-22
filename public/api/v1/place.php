<?php
global $segments;
header("Content-Type: application/json");
header("Access-Control-Allow-Origin : *");

require_once __DIR__ . "/../../../config/api.php";

require_once __DIR__ . "/../../../config/obj/Place.php";

use assets\obj\Place;

$segments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
$id = $segments[3] ?? null;

function getPlaceById($id) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        isFound($id);
        isIDNum($id);
        $object = Place::getByID($id);
        isFound($object);
        $object->QRCode = null;
        $object->ThumbnailID = null;
        echo json_encode($object);
    }
}


getPlaceById($id);