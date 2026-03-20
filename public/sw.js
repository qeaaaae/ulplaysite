self.addEventListener('push', function (event) {
    if (!event.data) return;
    var data = {};
    try {
        data = event.data.json();
    } catch (e) {
        data = { title: 'Уведомление', body: event.data.text ? event.data.text() : '' };
    }
    var title = data.title || 'UlPlay';
    var options = {
        body: data.body || '',
        icon: data.icon || '/favicon.svg',
        badge: data.badge || '/favicon.svg',
        tag: 'ulplay-' + Date.now(),
        requireInteraction: true,
        silent: false,
        data: { url: data.url || '/' },
    };
    if (data.sound) options.sound = data.sound;
    var soundUrl = data.sound || '/sounds/notification.mp3';
    event.waitUntil(
        self.registration.showNotification(title, options).catch(function () {
            return self.registration.showNotification(title, {
                body: options.body,
                icon: options.icon,
                badge: options.badge,
                sound: soundUrl,
                data: options.data,
                tag: options.tag,
                requireInteraction: options.requireInteraction,
                silent: options.silent,
            });
        }).then(function () {
            return self.clients.matchAll({ type: 'window', includeUncontrolled: true });
        }).then(function (clientList) {
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
