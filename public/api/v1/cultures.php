<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../../../config/obj/Culture.php";

use assets\obj\Culture;

function getCultures() {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo json_encode(Culture::getAll());
    }
}

getCultures();