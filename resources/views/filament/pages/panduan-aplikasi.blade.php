<x-filament-panels::page>
    @php
        $isMasterOwner = is_null(auth()->user()?->outlet_id);
    @endphp

    <div style="padding: 24px; border-radius: 20px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.15);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div>
                <span style="background: #3b82f6; color: #ffffff; font-size: 11px; font-weight: 900; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;">
                    📚 OFFICIAL USER MANUAL BOOK
                </span>
                <h2 style="font-size: 26px; font-weight: 900; color: #ffffff; margin: 10px 0 6px 0;">
                    Panduan Lengkap Penggunaan Muliku Store POS & Omnichannel
                </h2>
                <p style="font-size: 13px; color: #cbd5e1; margin: 0; line-height: 1.6; max-width: 700px;">
                    Ikuti standar operasional prosedur (SOP) di bawah ini agar transaksi kasir cepat, stok akurat 100%, dan pengawasan eksekutif berjalan lancar.
                </p>
            </div>
            <div style="background: rgba(255, 255, 255, 0.1); padding: 12px 20px; border-radius: 14px; text-align: center; border: 1px solid rgba(255, 255, 255, 0.2);">
                <span style="font-size: 11px; color: #94a3b8; display: block; uppercase font-weight: 700;">Status Akses Anda</span>
                <span style="font-size: 16px; font-weight: 900; color: #60a5fa;">
                    {{ $isMasterOwner ? '👑 Master Admin / Owner' : '🏪 Kasir Cabang Toko' }}
                </span>
            </div>
        </div>
    </div>

    {{-- 1. TABEL AKSES LOGIN RESMI --}}
    <div style="background: #ffffff; border-radius: 16px; border: 1px solid #cbd5e1; padding: 22px; box-shadow: 0 4px 14px rgba(0,0,0,0.02);">
        <h3 style="font-size: 18px; font-weight: 900; color: #0f172a; margin: 0 0 14px 0; display: flex; align-items: center; gap: 8px;">
            <span>🔑</span> 1. Daftar Akun Resmi & Batasan Akses (*Multi-Tenant Isolation*)
        </h3>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569;">
                        <th style="padding: 12px 14px; font-weight: 800;">Nama Akun Resmi</th>
                        <th style="padding: 12px 14px; font-weight: 800;">Email Login</th>
                        <th style="padding: 12px 14px; font-weight: 800;">Password</th>
                        <th style="padding: 12px 14px; font-weight: 800;">Cakupan Cabang</th>
                        <th style="padding: 12px 14px; font-weight: 800;">Peran & Tugas Utama</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 12px 14px; font-weight: 800; color: #1e40af;">👑 Master Admin Muliku</td>
                        <td style="padding: 12px 14px; font-family: monospace;">admin@gmail.com</td>
                        <td style="padding: 12px 14px; font-family: monospace;">password</td>
                        <td style="padding: 12px 14px;"><span style="background: #dbeafe; color: #1e40af; padding: 3px 10px; border-radius: 12px; font-weight: 800; font-size: 11px;">Semua Toko (Pusat)</span></td>
                        <td style="padding: 12px 14px; color: #64748b;">Memantau seluruh omzet, laporan laba rugi, log audit, dan kelola semua stok/cabang.</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 12px 14px; font-weight: 800; color: #047857;">🏪 Kasir Prabotan</td>
                        <td style="padding: 12px 14px; font-family: monospace;">mulikuprabotan@gmail.com</td>
                        <td style="padding: 12px 14px; font-family: monospace;">password</td>
                        <td style="padding: 12px 14px;"><span style="background: #d1fae5; color: #047857; padding: 3px 10px; border-radius: 12px; font-weight: 800; font-size: 11px;">Muliku Prabotan</span></td>
                        <td style="padding: 12px 14px; color: #64748b;">Melayani transaksi POS, buka/tutup shift, dan cek stok khusus cabang Prabotan.</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 12px 14px; font-weight: 800; color: #047857;">🏪 Kasir Plastik 01</td>
                        <td style="padding: 12px 14px; font-family: monospace;">mulikuplastik01@gmail.com</td>
                        <td style="padding: 12px 14px; font-family: monospace;">password</td>
                        <td style="padding: 12px 14px;"><span style="background: #d1fae5; color: #047857; padding: 3px 10px; border-radius: 12px; font-weight: 800; font-size: 11px;">Muliku Plastik 01</span></td>
                        <td style="padding: 12px 14px; color: #64748b;">Melayani transaksi POS, buka/tutup shift, dan cek stok khusus cabang Plastik 01.</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 14px; font-weight: 800; color: #047857;">🏪 Kasir Plastik 02</td>
                        <td style="padding: 12px 14px; font-family: monospace;">mulikuplastik02@gmail.com</td>
                        <td style="padding: 12px 14px; font-family: monospace;">password</td>
                        <td style="padding: 12px 14px;"><span style="background: #d1fae5; color: #047857; padding: 3px 10px; border-radius: 12px; font-weight: 800; font-size: 11px;">Muliku Plastik 02</span></td>
                        <td style="padding: 12px 14px; color: #64748b;">Melayani transaksi POS, buka/tutup shift, dan cek stok khusus cabang Plastik 02.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- 2. PANDUAN KHUSUS KASIR CABANG --}}
    <div style="background: #ffffff; border-radius: 16px; border: 2px solid #10b981; padding: 24px; box-shadow: 0 4px 14px rgba(0,0,0,0.02);">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
            <span style="background: #10b981; color: #ffffff; font-size: 18px; padding: 6px 12px; border-radius: 12px;">🏪</span>
            <div>
                <h3 style="font-size: 20px; font-weight: 900; color: #065f46; margin: 0;">
                    Standar Operasional Prosedur (SOP) Kasir Cabang
                </h3>
                <span style="font-size: 13px; color: #059669; font-weight: 600;">Panduan wajib sehari-hari dari pagi buka shift hingga malam tutup toko</span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 18px; border-radius: 14px;">
                <span style="background: #10b981; color: #ffffff; font-size: 11px; font-weight: 900; padding: 3px 10px; border-radius: 8px;">LANGKAH 1 (PAGI)</span>
                <h4 style="font-size: 16px; font-weight: 800; color: #065f46; margin: 10px 0 6px 0;">Buka Shift & Absen Masuk</h4>
                <p style="font-size: 13px; color: #15803d; line-height: 1.5; margin: 0;">
                    Masuk ke menu <strong>Riwayat Shift & Absen</strong>. Ketik jumlah uang kembalian pagi di laci pada kolom <em>Modal Awal Laci (Rp)</em>, lalu klik tombol biru <strong>🚀 Buka Shift Pagi</strong>.
                </p>
            </div>

            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 18px; border-radius: 14px;">
                <span style="background: #10b981; color: #ffffff; font-size: 11px; font-weight: 900; padding: 3px 10px; border-radius: 8px;">LANGKAH 2 (SIANG/SORE)</span>
                <h4 style="font-size: 16px; font-weight: 800; color: #065f46; margin: 10px 0 6px 0;">Melayani Transaksi POS</h4>
                <p style="font-size: 13px; color: #15803d; line-height: 1.5; margin: 0;">
                    Buka menu <strong>Layar Kasir (POS)</strong>. Scan barcode atau cari barang. Pilih metode bayar (<em>Tunai / QRIS / Transfer</em>). Klik <strong>✅ Proses Pembayaran</strong> untuk cetak struk thermal.
                </p>
            </div>

            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 18px; border-radius: 14px;">
                <span style="background: #10b981; color: #ffffff; font-size: 11px; font-weight: 900; padding: 3px 10px; border-radius: 8px;">LANGKAH 3 (MALAM)</span>
                <h4 style="font-size: 16px; font-weight: 800; color: #065f46; margin: 10px 0 6px 0;">Tutup Shift & Audit Uang Laci</h4>
                <p style="font-size: 13px; color: #15803d; line-height: 1.5; margin: 0;">
                    Buka kembali <strong>Riwayat Shift & Absen</strong>. Hitung uang fisik nyata di laci, ketik angkanya pada kolom merah <em>Uang Akhir Tutup Laci</em>, lalu klik <strong>🔒 Tutup Shift</strong>.
                </p>
            </div>
        </div>
    </div>

    {{-- 3. PANDUAN KHUSUS MASTER ADMIN --}}
    @if($isMasterOwner)
        <div style="background: #ffffff; border-radius: 16px; border: 2px solid #3b82f6; padding: 24px; box-shadow: 0 4px 14px rgba(0,0,0,0.02);">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
                <span style="background: #3b82f6; color: #ffffff; font-size: 18px; padding: 6px 12px; border-radius: 12px;">👑</span>
                <div>
                    <h3 style="font-size: 20px; font-weight: 900; color: #1e3a8a; margin: 0;">
                        Panduan Khusus Pemilik Toko (*Master Admin / Owner*)
                    </h3>
                    <span style="font-size: 13px; color: #2563eb; font-weight: 600;">Hak akses eksekutif untuk kendali penuh dan monitoring cabang</span>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 18px; border-radius: 14px;">
                    <h4 style="font-size: 15px; font-weight: 800; color: #1e40af; margin: 0 0 6px 0;">📊 Beranda & Konsolidasi Omzet</h4>
                    <p style="font-size: 13px; color: #1d4ed8; line-height: 1.5; margin: 0;">
                        Pantau grafik penjualan gabungan 3 toko sekaligus atau gunakan filter cabang di atas tabel untuk melihat performa spesifik per toko.
                    </p>
                </div>

                <div style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 18px; border-radius: 14px;">
                    <h4 style="font-size: 15px; font-weight: 800; color: #1e40af; margin: 0 0 6px 0;">📦 Katalog 11.792 Produk & Stok</h4>
                    <p style="font-size: 13px; color: #1d4ed8; line-height: 1.5; margin: 0;">
                        Kelola barang di menu <strong>Daftar Barang & Stok</strong>. Pisahkan barang siap jual di tab <em>Aktif</em> dan barang berhenti jual di tab <em>Nonaktif</em>.
                    </p>
                </div>

                <div style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 18px; border-radius: 14px;">
                    <h4 style="font-size: 15px; font-weight: 800; color: #1e40af; margin: 0 0 6px 0;">📑 Pusat Laporan Eksekutif</h4>
                    <p style="font-size: 13px; color: #1d4ed8; line-height: 1.5; margin: 0;">
                        Tarik rekapitulasi penjualan per menu, per cabang, per metode bayar (Tunai/QRIS), dan analisis laba rugi di menu <strong>Laporan</strong>.
                    </p>
                </div>

                <div style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 18px; border-radius: 14px;">
                    <h4 style="font-size: 15px; font-weight: 800; color: #1e40af; margin: 0 0 6px 0;">🔒 Log Aktivitas & Audit (Rahasia)</h4>
                    <p style="font-size: 13px; color: #1d4ed8; line-height: 1.5; margin: 0;">
                        Menu <strong>Log Aktivitas & Audit</strong> hanya muncul untuk Anda. Pantau siapa yang mengubah harga, menghapus barang, atau jam absen kasir.
                    </p>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
