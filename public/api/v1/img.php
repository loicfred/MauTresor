<?php
global $segments;

require_once __DIR__ . "/../../../config/api.php";

if ($_SERVER["REQUEST_METHOD"] === 'GET') {
    $segments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
    $type = $segments[3];
    $id = $segments[4];

    header('Cache-Control: public, max-age=86400');

    if ($type === 'avatar') {
        require_once __DIR__ . "/../../../config/obj/User.php";
        $img =  assets\obj\User::getByID($id);

        if (!isset($img) || !isset($img->Image)) {
            header('Content-Type: image/png');
            readfile(__DIR__ . '/../../assets/img/default-pfp.png');
            exit;
        }
        $data = base64_decode($img->Image);
        header('Content-Type: ' . $img->MimeType);
        header('Content-Length: ' . strlen($data));
        echo $data;
    } else {
        require_once __DIR__ . "/../../../config/obj/" . ucfirst($type) . "_Image.php";
        $fullClass = "assets\\obj\\" . ucfirst($type) . '_Image';

        $img = $fullClass::getByID($id);
        if (!$img) {
            header("Content-Type: application/json");
            isFound($img);
        }
        $data = base64_decode($img->Image);
        header('Content-Type: ' . $img->MimeType);
        header('Content-Length: ' . strlen($data));
        echo $data;
    }
}