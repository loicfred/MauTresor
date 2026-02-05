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

    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css"/>

    <style>
        html, body { overflow-x: hidden; }
        main { position: relative; }
        #map { height: 100%; width: 100%; }

        /* Floating UI */
        .map-fab {
            position: absolute;
            z-index: 1000;
            right: 15px;
            display: none;
        }
        #cancelRouteBtn { bottom: 90px; }
        #recenterBtn   { bottom: 140px; }

        #routeBanner {
            position: absolute;
            z-index: 1000;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            display: none;
            max-width: calc(100% - 24px);
        }

        #travelModeBar {
            position: absolute;
            z-index: 1000;
            left: 15px;
            bottom: 90px;
            display: none;
        }

        .leaflet-routing-container {
            max-height: 45vh;
            overflow-y: auto;
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/assets/fragments/header.php'; ?>

<main>
    <div id="map"></div>

    <!-- Route active banner -->
    <div id="routeBanner" class="alert alert-primary py-2 px-3 shadow-sm mb-0">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <div class="text-truncate">
                <strong id="bannerTitle">Route active</strong>
                <span class="ms-2 badge bg-light text-dark" id="bannerEta"></span>
            </div>
            <button class="btn btn-sm btn-outline-danger" type="button" id="bannerEndBtn">
                End
            </button>
        </div>
    </div>

    <!-- Travel mode buttons -->
    <div id="travelModeBar" class="btn-group shadow-sm" role="group" aria-label="Travel mode">
        <button type="button" class="btn btn-sm btn-primary" id="mode-driving">Driving</button>
        <button type="button" class="btn btn-sm btn-outline-primary" id="mode-walking">Walking</button>
        <button type="button" class="btn btn-sm btn-outline-primary" id="mode-cycling">Cycling</button>
    </div>

    <!-- Floating buttons -->
    <button id="recenterBtn" class="btn btn-light shadow map-fab" type="button">Recenter</button>
    <button id="cancelRouteBtn" class="btn btn-danger shadow map-fab" type="button">Cancel Route</button>
</main>

<script src="/assets/js/app.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

<script>
    // ✅ NEW: if we arrive from QR scan: /map?place=ID
    const focusPlaceId = new URLSearchParams(window.location.search).get("place");

    let map = null;

    // Base layers + overlays (for layer toggle)
    let baseLayers = {};
    let placesLayer = null;
    let layerControl = null;

    // User location
    let userLatLng = null;
    let userMarker = null;
    let watchId = null;

    // Routing
    let routingControl = null;
    let destinationLatLng = null;
    let destinationName = null;
    let currentProfile = "car"; // car | foot | bike

    // UI elements
    const cancelBtn = document.getElementById("cancelRouteBtn");
    const recenterBtn = document.getElementById("recenterBtn");

    const banner = document.getElementById("routeBanner");
    const bannerTitle = document.getElementById("bannerTitle");
    const bannerEta = document.getElementById("bannerEta");
    const bannerEndBtn = document.getElementById("bannerEndBtn");

    const travelModeBar = document.getElementById("travelModeBar");
    const btnDriving = document.getElementById("mode-driving");
    const btnWalking = document.getElementById("mode-walking");
    const btnCycling = document.getElementById("mode-cycling");

    function showEl(el, show) {
        if (!el) return;
        el.style.display = show ? "block" : "none";
    }

    function formatKm(meters) {
        return (meters / 1000).toFixed(1) + " km";
    }

    function formatTime(seconds) {
        const mins = Math.round(seconds / 60);
        if (mins < 60) return mins + " min";
        const h = Math.floor(mins / 60);
        const m = mins % 60;
        return h + " h " + m + " min";
    }

    function updateModeButtonsUI(profile) {
        const setActive = (btn, active) => {
            if (!btn) return;
            btn.classList.toggle("btn-primary", active);
            btn.classList.toggle("btn-outline-primary", !active);
        };
        setActive(btnDriving, profile === "car");
        setActive(btnWalking, profile === "foot");
        setActive(btnCycling, profile === "bike");
    }

    function createOsrmRouter(profile) {
        return L.Routing.osrmv1({
            serviceUrl: "https://router.project-osrm.org/route/v1",
            profile: profile
        });
    }

    function clearRoute() {
        if (routingControl && map) {
            map.removeControl(routingControl);
            routingControl = null;
        }
        destinationLatLng = null;
        destinationName = null;

        showEl(cancelBtn, false);
        showEl(travelModeBar, false);
        showEl(banner, false);
    }

    function openGoogleMapsDirections(destLat, destLng) {
        if (!userLatLng) {
            alert("Your location is not available yet.");
            return;
        }
        const url = `https://www.google.com/maps/dir/?api=1&origin=${userLatLng.lat},${userLatLng.lng}&destination=${destLat},${destLng}`;
        window.open(url, "_blank");
    }

    function buildRoutingControl() {
        if (!map || !userLatLng || !destinationLatLng) return;

        if (routingControl) {
            map.removeControl(routingControl);
            routingControl = null;
        }

        routingControl = L.Routing.control({
            waypoints: [userLatLng, destinationLatLng],
            router: createOsrmRouter(currentProfile),
            routeWhileDragging: false,
            addWaypoints: false,
            draggableWaypoints: false,
            fitSelectedRoutes: true,
            show: false,
            lineOptions: {
                addWaypoints: false,
                styles: [
                    { color: "#1a73e8", opacity: 0.25, weight: 10 },
                    { color: "#1a73e8", opacity: 0.95, weight: 6 }
                ]
            }
        }).addTo(map);

        routingControl.on("routesfound", function(e) {
            const route = e.routes && e.routes[0];
            if (!route || !route.summary) return;

            const d = route.summary.totalDistance;
            const t = route.summary.totalTime;

            bannerTitle.textContent = destinationName ? `Route to ${destinationName}` : "Route active";
            bannerEta.textContent = `${formatTime(t)} • ${formatKm(d)}`;
            showEl(banner, true);
        });

        showEl(cancelBtn, true);
        showEl(travelModeBar, true);
        updateModeButtonsUI(currentProfile);
    }

    function showRouteToPlace(destLat, destLng, placeName) {
        if (!map || !userLatLng) {
            alert("Your location is not available yet. Please allow location access.");
            return;
        }
        destinationLatLng = L.latLng(destLat, destLng);
        destinationName = placeName || null;

        buildRoutingControl();
        map.closePopup();
    }

    function recenterOnUser() {
        if (!map || !userLatLng) return;
        map.setView([userLatLng.lat, userLatLng.lng], Math.max(map.getZoom(), 14), { animate: true });
    }

    // watchPosition: keep user marker updated + re-route (throttled)
    let lastRouteUpdateAt = 0;

    function updateUserLocation(lat, lng) {
        userLatLng = L.latLng(lat, lng);

        if (userMarker) {
            userMarker.setLatLng(userLatLng);
        } else if (map) {
            const currentLocationIcon = L.icon({
                iconUrl: '/assets/img/red_pin.png',
                iconSize: [48, 48],
                iconAnchor: [24, 48],
                popupAnchor: [0, -48]
            });
            userMarker = L.marker(userLatLng, { icon: currentLocationIcon })
                .addTo(map)
                .bindPopup("<b>Your Location</b>");
        }

        if (routingControl && destinationLatLng) {
            const now = Date.now();
            if (now - lastRouteUpdateAt > 2500) {
                lastRouteUpdateAt = now;
                routingControl.setWaypoints([userLatLng, destinationLatLng]);
            }
        }
    }

    function startWatchingPosition() {
        if (!navigator.geolocation) return;
        if (watchId !== null) return;

        watchId = navigator.geolocation.watchPosition(
            (pos) => updateUserLocation(pos.coords.latitude, pos.coords.longitude),
            (err) => console.warn("watchPosition error:", err),
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 5000 }
        );
    }

    // DOM popup builder
    function buildPlacePopup(place, distanceKm) {
        const wrap = document.createElement("div");

        const title = document.createElement("b");
        title.textContent = place.Name;
        wrap.appendChild(title);

        wrap.appendChild(document.createElement("br"));

        const dist = document.createElement("div");
        dist.textContent = `Distance from you: ${distanceKm.toFixed(2)} km`;
        wrap.appendChild(dist);

        wrap.appendChild(document.createElement("br"));

        const btnRoute = document.createElement("button");
        btnRoute.className = "btn btn-sm btn-primary";
        btnRoute.textContent = "Show Route";
        btnRoute.addEventListener("click", () => showRouteToPlace(place.Latitude, place.Longitude, place.Name));
        wrap.appendChild(btnRoute);

        const btnCancel = document.createElement("button");
        btnCancel.className = "btn btn-sm btn-outline-danger ms-2";
        btnCancel.textContent = "Cancel Route";
        btnCancel.addEventListener("click", clearRoute);
        wrap.appendChild(btnCancel);

        wrap.appendChild(document.createElement("br"));
        wrap.appendChild(document.createElement("br"));

        const btnGmaps = document.createElement("button");
        btnGmaps.className = "btn btn-sm btn-outline-secondary";
        btnGmaps.textContent = "Open in Google Maps";
        btnGmaps.addEventListener("click", () => openGoogleMapsDirections(place.Latitude, place.Longitude));
        wrap.appendChild(btnGmaps);

        return wrap;
    }

    function setupBaseLayers() {
        // Base layers (tiles)
        const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        });

        const topo = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://opentopomap.org">OpenTopoMap</a> contributors',
            maxZoom: 17
        });

        baseLayers = {
            "OpenStreetMap": osm,
            "Topo": topo
        };

        // Default base layer
        osm.addTo(map);
    }

    function setupOverlaysAndControl() {
        // Overlays
        placesLayer = L.layerGroup().addTo(map);

        // Layer control (base layers + overlays)
        layerControl = L.control.layers(
            baseLayers,
            {
                "Places": placesLayer
            },
            { collapsed: true }
        ).addTo(map);
    }

    // Button wiring
    if (cancelBtn) cancelBtn.addEventListener("click", clearRoute);
    if (recenterBtn) recenterBtn.addEventListener("click", recenterOnUser);
    if (bannerEndBtn) bannerEndBtn.addEventListener("click", clearRoute);

    if (btnDriving) btnDriving.addEventListener("click", () => {
        currentProfile = "car";
        updateModeButtonsUI(currentProfile);
        if (destinationLatLng) buildRoutingControl();
    });
    if (btnWalking) btnWalking.addEventListener("click", () => {
        currentProfile = "foot";
        updateModeButtonsUI(currentProfile);
        if (destinationLatLng) buildRoutingControl();
    });
    if (btnCycling) btnCycling.addEventListener("click", () => {
        currentProfile = "bike";
        updateModeButtonsUI(currentProfile);
        if (destinationLatLng) buildRoutingControl();
    });

    // Init map (with location)
    navigator.geolocation.getCurrentPosition(position => {
        const { latitude, longitude } = position.coords;

        map = L.map('map').setView([latitude, longitude], 13);

        setupBaseLayers();
        setupOverlaysAndControl();

        updateUserLocation(latitude, longitude);
        showEl(recenterBtn, true);
        startWatchingPosition();

        // ✅ UPDATED: Load places + store markers + auto-focus if ?place=ID
        fetch('/api/v1/places')
            .then(res => res.json())
            .then(data => {
                const markersById = {};

                data.forEach(place => {
                    const distance = getDistanceFromLatLonInKm(latitude, longitude, place.Latitude, place.Longitude);

                    const marker = L.marker([place.Latitude, place.Longitude]);
                    marker.setZIndexOffset(1000);

                    const popupEl = buildPlacePopup(place, distance);
                    marker.bindPopup(popupEl);

                    marker.addTo(placesLayer);

                    markersById[String(place.ID)] = marker;
                });

                if (focusPlaceId && markersById[String(focusPlaceId)]) {
                    const m = markersById[String(focusPlaceId)];
                    const ll = m.getLatLng();

                    map.setView([ll.lat, ll.lng], 16, { animate: true });
                    m.openPopup();
                }
            });
    }, (error) => {
        console.error('Geolocation error:', error);
        console.error('Code:', error.code, 'Message:', error.message);

        let msg = "Location unavailable. ";
        if (error.code === 1) msg += "Permission denied. Please allow location and reload.";
        else if (error.code === 2) msg += "Position unavailable (GPS/network issue).";
        else if (error.code === 3) msg += "Request timed out. Try again.";

        alert(msg);

        // Fallback: Mauritius center
        const fallbackLat = -20.3484;
        const fallbackLng = 57.5522;

        map = L.map('map').setView([fallbackLat, fallbackLng], 11);

        setupBaseLayers();
        setupOverlaysAndControl();

        fetch('/api/v1/places')
            .then(res => res.json())
            .then(data => data.forEach(place => {
                const marker = L.marker([place.Latitude, place.Longitude]);

                const wrap = document.createElement("div");
                const title = document.createElement("b");
                title.textContent = place.Name;
                wrap.appendChild(title);
                wrap.appendChild(document.createElement("br"));
                wrap.appendChild(document.createElement("br"));

                const btnGmaps = document.createElement("button");
                btnGmaps.className = "btn btn-sm btn-outline-secondary";
                btnGmaps.textContent = "Open in Google Maps";
                btnGmaps.addEventListener("click", () => openGoogleMapsDirections(place.Latitude, place.Longitude));
                wrap.appendChild(btnGmaps);

                marker.bindPopup(wrap);
                marker.addTo(placesLayer);
            }));
    }, {
        enableHighAccuracy: true,
        timeout: 5000,
        maximumAge: 0
    });
</script>

</body>
</html>
