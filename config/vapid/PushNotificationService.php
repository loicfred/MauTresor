<script>
    // Push Notification Service (Client-side only)
    class PushNotificationService {
        constructor() {
            // ⚠️ REPLACE THIS WITH YOUR ACTUAL PUBLIC KEY from vapid_keys.json ⚠️
            this.publicVapidKey = 'UGTKd6KfnwN0IsRs7aD8-7YtABVUtpLg1LFKhVYubEGk5k7O1dHe0iRHlaMWB6DHMjErvpVi_iLh8MsLnKaO_J'; // Replace with your actual key
            this.isSubscribed = false;
        }

        async init() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                console.log('Push notifications not supported');
                return false;
            }

            try {
                // Register service worker
                const registration = await navigator.serviceWorker.register('/service-worker.js');
                console.log('Service Worker registered');

                // Subscribe to push
                const subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: this.urlBase64ToUint8Array(this.publicVapidKey)
                });

                console.log('User subscribed');

                // Send subscription to server
                await this.sendSubscriptionToServer(subscription);

                // Schedule periodic notifications (every 6 hours)
                this.schedulePeriodicNotifications();

                return true;
            } catch (error) {
                console.error('Push notification init failed:', error);
                return false;
            }
        }

        async sendSubscriptionToServer(subscription) {
            try {
                const response = await fetch('save_subscription.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        subscription: subscription,
                        userId: <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>
                    })
                });

                return await response.json();
            } catch (error) {
                console.error('Failed to save subscription:', error);
                return { success: false };
            }
        }

        // Schedule notifications every 6 hours
        schedulePeriodicNotifications() {
            // Show first notification after 1 minute (for testing)
            setTimeout(() => {
                this.showLocalNotification();
            }, 60000); // 1 minute for testing

            // Then every 6 hours (21,600,000 milliseconds)
            setInterval(() => {
                this.showLocalNotification();
            }, 6 * 60 * 60 * 1000); // 6 hours
        }

        // Show a local notification
        async showLocalNotification() {
            if (!('Notification' in window) || Notification.permission !== 'granted') {
                return;
            }

            const registration = await navigator.serviceWorker.ready;

            // Array of random messages
            const messages = [
                '🌴 Discover new tourist spots in Mauritius!',
                '🎁 Check for discounts at nearby locations!',
                '🏝️ Have you visited any beaches today?',
                '📱 Scan QR codes to earn points and rewards!',
                '🗺️ New places waiting to be explored near you!',
                '⭐ Complete your treasure hunt challenges!'
            ];

            const randomMessage = messages[Math.floor(Math.random() * messages.length)];

            // Show notification
            registration.showNotification('MauPromner Reminder', {
                body: randomMessage,
                icon: '/assets/img/icon-192.png',
                badge: '/assets/img/icon-192.png',
                tag: 'periodic-reminder',
                renotify: true,
                vibrate: [200, 100, 200],
                actions: [
                    {
                        action: 'open',
                        title: 'Open App'
                    }
                ]
            });
        }

        urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/\-/g, '+')
                .replace(/_/g, '/');

            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);

            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }
    }

    // Initialize when page loads
    document.addEventListener('DOMContentLoaded', async () => {
        // Create and add notification button
        addNotificationButton();
    });

    function addNotificationButton() {
        const button = document.createElement('button');
        button.id = 'notification-toggle';
        button.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 12px 20px;
        background: #667eea;
        color: white;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        font-family: Arial, sans-serif;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    `;

        // Create notification icon
        const icon = document.createElement('span');
        icon.innerHTML = '🔕';
        button.appendChild(icon);

        // Create text
        const text = document.createElement('span');
        text.textContent = 'Enable Notifications';
        button.appendChild(text);

        // Check current state
        if ('Notification' in window) {
            if (Notification.permission === 'granted') {
                icon.innerHTML = '🔔';
                text.textContent = 'Notifications On';
                button.style.background = '#28a745';

                // Initialize push service if already granted
                const pushService = new PushNotificationService();
                pushService.init();

            } else if (Notification.permission === 'denied') {
                icon.innerHTML = '🔕';
                text.textContent = 'Notifications Blocked';
                button.style.background = '#dc3545';
                button.disabled = true;
            }
        } else {
            icon.innerHTML = '❌';
            text.textContent = 'Not Supported';
            button.style.background = '#6c757d';
            button.disabled = true;
        }

        // Click handler
        button.onclick = async () => {
            if (!('Notification' in window)) return;

            if (Notification.permission === 'default') {
                const permission = await Notification.requestPermission();

                if (permission === 'granted') {
                    // Initialize push service
                    const pushService = new PushNotificationService();
                    const success = await pushService.init();

                    if (success) {
                        icon.innerHTML = '🔔';
                        text.textContent = 'Notifications On';
                        button.style.background = '#28a745';

                        // Show welcome notification
                        setTimeout(() => {
                            if ('serviceWorker' in navigator) {
                                navigator.serviceWorker.ready.then(registration => {
                                    registration.showNotification('Welcome to MauPromner!', {
                                        body: 'You will receive reminders every 6 hours.',
                                        icon: '/icon-192x192.png'
                                    });
                                });
                            }
                        }, 1000);
                    }
                } else if (permission === 'denied') {
                    icon.innerHTML = '🔕';
                    text.textContent = 'Notifications Blocked';
                    button.style.background = '#dc3545';
                }
            }
        };

        document.body.appendChild(button);
    }

    // Test function - you can call this from browser console
    function testNotification() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.ready.then(registration => {
                registration.showNotification('Test Notification', {
                    body: 'Push notifications are working!',
                    icon: '/assets/img/icon-192.png',
                    tag: 'test'
                });
            });
        }
    }
</script>