<?php
include __DIR__ . '/../config/auth.php';

?>

<!DOCTYPE html>
<html lang="en">
<head id="master-head">
    <title>Site | MauTresor</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="https://mautresor.mumanifest.json">
    <meta name="theme-color" content="#822BD9">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="icon" href="https://assets.mautresor.mu/img/logo_transparent.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://assets.mautresor.mu/css/main.css">
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
            margin: -30px;
            display: flex;
            flex-direction: column;
        }

        .map-top {
            display: flex;
            flex-direction: column;
            background: url("https://assets.mautresor.mu/img/scroll_top.png");
            background-repeat: repeat-y;
            background-position: center top;
            background-size: 100% auto;
            padding: 16% 20% 0;
        }
        .map-body {
            display: flex;
            flex-direction: column;
            background: url("https://assets.mautresor.mu/img/scroll_body.png");
            background-repeat: repeat-y;
            background-position: center top;
            background-size: contain;
            padding: 7.5% 20% 0;
        }
        .map-bottom {
            display: flex;
            flex-direction: column;
            background: url("https://assets.mautresor.mu/img/scroll_bottom.png");
            background-repeat: repeat-y;
            background-position: center top;
            background-size: 100% 100%;
            padding: 10% 20% 10%;
        }
        .map-box * {
            color: black;
        }
    </style>
</head>
<body>

<?php
require_once __DIR__ . '/../assets/fragments/header.php';
?>

<main>
    <div class="map-box">
        <div class="map-top">
            <h2 class="align-self-center">About Us</h2>
        </div>
        <div class="map-body">
            <p></p>







        </div>
        <div class="map-bottom">
            <p>Contact Us ~ mautresor@gmail.com</p>
        </div>
    </div>
</main>

<script src="https://assets.mautresor.mu/js/app.js"></script>

</body>
</html>