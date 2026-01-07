<?php
include __DIR__ . '/../config/auth.php';
checksForLogin();

require_once __DIR__ . "/../config/obj/Hint.php";
require_once __DIR__ . "/../config/obj/Place.php";
require_once __DIR__ . "/../config/obj/Event.php";
require_once __DIR__ . "/../config/obj/Hint_Found.php";
require_once __DIR__ . "/../config/obj/Event_Participant.php";
use assets\obj\Hint;
use assets\obj\Place;
use assets\obj\Event;
use assets\obj\Hint_Found;
use assets\obj\Event_Participant;

$segments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
$hint = Hint::getByID($segments[1]);

$participant = Event_Participant::getByUserAndEvent($_SESSION['user_id'] ?? 0, $hint->EventID);
if (!$participant) header("Location: /");
?>

<!DOCTYPE html>
<html lang="en">
<head id="master-head">
    <title>Hint | MauTresor</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#822BD9">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="icon" href="/assets/img/logo_transparent.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="/assets/css/main.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        html, body {
            overflow-x: hidden;
        }

        .map-box {
            margin: 10px -20px 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: url("/assets/img/scroll_treasure.png");
            background-repeat: no-repeat;
            background-position: center top;
            background-size: 100% auto;
        }

        .map-top {
            display: flex;
            flex-direction: column;
            padding: 16% 20% 0;
        }
        .map-body {
            display: flex;
            flex-direction: column;
            padding: 7.5% 20% 0;
        }
        .map-bottom {
            display: flex;
            flex-direction: column;
            padding: 10% 20% 10%;
        }
        .map-box * {
            color: black;
        }


        .exitBtn {
            position: absolute; background-color: #00000000; border: none; right: 15px; top: 5px; width: 10%; height: 5%; background-image: url('/assets/img/X.png'); background-position: center; background-size: contain; background-repeat: no-repeat;
        }
        .exitBtn:hover {
            cursor: pointer;
            scale: 1.1;
        }
    </style>
</head>
<body>

<?php
require_once __DIR__ . '/assets/fragments/header.php';
?>

<main style="position: relative">
    <a href="/event/<?= $hint->EventID ?>" class="exitBtn"></a>
    <div class="map-box">
        <div class="map-top">
            <h5 class="align-self-center" style="text-align: center;"><?= $hint->Name ?></h5>
        </div>
        <div class="map-body">
            <p style="font-size: 16px;"><?= $hint->Description ?></p>

            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    $participant = Event_Participant::getByUserAndEvent($_SESSION['user_id'], $hint->EventID);
                    if (!isset($participant)) {
                        echo "<div class='alert alert-danger'>You are not participating in this event.</div>";
                        return;
                    }

                    $event = Event::getByID($hint->EventID)->EndAt;
                    if ($event->EndAt == null) {
                        echo "<div class='alert alert-danger'>This event hasn't started yet.</div>";
                        return;
                    }
                    if (strtotime($event->EndAt) < time()) {
                        echo "<div class='alert alert-danger'>This event has already ended.</div>";
                        return;
                    }


                    if (!isset($_POST['latitude']) || !isset($_POST['longitude'])) {
                        echo "<div class='alert alert-danger'>No geolocation data sent.</div>";
                        return;
                    }


                    $place = Place::getByQRCode($_POST['qrcode']);
                    if (!$place) {
                        echo "<div class='alert alert-danger'>Place not found.</div>";
                        return;
                    }

                    if ($place->ID !== $hint->PlaceID) {
                        echo "<div class='alert alert-danger'>This hint is not for this place.</div>";
                        return;
                    }

                    if (Hint_Found::getByParticipantAndHint($hint->ID, $participant->ID)) {
                        echo "<div class='alert alert-success'>You have already found this hint.</div>";
                        return;
                    }

                    $lat = $_POST['latitude'];
                    $long = $_POST['longitude'];
                    if (isClose($lat, $place->Latitude, 0.005) && isClose($long, $place->Longitude, 0.005)) {
                        $hintFound = new Hint_Found();
                        $hintFound->ParticipantID = $participant->ID;
                        $hintFound->HintID = $hint->ID;
                        $hintFound->Write();
                        echo "<div class='alert alert-success'>Successfully found the hint!</div>";
                    } else {
                        echo "<div class='alert alert-danger'>You are not in the right location!</div>";
                    }
                } catch (Exception $e) {
                    echo "<div class='alert alert-danger'>An error occurred...</div>";
                }
            }

            function isClose($number, $target = 5, $radius = 2) {
                return abs($number - $target) <= $radius;
            }
            ?>
        </div>
        <div class="map-bottom align-items-center">
            <button class="btn btn-primary" style="color: white; width: 100px;" onclick="openScanner()">Scan</button>
        </div>
    </div>



    <div class="modal fade" id="scanModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div id="reader" style="width: 100%; height: 100%;"></div>
                    <p id="result"></p>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="/assets/js/app.js"></script>
<script>
    function openScanner() {
        const modal = new bootstrap.Modal(document.getElementById("scanModal"));
        modal.show();
        document.getElementById("reader").style.border = "none";
    }

    function onScanSuccess(decodedText, decodedResult) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                html5QrcodeScanner.clear();
                fetch('/api/v1/hint/' + <?= $hint->ID ?>, {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        qrcode: decodedText,
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    })
                });
            },
            function() {
                alert("Location access denied");
            },
            { enableHighAccuracy: true }
        );
    }

    const html5QrcodeScanner = new Html5QrcodeScanner(
        "reader",
        { fps: 10, qrbox: 250 }
    );
    html5QrcodeScanner.render(onScanSuccess);
</script>
<script>
    function getLocation() {

    }
</script>
</body>
</html>
