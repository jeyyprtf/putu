self.addEventListener('push', event => {
    const { title, body, path } = event.data.json();
    event.waitUntil(self.registration.showNotification(title, { body, data: { path } }));
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    const path = event.notification.data.path;
    if (['app/pages/tugas.php', 'app/pages/pengumuman.php', 'app/pages/pengaturan.php'].includes(path)) {
        event.waitUntil(self.clients.openWindow(new URL(path, self.registration.scope).href));
    }
});
