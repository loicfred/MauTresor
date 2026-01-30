<?php
function isUnauthorized() {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized", "code" => "401", ]);
    exit;
}
function isIDNum($id) {
    if (!isset($id) || !is_numeric($id)) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid ID", "code" => "400", ]);
        exit;
    }
}
function isFound($object) {
    if (!isset($object) || !$object) {
        http_response_code(404);
        echo json_encode(["message" => "Not found", "code" => "404"]);
        exit;
    }
}

