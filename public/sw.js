self.addEventListener('push', function (event) {
    var data = {};
    try { data = event.data ? event.data.json() : {}; } catch (e) {}

    var title = data.title || 'ServiceBazzar';
    var options = {
        body:  data.body  || '',
        icon:  data.icon  || '/assets/images/logo/favicon_new.png',
        badge: data.badge || '/assets/images/logo/favicon_new.png',
        data:  { url: data.url || '/user/listing' },
        requireInteraction: true
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    var target = (event.notification.data && event.notification.data.url) || '/user/listing';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (list) {
            for (var i = 0; i < list.length; i++) {
                if (list[i].url === target && 'focus' in list[i]) return list[i].focus();
            }
            if (clients.openWindow) return clients.openWindow(target);
        })
    );
});
