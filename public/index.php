<?php

include __DIR__ . '/../config/auth.php';

require_once __DIR__ . "/../config/obj/Place.php";
require_once __DIR__ . "/../config/obj/Event.php";

use assets\obj\Place;
use assets\obj\Event;
?>

<!DOCTYPE html>
<html lang="en">
<head id="master-head">
    <title>Home | MauTresor</title>

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
        .request-card {
            border-radius: 10px;
            background-color: var(--primary-color-lighter);
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            height: 100px;
            cursor: pointer;
            border: #CCCCCC solid 3px;
            box-shadow: 0 0 5px #000000;
        }
        .request-card:hover {
            scale: 1.01;
        }
    </style>
</head>

<body>

<?php
require_once __DIR__ . '/assets/fragments/header.php';
?>

<main class="page-wrap" id="pageWrap">
    <div class="page-carousel" id="pageCarousel">

        <!-- PAGE 1 -->
        <section class="page">
            <div class="container h-100 align-items-center">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 text-center text-white">

                        <img src="/assets/img/logo_transparent.png" height="250" class="mb-4" draggable="false" alt="Logo">

                        <p class="lead mb-4" style="text-shadow: 0 0 5px #000000">
                            Discover the island of Mauritius with a twist of fun!
                        </p>

                        <button class="btn btn-lg btn-primary px-5 mb-3" onclick="goToPage(3)">
                            Events
                        </button>

                    </div>
                </div>
            </div>
        </section>

        <!-- PAGE 2 -->
        <section class="page">
            <div class="container h-100 p-0">
                <div class="row g-1">
                    <?php $lplaces = Place::getLocalPlaces();
                    if (count($lplaces) == 0) echo "<h4 class='col-md-12 p-3 text-center'>No local places yet.</h4>";
                    else foreach ($lplaces as $lplace): ?>
                        <a href="/site/<?= $lplace->ID ?>" class="col-md-6">
                            <div class="request-card p-3" style="background-image: url('/api/v1/img/place/<?= $lplace->ThumbnailID ?>')">
                                <h5 style="text-shadow: 0 0 10px #000000"><?= $lplace->Name ?></h5>
                            </div>
                        </a>
                    <?php endforeach ?>
                </div>
            </div>
        </section>

        <!-- PAGE 3 -->
        <section class="page">
            <div class="container h-100 p-0">
                <div class="row g-1">
                    <?php $wplaces = Place::getWorldPlaces();
                    if (count($wplaces) == 0) echo "<h4 class='col-md-12 p-3 text-center'>No world places yet.</h4>";
                    else foreach ($wplaces as $wplace): ?>
                        <a href="/site/<?= $wplace->ID ?>" class="col-md-6">
                            <div class="request-card p-3" style="background-image: url('/api/v1/img/place/<?= $wplace->ThumbnailID ?>')">
                                <h5 style="text-shadow: 0 0 10px #000000"><?= $wplace->Name ?></h5>
                            </div>
                        </a>
                    <?php endforeach ?>
                </div>
            </div>
        </section>

        <!-- PAGE 4 -->
        <section class="page">
            <div class="container h-100 p-0">
                <div class="row g-1">
                    <?php $events = Event::getAll();
                    if (count($events) == 0) echo "<h4 class='col-md-12 p-3 text-center'>No upcoming events.</h4>";
                    else foreach ($events as $event): ?>
                        <a href="/event/<?= $event->ID ?>" class="col-md-6">
                            <div class="request-card p-3" style="background-image: url('/api/v1/img/event/<?= $event->ThumbnailID ?>')">
                                <h5 style="text-shadow: 0 0 10px #000000"><?= $event->Name ?></h5>
                            </div>
                        </a>
                    <?php endforeach ?>
                </div>
            </div>
        </section>
    </div>

</main>

<script src="/assets/js/app.js"></script>
<script src="/assets/js/pagecarousel.js"></script>

<?php
require_once __DIR__ . '/assets/fragments/bottom-nav.html';
?>

</body>
</html>