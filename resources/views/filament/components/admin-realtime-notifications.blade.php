@if(auth()->check() && auth()->user()->outlet_id === null)
<link rel="manifest" href="/manifest.json">
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Hanya Master Admin yang menjalankan pengawasan notifikasi realtime
    // Ambil waktu terakhir cek dari storage atau gunakan waktu server persis saat halaman dimuat
    let lastCheckTime = sessionStorage.getItem('admin_last_check_time') || "{{ now()->format('Y-m-d H:i:s') }}";
    
    // Simpan daftar ID pesanan yang sudah dimunculkan notifikasinya agar tidak double/berulang saat di-refresh
    let notifiedOrderIds = new Set(JSON.parse(sessionStorage.getItem('notified_order_ids') || '[]'));

    // Fungsi bantu konversi kunci VAPID Base64 ke Uint8Array
    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    // Registrasi Service Worker & VAPID Web Push (Untuk notifikasi saat browser/PWA ditutup total)
    function setupWebPushSubscription(showFeedback = false) {
        if ('serviceWorker' in navigator && 'PushManager' in window) {
            navigator.serviceWorker.register('/sw.js').then(function(reg) {
                const vapidPublicKey = "{{ config('webpush.vapid.public_key') ?: env('VAPID_PUBLIC_KEY') }}";
                if (!vapidPublicKey) {
                    if (showFeedback) {
                        alert("⚠️ PERHATIAN: VAPID_PUBLIC_KEY belum terdeteksi di server Railway Anda! Pastikan 3 variabel VAPID sudah dipasang di tab Variables Railway.");
                    }
                    return;
                }
                if (Notification.permission === 'granted') {
                    reg.pushManager.getSubscription().then(function(sub) {
                        if (!sub) {
                            reg.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
                            }).then(function(newSub) {
                                fetch('/admin/api/push-subscribe', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify(newSub)
                                }).then(() => {
                                    if (showFeedback) alert('Sukses! HP/Browser ini resmi terdaftar ke server Railway untuk menerima notifikasi pop-up saat aplikasi ditutup.');
                                });
                            }).catch(function(err) {
                                if (showFeedback) alert('⚠️ Gagal berlangganan push service FCM: ' + err.message);
                            });
                        } else {
                            fetch('/admin/api/push-subscribe', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(sub)
                            }).then(() => {
                                if (showFeedback) alert('Sukses! Langganan notifikasi HP/Browser Anda telah diperbarui di server Railway.');
                            });
                        }
                    });
                }
            }).catch(function(err) {
                if (showFeedback) alert('⚠️ Gagal mendaftarkan Service Worker: ' + err.message);
            });
        }
    }

    // Minta izin notifikasi browser/HP (jika belum diizinkan) & daftarkan Web Push
    if ("Notification" in window) {
        if (Notification.permission === "granted") {
            setupWebPushSubscription(false);
        }
    }

    // Buat tombol floating elegan untuk aktivasi manual agar browser (seperti Edge) tidak memblokir pop-up izin
    const pushBtn = document.createElement('div');
    pushBtn.id = 'muliku-push-activator';
    pushBtn.style.cssText = 'position: fixed; bottom: 20px; left: 20px; z-index: 999999; background: #1E40AF; color: white; padding: 10px 16px; border-radius: 50px; font-weight: bold; font-size: 13px; box-shadow: 0 4px 12px rgba(30,64,175,0.4); cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.3s ease; border: 1px solid #60A5FA;';
    
    if ("Notification" in window && Notification.permission === "granted") {
        pushBtn.innerHTML = '✅ <span>Notifikasi PC Aktif</span>';
        pushBtn.style.background = '#10B981';
        pushBtn.style.borderColor = '#34D399';
        setTimeout(() => { pushBtn.style.opacity = '0.7'; }, 3000);
    } else {
        pushBtn.innerHTML = '🔔 <span>Aktifkan Notifikasi PC/HP</span>';
    }

    pushBtn.addEventListener('click', function() {
        if ("Notification" in window) {
            Notification.requestPermission().then(permission => {
                if (permission === "granted") {
                    setupWebPushSubscription(true);
                    pushBtn.innerHTML = '✅ <span>Notifikasi PC Aktif</span>';
                    pushBtn.style.background = '#10B981';
                    pushBtn.style.borderColor = '#34D399';
                } else {
                    alert('Izin notifikasi ditolak oleh browser. Silakan klik ikon gembok di samping alamat URL browser untuk mengizinkan.');
                }
            });
        }
    });

    document.body.appendChild(pushBtn);

    // Fungsi menghasilkan suara Ting-Tong profesional menggunakan Web Audio API murni (Tanpa file eksternal)
    function playExecutiveChime() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(587.33, ctx.currentTime); // Nada D5
            gain1.gain.setValueAtTime(0.3, ctx.currentTime);
            gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
            
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start();
            osc1.stop(ctx.currentTime + 0.4);

            setTimeout(() => {
                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(880.00, ctx.currentTime); // Nada A5 (Tinggi/Cerah)
                gain2.gain.setValueAtTime(0.3, ctx.currentTime);
                gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.6);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start();
                osc2.stop(ctx.currentTime + 0.6);
            }, 180);
        } catch (e) {
            console.log("Audio not allowed yet by browser policy");
        }
    }

    // Fungsi menampilkan Toast Pop-up Eksklusif di pojok kanan atas
    function showFloatingToast(order) {
        const toastId = 'toast-ord-' + Date.now() + '-' + order.id;
        const toastHtml = `
            <div id="${toastId}" style="background: linear-gradient(135deg, #1E3A8A 0%, #1D4ED8 100%); color: white; padding: 16px 20px; border-radius: 14px; box-shadow: 0 20px 40px -10px rgba(30, 58, 138, 0.5); border: 1px solid #60A5FA; display: flex; align-items: center; justify-content: space-between; min-width: 330px; max-width: 420px; margin-bottom: 12px; transform: translateX(120%); transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); z-index: 999999; pointer-events: auto;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="background: rgba(255,255,255,0.2); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
                        🛍️
                    </div>
                    <div>
                        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #93C5FD;">
                            PESANAN BARU • ${order.outlet_name}
                        </div>
                        <div style="font-size: 15px; font-weight: 800; margin: 2px 0;">
                            ${order.total_amount_formatted}
                        </div>
                        <div style="font-size: 12px; color: #E0F2FE;">
                            No. Pesanan: ${order.order_number} (${order.created_at} WIB)
                        </div>
                    </div>
                </div>
                <button onclick="document.getElementById('${toastId}').remove()" style="background: none; border: none; color: #93C5FD; font-size: 18px; cursor: pointer; padding: 4px; line-height: 1;">✕</button>
            </div>
        `;

        let container = document.getElementById('admin-realtime-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'admin-realtime-toast-container';
            container.style.cssText = 'position: fixed; top: 24px; right: 24px; z-index: 999999; display: flex; flex-direction: column; pointer-events: none;';
            document.body.appendChild(container);
        }

        const wrapper = document.createElement('div');
        wrapper.innerHTML = toastHtml;
        const toastEl = wrapper.firstElementChild;
        container.prepend(toastEl);

        setTimeout(() => {
            toastEl.style.transform = 'translateX(0)';
        }, 50);

        setTimeout(() => {
            if (toastEl && toastEl.parentElement) {
                toastEl.style.transform = 'translateX(120%)';
                setTimeout(() => toastEl.remove(), 400);
            }
        }, 8000);
    }

    // Polling ringan (3 milidetik server time) setiap 8 detik
    setInterval(async () => {
        try {
            const res = await fetch(`/admin/api/check-new-orders?last_check=${encodeURIComponent(lastCheckTime)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;

            const data = await res.json();
            if (data.server_time) {
                lastCheckTime = data.server_time;
                sessionStorage.setItem('admin_last_check_time', lastCheckTime);
            }

            if (data.has_new && data.orders && data.orders.length > 0) {
                let hasFreshOrder = false;

                data.orders.forEach(order => {
                    // Cek apakah pesanan ini belum pernah dimunculkan sebelumnya
                    if (!notifiedOrderIds.has(order.id)) {
                        notifiedOrderIds.add(order.id);
                        hasFreshOrder = true;

                        showFloatingToast(order);

                        if ("Notification" in window && Notification.permission === "granted") {
                            new Notification("🛍️ Pesanan Baru Masuk - " + order.outlet_name, {
                                body: `Omset: ${order.total_amount_formatted} (${order.order_number})`,
                                icon: "/favicon.ico"
                            });
                        }
                    }
                });

                if (hasFreshOrder) {
                    playExecutiveChime();
                    // Simpan maksimal 100 ID pesanan terakhir ke session storage
                    const arrIds = Array.from(notifiedOrderIds).slice(-100);
                    sessionStorage.setItem('notified_order_ids', JSON.stringify(arrIds));

                    // Beri tahu komponen lonceng Filament (Livewire) untuk langsung memperbarui angka badge & isi laci tanpa refresh
                    if (window.Livewire) {
                        window.Livewire.dispatch('databaseNotificationsSent');
                    }
                }
            }
        } catch (e) {
            // Silently catch fetch errors when offline/tab sleeping
        }
    }, 8000);
});
</script>
@endif
