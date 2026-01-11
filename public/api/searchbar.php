<?php
header("Content-Type: application/json");

require __DIR__ . "/../../config/obj/Place.php";
require __DIR__ . "/../../config/obj/Event.php";

use assets\obj\Place;
use assets\obj\Event;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $search = $_GET['s'];

    $items = [];

    foreach (Event::getAll() as $event) {
       if (stripos($event->Name, $search) !== false) {
           $items[] = ["id" => $event->ID, "type" => "event", "name" => $event->Name];
       }
    }

    foreach (Place::getAll() as $place) {
        if (stripos($place->Name, $search) !== false) {
            $items[] = ["id" => $place->ID, "type" => "place", "name" => $place->Name];
        }
    }

    echo json_encode($items);
}