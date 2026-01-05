<?php

function isUnauthorized() {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized", "code" => "401", ]);
    exit;
}

function isValid($id) {
    if (!isset($id[2]) || !is_numeric($id[2])) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid ID", "code" => "400", ]);
        exit;
    }
}

function isFound($object) {
    if (!$object) {
        http_response_code(404);
        echo json_encode(["error" => "Not found", "code" => "404"]);
        exit;
    }
}
