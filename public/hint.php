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
            margin: 0 -15px 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: url("/assets/img/scroll_treasure.png") no-repeat center top;
            background-size: 100% auto;
        }

        .map-top {
            display: flex;
            flex-direction: column;
            padding: 17.25% 22% 0;
        }
        .map-body {
            display: flex;
            flex-direction: column;
            padding: 8% 22% 0;
        }
        .map-body p {
            font-size: 18px;
        }
        .map-bottom {
            display: flex;
            flex-direction: column;
            padding: 10% 20% 0;
        }
        .map-box * {
            color: black;
        }

        .map-top h5 {
            font-size: 24px;
        }

        .exitBtn {
            position: absolute; background-color: #00000000; border: none; right: 15px; top: 5px; width: 10%; height: 10%; background-image: url('/assets/img/X.png'); background-position: center; background-size: contain; background-repeat: no-repeat;
        }
        .exitBtn:hover {
            cursor: pointer;
            scale: 1.1;
        }

        @media (max-width: 900px) {
            .map-top h5 {
                font-size: 20px;
            }
            .map-body {
                padding: 5% 22% 0;
            }
            .map-body p {
                font-size: 15px;
            }
            .exitBtn {
                height: 5%;
            }
        }
    </style>
</head>
<body>

<?php
require_once __DIR__ . '/assets/fragments/header.php';
?>

<main class="page pb-0">
    <div class="map-box position-relative">
        <a href="/event/<?= $hint->EventID ?>" class="exitBtn"></a>
        <div class="map-top">
            <h5 class="align-self-center" style="text-align: center;"><?= $hint->Name ?></h5>
        </div>
        <div class="map-body">
            <p><?= $hint->Description ?></p>

            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    $participant = Event_Participant::getByUserAndEvent($_SESSION['user_id'], $hint->EventID);
                    $event = Event::getByID($hint->EventID);
                    $place = Place::getByQRCode($_POST['qrcode']);
                    $lat = $_POST['latitude'];
                    $long = $_POST['longitude'];
                    if (!isset($participant)) {
                        echo "<div class='alert alert-danger'>You are not participating in this event.</div>";
                    }

                    elseif ($event->EndAt == null) {
                        echo "<div class='alert alert-danger'>This event hasn't started yet.</div>";
                    }
                    elseif (strtotime($event->EndAt) < time()) {
                        echo "<div class='alert alert-danger'>This event has already ended.</div>";
                    }


                    elseif (!isset($_POST['latitude']) || !isset($_POST['longitude'])) {
                        echo "<div class='alert alert-danger'>No geolocation data sent.</div>";
                    }


                    elseif (!$place) {
                        echo "<div class='alert alert-danger'>Place not found.</div>";
                    }

                    elseif ($place->ID !== $hint->PlaceID) {
                        echo "<div class='alert alert-danger'>This hint is not for this place.</div>";
                    }

                    elseif (Hint_Found::getByParticipantAndHint($participant->ID, $hint->ID)) {
                        echo "<div class='alert alert-success'>You have already found this hint.</div>";
                    }

                    elseif (isClose($lat, $place->Latitude, 0.03) && isClose($long, $place->Longitude, 0.03)) {
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



    <form class="modal fade" id="scanModal" tabindex="-1" action="/hint/<?= $hint->ID ?>" method="post">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div id="reader" style="width: 100%; height: 100%;"></div>
                    <p id="result"></p>
                    <input type="text" id="qrcode" name="qrcode" class="hidden d-none">
                    <input type="text" id="latitude" name="latitude" class="hidden d-none">
                    <input type="text" id="longitude" name="longitude" class="hidden d-none">
                </div>
            </div>
        </div>
    </form>
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
                console.log('text: ' + decodedText);
                html5QrcodeScanner.clear();
                document.getElementById("qrcode").value = decodedText;
                document.getElementById("latitude").value = position.coords.latitude;
                document.getElementById("longitude").value = position.coords.longitude;
                //document.getElementById("scanModal").submit();
            },
            function() {
                alert("Location access denied");
            },
            { enableHighAccuracy: true }
        );
    }

    const html5QrcodeScanner = new Html5QrcodeScanner(
        "reader",
        { fps: 10 }
    );
    html5QrcodeScanner.render(onScanSuccess);
</script>
</body>
</html>
