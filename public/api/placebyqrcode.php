<?php
global $segments;
header("Content-Type: application/json");

require_once __DIR__ . "/../../config/api.php";

require_once __DIR__ . "/../../config/obj/Place.php";

use assets\obj\Place;

$segments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
$code = $segments[2] ?? null;

function getPlaceByQRCode($code) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        isFound($code);
        $object = Place::getByQRCode($code);
        isFound($object);
        $object->QRCode = null;
        $object->ThumbnailID = null;
        echo json_encode($object);
    }
}


getPlaceByQRCode($code);