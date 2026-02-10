const hamburgerBtn = document.getElementById('hamburgerBtn');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebarOverlay');
function openSidebar() {
    sidebar.classList.add('open');
    overlay.classList.add('show');
}
function closeSidebar() {
    sidebar.classList.remove('open');
    overlay.classList.remove('show');
}
hamburgerBtn.addEventListener('click', openSidebar);
overlay.addEventListener('click', closeSidebar);
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeSidebar();
});

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

function sendNotification(title, body) {
    Notification.requestPermission().then(p => {
        if (p === "granted") {
            navigator.serviceWorker.ready.then(sw =>
                sw.showNotification(title, {
                    body: body,
                    icon: "/assets/img/icons/icon-192.png", })
            );
        }
    });
}
function isClose(number, target = 5, radius = 2) {
    return Math.abs(number - target) <= radius;
}




// Check for nearby places
let places = []
fetch('/api/v1/places')
    .then(res=>res.json())
    .then(ps => places = ps);

navigator.geolocation.watchPosition(pos => {
    const myLat = pos.coords.latitude;
    const myLon = pos.coords.longitude;
    if (places.length === 0) return;
    if (Date.now() < sessionStorage.getItem('lastCheckPlaceRadius')) return;
    sessionStorage.setItem('lastCheckPlaceRadius', (Date.now() + 3 * 60 * 1000));
    places.sort((a, b) =>
        getDistanceFromLatLonInKm(myLat, myLon, a.Latitude, a.Longitude)
        - getDistanceFromLatLonInKm(myLat, myLon, b.Latitude, b.Longitude)
    );
    for (const p of places) {
        if (isClose(myLat, p.Latitude, 0.015) && isClose(myLon, p.Longitude, 0.015)) {
            sendNotification('Place Detected Nearby', p.Name + ' is within ' + getDistanceFromLatLonInKm(myLat, myLon, p.Latitude, p.Longitude).toFixed(2) + ' km from you!');
            sessionStorage.setItem('lastCheckPlaceRadius', (Date.now() + 60 * 60 * 1000));
            return;
        }
    }
}, null, {
    enableHighAccuracy: true,
    maximumAge: 0,
    timeout: 15000
});

console.log("app.js loaded successfully");


(() => {
    'use strict';

    if (!('serviceWorker' in navigator)) {
        console.info('[PWA] Service workers not supported');
        return;
    }

    window.addEventListener('load', async () => {
        try {
            const existing = await navigator.serviceWorker.getRegistration('/');
            if (existing) {
                console.log('[PWA] Service Worker already registered:', existing.scope);
                return;
            }

            const registration = await navigator.serviceWorker.register(
                '/service-worker.js',
                { scope: '/' }
            );

            console.log('[PWA] Service Worker registered:', registration.scope);
        } catch (err) {
            console.error('[PWA] Service Worker registration failed:', err);
        }
    });
})();
