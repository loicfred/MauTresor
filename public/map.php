<?php
include __DIR__ . '/../config/auth.php';

?>

<!DOCTYPE html>
<html lang="en">
<head id="master-head">
    <title>Map | MauTresor</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#822BD9">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="icon" href="/assets/img/logo_transparent.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <style>
        html, body {
            overflow-x: hidden;
        }

        #map {
            height: 100%;
            width: 100%;
        }
    </style>
</head>
<body>

<?php
require_once __DIR__ . '/assets/fragments/header.php';
?>

<main>
    <!--    <iframe-->
    <!--        src="https://www.google.com/maps/embed?37.7749,-122.4194"-->
    <!--        width="100%"-->
    <!--        height="100%"-->
    <!--        style="border:0; margin-bottom: -10px"-->
    <!--        allowfullscreen=""-->
    <!--        loading="lazy"-->
    <!--        referrerpolicy="no-referrer-when-downgrade">-->
    <!--    </iframe>-->
    <div id="map"></div>
</main>

<script src="/assets/js/app.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    navigator.geolocation.getCurrentPosition(position => {
        const {latitude, longitude} = position.coords;
        const map = L.map('map').setView([latitude, longitude], 13);

        const currentLocationIcon = L.icon({
            iconUrl: '/assets/img/red_pin.png',
            iconSize: [48, 48],
            iconAnchor: [24, 48],
            popupAnchor: [0, -48]
        });

        L.marker([latitude, longitude], {icon: currentLocationIcon}).addTo(map).bindPopup(`<b>Your Location</b>`);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        fetch('/api/v1/places')
            .then(res => res.json())
            .then(data => data.forEach(place => {
                    const distance = getDistanceFromLatLonInKm(latitude, longitude, place.Latitude, place.Longitude);
                    L.marker([place.Latitude, place.Longitude]).addTo(map).bindPopup(`<b>${place.Name}</b><br>Distance from you: ${distance.toFixed(2)}km`);
                })
            )
    }, (error) => {
        kms.textContent = 'Location unavailable';
        console.error('Geolocation error:', error);
    }, {
        enableHighAccuracy: true,
        timeout: 5000,
        maximumAge: 0
    })
</script>
<script src="/assets/js/app.js"></script>

</body>
</html>