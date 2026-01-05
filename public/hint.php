<?php
include __DIR__ . '/../config/auth.php';
checksForAdmin();


require_once __DIR__ . "/../assets/obj/Hint.php";
require_once __DIR__ . "/../assets/obj/DBObject.php";
use assets\obj\Hint;

$segments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
$hint = Hint::getByID($segments[1]);
?>

<!DOCTYPE html>
<html lang="en">
<head id="master-head">
    <title>Hint | MauTresor</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="https://assets.mautresor.mu/manifest.json">
    <meta name="theme-color" content="#822BD9">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="icon" href="https://assets.mautresor.mu/img/logo_transparent.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://assets.mautresor.mu/css/main.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        html, body {
            overflow-x: hidden;
        }

        .carousel-wrap {
            position:relative;overflow:hidden;border-radius:12px;
            background:linear-gradient(180deg,#07172a,#041226);height:220px;
            border: 2px solid black;
            margin: 5px 5px 30px;
        }
        .carousel {
            display:flex;transition:transform .28s ease;height:100%
        }
        .slide {
            min-width:100%;box-sizing:border-box;display:flex;align-items:center;justify-content:center;flex-direction:column
        }
        .dots {
            position:absolute;left:50%;transform:translateX(-50%);bottom:10px;display:flex;gap:6px
        }
        .dot {
            width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,0.12)
        }
        .dot.active {
            background: #3da8cf;box-shadow:0 0 6px rgba(6,182,212,0.14)
        }


        .map-box {
            margin: 10px -20px 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: url("https://assets.mautresor.mu/img/scroll_treasure.png");
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
            background-size: 100% 100%;
            padding: 10% 20% 10%;
        }
        .map-box * {
            color: black;
        }


        .exitBtn {
            position: absolute; background-color: #00000000; border: none; right: 15px; top: 5px; width: 10%; height: 5%; background-image: url('https://assets.mautresor.mu/img/X.png'); background-position: center; background-size: contain; background-repeat: no-repeat;
        }
        .exitBtn:hover {
            cursor: pointer;
            scale: 1.01;
        }
    </style>
</head>
<body>

<?php
require_once __DIR__ . '/../assets/fragments/header.php';
?>

<main style="position: relative">
    <button class="exitBtn"></button>
    <div class="map-box">
        <div class="map-top">
            <h5 class="align-self-center" style="text-align: center;"><?= $hint->Name ?></h5>
        </div>
        <div class="map-body">
            <p style="font-size: 16px;"><?= $hint->Description ?></p>
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

<script src="https://assets.mautresor.mu/js/app.js"></script>
<script>
    function openScanner() {
        const modal = new bootstrap.Modal(document.getElementById("scanModal"));
        modal.show();
        document.getElementById("reader").style.border = "none";
    }

    function onScanSuccess(decodedText, decodedResult) {
        document.getElementById("result").innerText = decodedText;
        html5QrcodeScanner.clear();
    }

    const html5QrcodeScanner = new Html5QrcodeScanner(
        "reader",
        { fps: 10, qrbox: 250 }
    );
    html5QrcodeScanner.render(onScanSuccess);
</script>
</body>
</html>
