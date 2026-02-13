<?php
include __DIR__ . '/../config/auth.php';

require_once __DIR__ . "/../config/obj/Culture.php";
use assets\obj\Culture;

$segments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
$culture = Culture::getByID($segments[1]);
if (!$culture) header("Location: /");
?>

<!DOCTYPE html>
<html lang="en">
<head id="master-head">
    <title><?= $culture->Name ?>  | MauTresor</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#957304">
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

        .carousel-wrap {
            position:relative;overflow:hidden;border-radius:12px;
            background:linear-gradient(180deg,#07172a,#041226);height:350px;
            border: 2px solid black;
            margin: 5px 5px 25px;
            touch-action: none;
            user-select: none;
            -webkit-user-select: none;
            -webkit-touch-callout: none;
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
            margin: -25px;
            display: flex;
            flex-direction: column;
        }

        .map-top {
            display: flex;
            flex-direction: column;
            background: url("/assets/img/scroll_top.png");
            background-repeat: no-repeat;
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
            background-size: 100% auto;
            padding: 7.5% 20% 0;
        }
        .map-bottom {
            display: flex;
            flex-direction: column;
            background: url("/assets/img/scroll_bottom.png");
            background-repeat: no-repeat;
            background-position: center top;
            background-size: 100% 100%;
            padding: 10% 20% 10%;
        }
        .map-box * {
            color: black;
        }

        @media (max-width: 900px) {
            .carousel-wrap {
                height: 250px;
            }
        }
    </style>
</head>
<body>

<?php
require_once __DIR__ . '/assets/fragments/header.php';
?>

<main class="page">
    <div class="carousel-wrap" id="carouselWrap">
        <div class="carousel" id="carousel">
            <?php
            foreach ($culture->getImages() as $img):
                ?>
                <div draggable="false" class="slide" style="background: url('/api/v1/img/culture/<?= $img->ID ?>'); background-size: cover; background-repeat: no-repeat; background-position: center;"></div>
            <?php
            endforeach;
            ?>
        </div>
        <div class="dots" id="dots"></div>
    </div>

    <div class="map-box">
        <div class="map-top">
            <h2 class="align-self-center text-center"><?= $culture->Name ?></h2>
        </div>
        <div class="map-body">
            <p><?= $culture->Description ?></p>
        </div>
        <div class="map-bottom">
            <p><?= $culture->Name ?></p>
        </div>
    </div>
</main>

<script src="/assets/js/app.js"></script>
<script src="/assets/js/imagecarousel.js"></script>
</body>
</html>