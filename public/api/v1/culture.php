<?php
global $segments;
header("Content-Type: application/json");
header("Access-Control-Allow-Origin : *");

require_once __DIR__ . "/../../../config/api.php";

require_once __DIR__ . "/../../../config/obj/Culture.php";

use assets\obj\Culture;

$segments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
$id = $segments[3] ?? null;

function getCultureById($id) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        isFound($id);
        isIDNum($id);
        $object = Culture::getByID($id);
        isFound($object);
        $object->QRCode = null;
        $object->ThumbnailID = null;
        echo json_encode($object);
    }
}


getCultureById($id);