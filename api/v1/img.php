<?php
global $segments;

require_once __DIR__ . "/../../config/api.php";
require_once __DIR__ . "/../../assets/obj/Place_Image.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $segments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
    $type = $segments[2];
    $id = $segments[3];

    require_once __DIR__ . "/../../assets/obj/" . ucfirst($type) . "_Image.php";
    $fullClass = "assets\\obj\\" . ucfirst($type) . '_Image';

    $img = $fullClass::getByID($id);
    if ($img == null) {
        header("Content-Type: application/json");
        isFound($img);
    }

    $data = base64_decode($img->Image);
    header('Content-Type: ' . $img->MimeType);
    header('Content-Length: ' . strlen($data));
    header('Cache-Control: public, max-age=86400');

    echo $data;
}