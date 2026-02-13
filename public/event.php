<?php
include __DIR__ . '/../config/auth.php';

require_once __DIR__ . "/../config/obj/Event.php";
require_once __DIR__ . "/../config/obj/Event_Participant.php";
require_once __DIR__ . "/../config/obj/Hint_Found.php";
use assets\obj\Event;
use assets\obj\Event_Participant;
use assets\obj\Hint_Found;

$segments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
$event = Event::getByID($segments[1]);
if (!$event) header("Location: /");

$participant = Event_Participant::getByUserAndEvent($_SESSION['user_id'] ?? 0, $event->ID);
?>

<!DOCTYPE html>
<html lang="en">
<head id="master-head">
    <title><?= $event->Name ?> | MauTresor</title>

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
        nav {
            z-index: 5;
        }
        main {
            position: relative;
        }

        .carousel-wrap {
            background: url("/assets/img/pancarte.png");
            background-repeat: no-repeat;
            background-position: center top;
            background-size: cover;

            position: relative; overflow:hidden;border-radius:12px;
            margin: -45px -10px -10px;

            padding-top: 32.5%;
            padding-left: 18%;
            padding-right: 18%;
            padding-bottom: 50px;
            touch-action: none;
            user-select: none;
            -webkit-user-select: none;
            -webkit-touch-callout: none;

        }
        .carousel {
            display:flex;transition:transform .28s ease;
        }
        .slide {
            height: 350px;
            border: 1px solid black;
            min-width:100%;box-sizing:border-box;display:flex;align-items:center;
            justify-content:center;flex-direction:column;
            background-size: cover; background-repeat: no-repeat;
        }
        .dots {
            margin-bottom: 50px;
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
            margin-top: -50px;
            display: flex;
            flex-direction: column;
        }

        .map-top {
            z-index: 2;
            display: flex;
            flex-direction: column;
            background: url("/assets/img/scroll_top.png");
            background-repeat: no-repeat;
            background-position: center top;
            background-size: 100% auto;
            padding: 16% 20% 0;
        }
        .map-body {
            z-index: 2;
            display: flex;
            flex-direction: column;
            background: url("/assets/img/scroll_body.png");
            background-repeat: repeat-y;
            background-position: center top;
            background-size: 100% auto;
            padding: 4% 20% 0;
        }
        .map-table {
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: url("/assets/img/scroll_body.png");
            background-repeat: repeat-y;
            background-position: center top;
            background-size: 100% auto;
            padding: 4% 15% 0;
        }
        .map-bottom {
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: url("/assets/img/scroll_bottom.png") no-repeat center top;
            background-size: 100% 100%;
            padding: 0 20% 12%;
        }

        .register {
            width: 200px;
            margin-bottom: 20px;
        }

        .map-box * {
            color: black;
        }

        @media (max-width: 900px) {
            .slide {
                height: 225px;
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
        <div style="overflow: hidden; border-radius: 20px;">
            <div class="carousel" id="carousel">
                <?php
                foreach ($event->getImages() as $img):
                    ?>
                    <div draggable="false" class="slide" style="background-image: url('/api/v1/img/event/<?= $img->ID ?>'); background-position: center; background-size: cover"></div>
                <?php
                endforeach;
                ?>
            </div>
        </div>
        <div class="dots" id="dots"></div>
    </div>

    <div class="map-box">
        <div class="map-top">
            <h2 class="align-self-center text-center"><?= $event->Name ?></h2>
        </div>
        <div class="map-body">
            <p><?= $event->Description ?></p>
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                checksForLogin();
                $participant = Event_Participant::getByUserAndEvent($_SESSION['user_id'], $event->ID);
                if (isset($_GET['register'])) {
                    if ($participant) {
                        echo "<div class='alert alert-danger'>You are already registered.</div>";
                        return;
                    }
                    if (strtotime($event->StartAt) < time()) {
                        echo "<div class='alert alert-danger'>This event has already started.</div>";
                        return;
                    }
                    $participant = new Event_Participant();
                    $participant->UserID = $_SESSION['user_id'];
                    $participant->EventID = $event->ID;
                    $participant->Write();
                    echo "<div class='alert alert-success'>Successfully registered.</div>";
                } else if (isset($_GET['unregister'])) {
                    if (!$participant) {
                        echo "<div class='alert alert-danger'>You are not registered.</div>";
                        return;
                    }
                    $participant->Delete();
                    $participant = null;
                    echo "<div class='alert alert-success'>Successfully unregistered.</div>";
                }
            }
            ?>
        </div>
        <?php
        if (strtotime($event->StartAt) > time()): ?>
            <form class="map-table" method="post" action="/event/<?= $event->ID ?>?<?= $participant ? 'un' : '' ?>register">
                <button class="register btn btn-success" style="color: white;"><?= $participant ? 'Unregister' : 'Register' ?></button>
            </form>
        <?php else: ?>
            <div class="map-table">
                <table class="table table-bordered table-striped table-hover" style="border: 1px solid black">
                    <thead class="table-dark">
                    <tr>
                        <th>Hint Name</th>
                        <?php if ($participant): ?> <th class="text-center">Completed</th> <?php endif ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($event->getHints() as $hint):
                        if ($participant) {
                            $hintFound = Hint_Found::getWhere("ParticipantID = ? AND HintID = ?", $participant->ID, $hint->ID);
                        }
                        ?>
                        <tr>
                            <td style="font-size: 0.9rem;"><a href="/hint/<?= $hint->ID ?>"><?= htmlspecialchars($hint->Name ?? 'Unnamed Hint') ?></a></td>
                            <?php if ($participant): ?>
                                <td class="text-center">
                                    <?php if ($hintFound): ?>
                                        <span class="badge" style="font-size: 1.2rem;">✓</span>
                                    <?php else: ?>
                                        <span class="badge">Pending</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <div class="map-bottom">
            <p><strong>Start:</strong> <?= date('F j, Y g:i A', strtotime($event->StartAt)) ?></p>
            <p><strong>End:</strong> <?= date('F j, Y g:i A', strtotime($event->EndAt)) ?></p>
        </div>
    </div>
</main>

<script src="/assets/js/app.js"></script>
<script src="/assets/js/imagecarousel.js"></script>

</body>
</html>