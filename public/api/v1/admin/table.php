<?php
require_once __DIR__ . "/../../../../config/auth.php";
global $segments;
header("Content-Type: application/json");
header("Access-Control-Allow-Credentials: true");

require_once __DIR__ . "/../../../../config/api.php";

require_once __DIR__ . "/../../../../config/obj/DBObject.php";
use assets\obj\DBObject;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isAdmin()) isUnauthorized();

    $segments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));

    $data = [];
    $data[] = DBObject::getTableDetails($segments[3]);

    require_once __DIR__ . "/../../../../config/obj/" . $segments[4] . ".php";
    $fullClass = "assets\\obj\\$segments[4]";
    $data[] = $fullClass::getAllLimitExcept(50, 'Image');
    echo json_encode($data);
}