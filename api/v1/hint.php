<?php
global $segments;
header("Content-Type: application/json");
header("Access-Control-Allow-Origin : *");

require_once __DIR__ . "/../../config/api.php";

require_once __DIR__ . "/../../assets/obj/Hint.php";
require_once __DIR__ . "/../../assets/obj/Place.php";
require_once __DIR__ . "/../../assets/obj/Event.php";
require_once __DIR__ . "/../../assets/obj/Hint_Found.php";
require_once __DIR__ . "/../../assets/obj/Event_Participant.php";

use assets\obj\Hint;
use assets\obj\Place;
use assets\obj\Event;
use assets\obj\Hint_Found;
use assets\obj\Event_Participant;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        checksForLogin();
        $QRCode = $_POST['qrcode'];
        $hint = Hint::getByQRCode($QRCode);

        $participant = Event_Participant::getByUserAndEvent($_SESSION['user_id'], $hint->EventID);
        if (!isset($participant)) {
            echo json_encode(["message" => "You are not participating in this event.", "code" => "406", ]);
            return;
        }

        $event = Event::getByID($hint->EventID)->EndAt;
        if ($event->EndAt == null) {
            echo json_encode(["message" => "This event hasn't started yet.", "code" => "406", ]);
            return;
        }
        if (strtotime($event->EndAt) < time()) {
            echo json_encode(["message" => "This event has already ended.", "code" => "406", ]);
            return;
        }
        if (!isset($_POST['latitude']) || !isset($_POST['longitude'])) {
            echo json_encode(["message" => "No geolocation data sent.", "code" => "406", ]);
            return;
        }


        $lat = $_POST['latitude'];
        $long = $_POST['longitude'];
        $place = Place::getByID($hint->PlaceID);
        if (isClose($lat, $place->Latitude, 0.005) && isClose($long, $place->Longitude, 0.005)) {
            $hintFound = new Hint_Found();
            $hintFound->ParticipantID = $participant->ID;
            $hintFound->HintID = $hint->ID;
            $hintFound->Write();
            echo json_encode(["message" => "Successfully found the hint!", "code" => "200"]);
        } else {
            echo json_encode(["message" => "You are not in the right location!", "code" => "406"]);
        }
    } catch (Exception $e) {
        echo json_encode(["message" => "An error occurred...", "code" => "400", ]);
    }
}

function isClose($number, $target = 5, $radius = 2) {
    return abs($number - $target) <= $radius;
}