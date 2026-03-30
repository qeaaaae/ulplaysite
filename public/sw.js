self.addEventListener('push', function (event) {
    if (!event.data) return;
    var defaultSoundUrl = new URL('/sounds/notification.mp3', self.location.origin).toString();
    var data = {};
    try {
        data = event.data.json();
    } catch (e) {
        data = {
            title: 'Уведомление',
            body: event.data.text ? event.data.text() : '',
            sound: defaultSoundUrl,
        };
    }
    var title = data.title || 'UlPlay';
    var soundUrl = data.sound || defaultSoundUrl;
    // Chrome / Edge не поддерживают options.sound в showNotification() — только системный звук.
    // Наш MP3 — через postMessage в открытые вкладки (см. app.js) и data.sound для клика по уведомлению.
    var options = {
        body: data.body || '',
        icon: data.icon || '/favicon.svg',
        badge: data.badge || '/favicon.svg',
        tag: 'ulplay-' + Date.now(),
        requireInteraction: true,
        silent: false,
        data: { url: data.url || '/', sound: soundUrl },
    };
    event.waitUntil(
        self.registration
            .showNotification(title, options)
            .catch(function () {
                return self.registration.showNotification(title, {
                    body: options.body,
                    icon: options.icon,
                    badge: options.badge,
                    data: options.data,
                    tag: options.tag,
                    requireInteraction: options.requireInteraction,
                    silent: options.silent,
                });
            })
            .then(function () {
                return self.clients.matchAll({ type: 'window', includeUncontrolled: true });
            })
            .then(function (clientList) {
                clientList.forEach(function (c) {
                    c.postMessage({ type: 'PLAY_NOTIFICATION_SOUND', url: soundUrl });
                });
            })
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    var url = event.notification.data && event.notification.data.url;
    if (url) event.waitUntil(clients.openWindow(url));
});
