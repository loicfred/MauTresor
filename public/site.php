<?php
include __DIR__ . '/../config/auth.php';

require_once __DIR__ . "/../config/obj/Place.php";
use assets\obj\Place;

$segments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
$place = Place::getByID($segments[1]);
if (!$place) header("Location: /");
?>

<!DOCTYPE html>
<html lang="en">
<head id="master-head">
    <title><?= $place->Name ?>  | MauTresor</title>

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

        .carousel-wrap {
            position:relative;overflow:hidden;border-radius:12px;
            background:linear-gradient(180deg,#07172a,#041226);height:350px;
            border: 2px solid black;
            margin: 5px 5px 25px;
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
            foreach ($place->getImages() as $img):
            ?>
                <div draggable="false" class="slide" style="background: url('/api/v1/img/place/<?= $img->ID ?>'); background-size: cover; background-repeat: no-repeat; background-position: center;"></div>
            <?php
            endforeach;
            ?>
        </div>
        <div class="dots" id="dots"></div>
    </div>

    <div class="map-box">
        <div class="map-top">
            <h2 class="align-self-center text-center"><?= $place->Name ?></h2>
        </div>
        <div class="map-body">
            <p><?= $place->Description ?></p>
        </div>
        <div class="map-bottom align-items-center">
            <p id="km" class="text-center"></p>
            <a href="/map" class="ms-auto btn btn-secondary d-flex align-items-center justify-content-center mb-3" style="color: white;">
                <svg viewBox="0 0 20 20" style="height: 20px; width: 20px; margin-right: 0.3em;">
                    <path fill="white" d="M10 1.67C6.78 1.67 4.17 4.28 4.17 7.5c0 4.38 5.83 10.83 5.83 10.83s5.83-6.46 5.83-10.83c0-3.22-2.61-5.83-5.83-5.83zm0 7.92c-1.15 0-2.08-.93-2.08-2.09s.93-2.08 2.08-2.08 2.08.93 2.08 2.08-.93 2.09-2.08 2.09z"/>
                </svg>
                Locate
            </a>
        </div>
    </div>
</main>

<script src="/assets/js/app.js"></script>
<script>
    const carousel = document.getElementById('carousel');
    const carouselWrap = document.getElementById('carouselWrap');
    const slides = carousel.querySelectorAll('.slide');
    const dots = document.getElementById('dots');
    let current = 0;
    slides.forEach((s,i)=>{ const d=document.createElement('div'); d.className='dot' + (i===0?' active':''); d.dataset.i=i; d.addEventListener('click', ()=> goTo(i)); dots.appendChild(d); });
    function updateCarousel(){ carousel.style.transform = `translateX(-${current*100}%)`; Array.from(dots.children).forEach((d,i)=>d.classList.toggle('active', i===current)); }
    function goTo(i){ current = (i+slides.length)%slides.length; updateCarousel(); }
    let startX=0, isDown=false;
    carouselWrap.addEventListener('pointerdown', e=>{ isDown=true; startX=e.clientX; carouselWrap.setPointerCapture(e.pointerId); });
    carouselWrap.addEventListener('pointermove', e=>{
        if(!isDown) return;
        const dx = e.clientX - startX;
        carousel.style.transition='none';
        carousel.style.transform = `translateX(calc(-${current*100}% + ${dx}px))`;
    });
    carouselWrap.addEventListener('pointerup', e=>{
        isDown=false; carousel.style.transition='';
        const dx = e.clientX - startX;
        if(Math.abs(dx) > 60){
            if(dx < 0) current = Math.min(current+1, slides.length-1);
            else current = Math.max(current-1, 0);
        }
        updateCarousel();
    });
    carouselWrap.addEventListener('pointerleave', ()=>{ if(isDown){ isDown=false; updateCarousel(); }});
</script>
<script
        src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap"
        async
        defer>
</script>
<script>
    function getDistanceFromLatLonInKm(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = deg2rad(lat2 - lat1);
        const dLon = deg2rad(lon2 - lon1);
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }
    function deg2rad(deg) {
        return deg * (Math.PI / 180);
    }
    const kms = document.getElementById('km');

    let userLat = null;
    let userLon = null;
    if (navigator.geolocation) {
        navigator.geolocation.watchPosition(
            (position) => {
                userLat = position.coords.latitude;
                userLon = position.coords.longitude;
                updateDistance();
            },
            (error) => {
                kms.textContent = 'Location unavailable';
                console.error('Geolocation error:', error);
            },
            {
                enableHighAccuracy: true,
                maximumAge: Infinity,
                timeout: Infinity
            }
        );
    } else {
        kms.textContent = 'Geolocation not supported';
    }

    function updateDistance() {
        if (userLat !== null && userLon !== null) {
            const distance = getDistanceFromLatLonInKm(userLat, userLon, <?= $place->Latitude ?>, <?= $place->Longitude ?>);
            kms.textContent = distance.toFixed(2) + ' km away from you';
        }
    }
</script>
</body>
</html>