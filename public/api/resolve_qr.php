<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../../config/obj/Place.php";
use assets\obj\Place;

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

$code = isset($_GET["code"]) ? trim((string)$_GET["code"]) : "";
if ($code === "") {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing QR code value"]);
    exit;
}

// 1) If QR contains a URL, try extracting place id from:
//    - /site/{id}
//    - /map?place={id}
$placeId = null;
if (preg_match('#^https?://#i', $code)) {
    $parts = parse_url($code);
    $path  = $parts["path"] ?? "";
    $query = $parts["query"] ?? "";

    // /site/123
    if (preg_match('#/site/(\d+)#', $path, $m)) {
        $placeId = (int)$m[1];
    }

    // /map?place=123
    if ($placeId === null && $query) {
        parse_str($query, $qs);
        if (isset($qs["place"]) && is_numeric($qs["place"])) {
            $placeId = (int)$qs["place"];
        }
    }

    // If URL ends with just a number somewhere, last fallback:
    if ($placeId === null && preg_match('#(\d+)$#', $path, $m)) {
        $placeId = (int)$m[1];
    }
}

// 2) If scanned text is numeric, treat it as Place ID
if ($placeId === null && is_numeric($code)) {
    $placeId = (int)$code;
}

$place = null;

// Try by ID if we got one
if ($placeId !== null && $placeId > 0) {
    $place = Place::getByID($placeId);
}

// 3) Otherwise treat it as Place.QRCode (your DB field)
if (!$place) {
    $place = Place::getByQRCode($code);
}

// 4) Not found
if (!$place) {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "No place matched this QR code."
    ]);
    exit;
}

// Success
echo json_encode([
    "success" => true,
    "place" => [
        "id" => $place->ID,
        "name" => $place->Name
    ]
]);
