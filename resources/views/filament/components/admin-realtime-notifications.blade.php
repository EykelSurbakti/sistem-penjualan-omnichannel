@if(auth()->check() && auth()->user()->outlet_id === null)
<link rel="manifest" href="/manifest.json">
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Hanya Master Admin yang menjalankan pengawasan notifikasi realtime
    // Ambil waktu terakhir cek dari storage atau gunakan waktu server persis saat halaman dimuat
    let lastCheckTime = sessionStorage.getItem('admin_last_check_time') || "{{ now()->format('Y-m-d H:i:s') }}";
    
    // Simpan daftar ID pesanan yang sudah dimunculkan notifikasinya agar tidak double/berulang saat di-refresh
    let notifiedOrderIds = new Set(JSON.parse(sessionStorage.getItem('notified_order_ids') || '[]'));

    // Fungsi menghasilkan suara Ting-Tong profesional menggunakan Web Audio API murni
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
                osc2.frequency.setValueAtTime(880.00, ctx.currentTime); // Nada A5
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

    // Polling ringan (3 milidetik server time) setiap 8 detik saat admin membuka dasbor
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
                    if (!notifiedOrderIds.has(order.id)) {
                        notifiedOrderIds.add(order.id);
                        hasFreshOrder = true;
                        showFloatingToast(order);
                    }
                });

                if (hasFreshOrder) {
                    playExecutiveChime();
                    const arrIds = Array.from(notifiedOrderIds).slice(-100);
                    sessionStorage.setItem('notified_order_ids', JSON.stringify(arrIds));

                    // Beri tahu komponen lonceng Filament (Livewire) untuk memperbarui angka badge & isi laci
                    if (window.Livewire) {
                        window.Livewire.dispatch('databaseNotificationsSent');
                    }
                }
            }
        } catch (e) {
            // Silently catch errors when tab sleeping/offline
        }
    }, 8000);
});
</script>
@endif
