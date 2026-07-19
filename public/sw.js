self.addEventListener("install", function (event) {
    self.skipWaiting();
});

self.addEventListener("activate", function (event) {
    event.waitUntil(self.clients.claim());
});

self.addEventListener("push", function (event) {
    let data = {};
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data = {
                title: "🛍️ Pesanan Baru - MULIKU STORE",
                body: event.data.text(),
                icon: "/favicon.ico"
            };
        }
    } else {
        data = {
            title: "🛍️ Pesanan Baru Masuk!",
            body: "Cek aplikasi untuk melihat detail pesanan.",
            icon: "/favicon.ico"
        };
    }

    const title = data.title || "🛍️ Pesanan Baru - MULIKU STORE";
    const options = {
        body: data.body || "Transaksi baru telah diproses oleh kasir.",
        icon: data.icon || "/favicon.ico",
        vibrate: [200, 100, 200, 100, 200],
        requireInteraction: true,
        renotify: true,
        tag: 'muliku-order-' + Date.now(),
        data: {
            url: data.action_url || "/admin"
        }
    };

    event.waitUntil(
        self.registration.showNotification(title, options).catch(function(e) {
            return self.registration.showNotification(title, {
                body: options.body,
                vibrate: [200, 100, 200]
            });
        })
    );
});

self.addEventListener("notificationclick", function (event) {
    event.notification.close();
    event.waitUntil(
        clients.matchAll({ type: "window", includeUncontrolled: true }).then(function (clientList) {
            if (clientList.length > 0) {
                let client = clientList[0];
                for (let i = 0; i < clientList.length; i++) {
                    if (clientList[i].focused) {
                        client = clientList[i];
                    }
                }
                return client.focus();
            }
            return clients.openWindow("/admin");
        })
    );
});
