// ✅ Put your VAPID PUBLIC key here (the one you generated)
const VAPID_PUBLIC_KEY = "BNZVm5Ld0Qfv9EIjHC-bFQznMh15pxqEumH5UHYM1ckx5xaqIDWMg3yh_--iIeOwkmO4pT3H7cVx8iLzBvLeLJ0";

function urlBase64ToUint8Array(base64String) {
    const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, "+").replace(/_/g, "/");
    const rawData = atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) outputArray[i] = rawData.charCodeAt(i);
    return outputArray;
}

async function registerServiceWorker() {
    if (!("serviceWorker" in navigator)) throw new Error("Service Worker not supported");
    const reg = await navigator.serviceWorker.register("/service-worker.js");
    return reg;
}

async function subscribeToPush() {
    const reg = await registerServiceWorker();

    const permission = await Notification.requestPermission();
    if (permission !== "granted") throw new Error("Notification permission denied");

    const existing = await reg.pushManager.getSubscription();
    if (existing) return existing;

    const sub = await reg.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
    });

    return sub;
}

// ✅ Send full subscription object (as JSON)
async function saveSubscription(subscription) {
    const res = await fetch('/api/v1/push/subscribe.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(subscription)
    });

    if (!res.ok) {
        const txt = await res.text();
        console.error('subscribe.php response:', txt);
        throw new Error('Failed to save subscription');
    }

    return res.json();
}

// Call this when user clicks "Enable Notifications"
async function enablePushNotifications() {
    const sub = await subscribeToPush();

    // IMPORTANT: send full subscription returned by pushManager.subscribe()
    const saved = await saveSubscription(sub.toJSON());

    console.log("✅ Push subscription saved:", saved);
    alert("✅ Notifications enabled!");
}

// OPTIONAL: expose to window so you can call from button onclick
window.enablePushNotifications = enablePushNotifications;
