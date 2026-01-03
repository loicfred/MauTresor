<?php
include __DIR__ . '/../private/auth.php';

require_once __DIR__ . "/../private/obj/User.php";
use assets\obj\User;

?>

<!DOCTYPE html>
<html lang="en">
<head id="master-head">
    <title>Home | MauDonate</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#822BD9">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="icon"  href="assets/img/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/main.css">

    <style>
        .request-card, .modal-content {
            margin: 8px;
            border-radius: 10px;
            background-color: var(--primary-color-lighter);
        }
    </style>
</head>

<body>

<?php
require_once 'fragments/header.php';
?>

<main class="page-wrap" id="pageWrap">
    <div class="page-carousel" id="pageCarousel">

        <!-- PAGE 1 -->
        <section class="page">
            <div class="container h-100 align-items-center">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 text-center text-white">

                        <img src="/assets/img/logo_transparent.png" height="250" class="mb-4" draggable="false" alt="BeatCam Logo">

                        <p class="lead mb-4">
                            Your kindness. In any way.
                        </p>

                        <a class="btn btn-lg btn-success px-5 mb-3" href="/fundraise" id="fundraiseNow">
                            Donate Now
                        </a>

                    </div>
                </div>
            </div>
        </section>

        <!-- PAGE 2 -->
        <section class="page">

        </section>

        <!-- PAGE 3 -->
        <section class="page">
            <p style="justify-content: center; align-items: center; text-align: center; padding: 30px;">Coming soon.</p>
        </section>

        <!-- PAGE 4 -->
        <section class="page">
            <div class="container h-100">
                <div class="row justify-content-center">
                    <div class="col-md-8 text-center text-white p-1">


                    </div>
                </div>
            </div>
        </section>
    </div>

</main>

<script src="/assets/js/app.js"></script>
<script src="/assets/js/pagecarousel.js"></script>

<?php
require_once 'fragments/bottom-nav.html';
?>

</body>
</html>