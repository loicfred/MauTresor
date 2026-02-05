<?php
header("Content-Type: application/json");

require __DIR__ . "/../../config/obj/Place.php";
require __DIR__ . "/../../config/obj/Event.php";

use assets\obj\Place;
use assets\obj\Event;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $items = [];

    if (isset($_GET['s'])) {
        $search = $_GET['s'];
        if (strlen($search) > 0) {
            foreach (Event::getAll() as $event) {
                if (stripos($event->Name, $search) !== false) {
                    $items[] = ["id" => $event->ID, "type" => "event", "name" => $event->Name, "description" => $event->Description];
                }
            }
            foreach (Culture::getAll() as $culture) {
                if (stripos($culture->Name, $search) !== false) {
                    $items[] = ["id" => $culture->ID, "type" => "culture", "name" => $culture->Name, "description" => $culture->Description];
                }
            }
            foreach (Place::getAll() as $place) {
                if (stripos($place->Name, $search) !== false) {
                    $items[] = ["id" => $place->ID, "type" => "place", "name" => $place->Name, "description" => $place->Description];
                }
            }
        }
    } else {
        foreach (Event::getAll() as $event) {
            $items[] = ["id" => $event->ID, "type" => "event", "name" => $event->Name, "description" => $event->Description];
        }
        foreach (Culture::getAll() as $culture) {
            $items[] = ["id" => $culture->ID, "type" => "culture", "name" => $culture->Name, "description" => $culture->Description];
        }
        foreach (Place::getAll() as $place) {
            $items[] = ["id" => $place->ID, "type" => "place", "name" => $place->Name, "description" => $place->Description];
        }
    }

    echo json_encode($items);
}
