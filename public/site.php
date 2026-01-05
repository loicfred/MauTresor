<?php
include __DIR__ . '/../config/auth.php';
checksForAdmin();


require_once __DIR__ . "/../assets/obj/Place.php";
require_once __DIR__ . "/../assets/obj/DBObject.php";
use assets\obj\Place;

$segments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
$place = Place::getByID($segments[1]);
?>

<!DOCTYPE html>
<html lang="en">
<head id="master-head">
    <title>Site | MauTresor</title>

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
            padding: 75px 15% 0;
        }
        .map-body {
            display: flex;
            flex-direction: column;
            background: url("https://assets.mautresor.mu/img/scroll_body.png");
            background-repeat: repeat-y;
            background-position: center top;
            background-size: contain;
            padding: 20px 20% 50px;
        }
        .map-bottom {
            display: flex;
            flex-direction: column;
            background: url("https://assets.mautresor.mu/img/scroll_bottom.png");
            background-repeat: repeat-y;
            background-position: center top;
            background-size: 100% 100%;
            padding: 0 20% 50px;
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
    <div class="carousel-wrap" id="carouselWrap">
        <div class="carousel" id="carousel">
            <?php
            foreach ($place->getImages() as $img):
            ?>
                <img draggable="false" class="slide" src="https://api.mautresor.mu/v1/img/place/<?= $img->ID ?>" alt="img.png">
            <?php
            endforeach;
            ?>
        </div>
        <div class="dots" id="dots"></div>
    </div>

    <div class="map-box">
        <div class="map-top">
            <h2 class="align-self-center"><?= $place->Name ?></h2>
        </div>
        <div class="map-body">
            <p><?= $place->Description ?></p>
        </div>
        <div class="map-bottom">
            <p><?= $place->Name ?></p>
        </div>
    </div>
</main>

<script src="https://assets.mautresor.mu/js/app.js"></script>
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
        // small drag translate (visual)
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

</body>
</html>
