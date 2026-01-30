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
