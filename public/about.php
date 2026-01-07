<?php
include __DIR__ . '/../config/auth.php';

?>

<!DOCTYPE html>
<html lang="en">
<head id="master-head">
    <title>Site | MauTresor</title>

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
    <style>
        html, body {
            overflow-x: hidden;
        }


        .map-box {
            margin: -30px;
            display: flex;
            flex-direction: column;
        }

        .map-top {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            background: url("/assets/img/scroll_top.png");
            background-repeat: repeat-y;
            background-position: center top;
            background-size: 100% auto;
            padding: 16% 20% 0;
        }
        .map-body {
            display: flex;
            flex-direction: column;
            background: url("/assets/img/scroll_body.png");
            background-repeat: repeat-y;
            background-position: center top;
            background-size: contain;
            padding: 7.5% 20% 0;
        }
        .map-bottom {
            display: flex;
            flex-direction: column;
            background: url("/assets/img/scroll_bottom.png");
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
require_once __DIR__ . '/assets/fragments/header.php';
?>

<main>
    <div class="map-box">
        <div class="map-top">
            <h2 class="align-self-center">About Us</h2>
        </div>
        <div class="map-body">
            <p>
                MauTresor is a digital platform dedicated to discovering, preserving, and sharing the hidden cultural, historical, and natural treasures of Mauritius. Our mission is to connect people with the island’s rich heritage through engaging locations, stories, and interactive experiences.
            </p>
            <p>
                From well-known landmarks to forgotten paths and local secrets, MauTresor encourages exploration beyond the usual destinations. Each place tells a story — of history, tradition, and identity — waiting to be uncovered.
            </p>
            <p>
                Designed for locals, tourists, and explorers alike, MauTresor blends technology with culture to create meaningful journeys. Whether you are searching for adventure, knowledge, or nostalgia, MauTresor helps you see Mauritius through a new lens.
            </p>
        </div>
        <div class="map-bottom">
            <p>Contact Us ~ mautresor@gmail.com</p>
        </div>
    </div>
</main>
<script src="/assets/js/app.js"></script>

</body>
</html>