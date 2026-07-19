<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Portal Kerja Kasir - MULIKU STORE</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        html, body {
            width: 100%;
            max-width: 100vw;
            overflow-x: hidden;
            background-color: #F1F5F9;
            color: #0F172A;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            user-select: none;
        }
        .header-bar {
            background: linear-gradient(135deg, #1565C0 0%, #1E88E5 100%);
            min-height: 56px;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(21, 101, 192, 0.22);
            flex-shrink: 0;
            width: 100%;
        }
        .logo-box {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #ffffff;
            color: #1565C0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 16px;
            flex-shrink: 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 28px 20px;
            width: 100%;
        }
        .portal-container {
            width: 100%;
            max-width: 1040px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .portal-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            width: 100%;
        }
        @media (max-width: 768px) {
            .portal-grid {
                grid-template-columns: 1fr;
            }
        }
        .portal-card {
            background: #ffffff;
            border-radius: 18px;
            border: 2px solid #E2E8F0;
            box-shadow: 0 8px 20px rgba(0,0,0,0.04);
            transition: all 0.2s ease;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-decoration: none;
            color: inherit;
            width: 100%;
            cursor: pointer;
        }
        .portal-card:hover {
            transform: translateY(-4px);
            border-color: #1E88E5;
            box-shadow: 0 16px 30px rgba(30, 136, 229, 0.14);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            width: 100%;
        }
        @media (max-width: 680px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
        .stat-box {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #CBD5E1;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }
        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #EFF6FF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        /* MODAL OVERLAY */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }
        .modal-box {
            background: #ffffff;
            border-radius: 20px;
            width: 100%;
            max-width: 480px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3);
            animation: modalFadeIn 0.2s ease-out;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>

    @php
        $hariIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][now()->dayOfWeek];
        $bulanIndo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][now()->month - 1];
        $tanggalIndo = $hariIndo . ', ' . now()->day . ' ' . $bulanIndo . ' ' . now()->year;
    @endphp

    {{-- HEADER BIRU KONSISTEN MULIKU STORE --}}
    <header class="header-bar">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div class="logo-box">MS</div>
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <h1 style="font-size: 15px; font-weight: 900; letter-spacing: 0.3px;">MULIKU STORE</h1>
                    <span style="background: #34D399; color: #064E3B; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 4px;">PORTAL CABANG</span>
                </div>
                <span style="font-size: 11px; color: #DBEAFE; font-weight: 500;">Sistem Kasir Toko & Pengelolaan Barang</span>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 10px; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25); font-size: 12px; font-weight: 800;">
                <span>🏪 {{ auth()->user()->outlet->name ?? 'Muliku Store' }}</span>
            </div>

            <div style="display: flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 10px; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25); font-size: 12px; font-weight: 800;">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #34D399;"></span>
                <span>{{ $activeShift->cashier_name ?? auth()->user()->name ?? 'Kasir' }}</span>
            </div>

            @if($activeShift)
                <button type="button" onclick="alertCannotLogoutOpenShift()" style="padding: 7px 14px; border-radius: 8px; background: #FFF1F2; color: #E11D48; border: 1px solid #FECDD3; font-size: 12px; font-weight: 800; cursor: pointer; transition: 0.2s;">
                    🔒 Keluar
                </button>
            @else
                <form method="POST" action="{{ route('filament.admin.auth.logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" style="padding: 7px 14px; border-radius: 8px; background: #EF4444; color: #ffffff; border: none; font-size: 12px; font-weight: 800; cursor: pointer; transition: 0.2s;">
                        Keluar
                    </button>
                </form>
            @endif
        </div>
    </header>

    {{-- KONTEN UTAMA PORTAL YANG 100% INDONESIA & SIAP ABSEN SHIFT --}}
    <main class="main-wrapper">
        <div class="portal-container">
            
            {{-- HEADER SAMBUTAN & STATUS MESIN KASIR --}}
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; background: #ffffff; padding: 18px 22px; border-radius: 16px; border: 1px solid #CBD5E1; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="padding: 3px 10px; border-radius: 12px; background: #E0F2FE; color: #0369A1; font-size: 10px; font-weight: 800; text-transform: uppercase;">
                            Pusat Kerja Toko
                        </span>
                        <span style="font-size: 12px; color: #64748B; font-weight: 600;">
                            &bull; {{ $tanggalIndo }}
                        </span>
                    </div>
                    <h2 style="font-size: 22px; font-weight: 900; color: #0F172A; margin-top: 4px;">
                        Selamat Datang, {{ $activeShift->cashier_name ?? auth()->user()->name ?? 'Staf Kasir' }}!
                    </h2>
                </div>

                {{-- STATUS SHIFT KASIR & RIWAYAT ABSEN --}}
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    @if($activeShift)
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 800; color: #059669; background: #ECFDF5; padding: 8px 14px; border-radius: 10px; border: 1px solid #A7F3D0;">
                            <span>🟢 Mesin Kasir Buka &bull; Modal Rp {{ number_format($activeShift->initial_cash, 0, ',', '.') }}</span>
                        </div>
                    @else
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 800; color: #D97706; background: #FEF3C7; padding: 8px 14px; border-radius: 10px; border: 1px solid #FDE68A;">
                            <span>🔒 Mesin Kasir Tutup (Belum Absen Masuk)</span>
                        </div>
                    @endif

                    <button
                        type="button"
                        onclick="openRiwayatShiftModal()"
                        style="padding: 8px 14px; border-radius: 10px; background: #EFF6FF; color: #1976D2; font-size: 12px; font-weight: 800; border: 1px solid #BFDBFE; cursor: pointer;"
                    >
                        📋 Riwayat Shift & Tutup Kasir
                    </button>
                </div>
            </div>

            {{-- 2 PILIHAN KARTU MODE KERJA UTAMA --}}
            <div class="portal-grid">
                
                {{-- KARTU 1: MESIN KASIR (POS TUNAI) --}}
                @if($activeShift)
                    {{-- Jika Sudah Buka Shift -> Langsung Masuk POS --}}
                    <a href="{{ url('/admin/pos-kasir') }}" class="portal-card">
                        <div style="height: 105px; background: linear-gradient(135deg, #1565C0 0%, #1E88E5 100%); padding: 22px; display: flex; align-items: center; justify-content: space-between; color: #ffffff;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 10.5px; font-weight: 800; opacity: 0.9; text-transform: uppercase;">TRANSAKSI PENJUALAN</span>
                                    <span style="background: #34D399; color: #064E3B; font-size: 9.5px; font-weight: 800; padding: 2px 8px; border-radius: 4px;">SIAP JUAL</span>
                                </div>
                                <h3 style="font-size: 21px; font-weight: 900; margin-top: 4px;">MESIN KASIR TOKO</h3>
                            </div>
                            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; font-size: 26px;">
                                🖥️
                            </div>
                        </div>

                        <div style="padding: 22px; display: flex; flex-direction: column; justify-content: space-between; flex: 1;">
                            <p style="font-size: 13.5px; color: #475569; line-height: 1.55;">
                                Buka layar kasir penuh tanpa gangguan untuk melayani transaksi pembeli tunai langsung di toko. Dilengkapi cari barang cepat, scan barcode, & hitung kembalian otomatis.
                            </p>

                            <div style="margin-top: 24px; padding: 13px 18px; border-radius: 12px; background: #1976D2; color: #ffffff; font-weight: 800; font-size: 13.5px; display: flex; align-items: center; justify-content: space-between;">
                                <span>MASUK KE LAYAR TRANSAKSI</span>
                                <span style="font-size: 17px;">&rarr;</span>
                            </div>
                        </div>
                    </a>
                @else
                    {{-- Jika Belum Buka Shift -> Klik Munculkan Pop-Up Absen & Modal Awal ala iSeller --}}
                    <div onclick="openShiftModal()" class="portal-card">
                        <div style="height: 105px; background: linear-gradient(135deg, #1565C0 0%, #1E88E5 100%); padding: 22px; display: flex; align-items: center; justify-content: space-between; color: #ffffff;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 10.5px; font-weight: 800; opacity: 0.9; text-transform: uppercase;">TRANSAKSI PENJUALAN</span>
                                    <span style="background: #FEE2E2; color: #991B1B; font-size: 9.5px; font-weight: 800; padding: 2px 8px; border-radius: 4px;">BELUM BUKA SHIFT</span>
                                </div>
                                <h3 style="font-size: 21px; font-weight: 900; margin-top: 4px;">MESIN KASIR TOKO</h3>
                            </div>
                            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; font-size: 26px;">
                                🖥️
                            </div>
                        </div>

                        <div style="padding: 22px; display: flex; flex-direction: column; justify-content: space-between; flex: 1;">
                            <p style="font-size: 13.5px; color: #475569; line-height: 1.55;">
                                Mesin kasir saat ini masih tutup. Tekan tombol di bawah untuk <b>melakukan absen masuk</b> dan memasukkan <b>uang modal awal laci hari ini</b> sebelum memulai pelayanan.
                            </p>

                            <div style="margin-top: 24px; padding: 13px 18px; border-radius: 12px; background: #1976D2; color: #ffffff; font-weight: 800; font-size: 13.5px; display: flex; align-items: center; justify-content: space-between;">
                                <span>BUKA MESIN KASIR & ABSEN MASUK</span>
                                <span style="font-size: 17px;">&rarr;</span>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- KARTU 2: KELOLA PRODUK & STOK CABANG (TAMPILAN ADMIN FILAMENT RESMI YANG DIRAPIHKAN) --}}
                @if($activeShift)
                    <a href="{{ url('/admin/products') }}" class="portal-card">
                        <div style="height: 105px; background: linear-gradient(135deg, #0284C7 0%, #0EA5E9 100%); padding: 22px; display: flex; align-items: center; justify-content: space-between; color: #ffffff;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 10.5px; font-weight: 800; opacity: 0.9; text-transform: uppercase;">PENGELOLAAN BARANG</span>
                                    <span style="background: rgba(255,255,255,0.25); color: #ffffff; font-size: 9.5px; font-weight: 800; padding: 2px 8px; border-radius: 4px;">STOK TOKO</span>
                                </div>
                                <h3 style="font-size: 21px; font-weight: 900; margin-top: 4px;">KATALOG & STOK CABANG</h3>
                            </div>
                            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; font-size: 26px;">
                                📦
                            </div>
                        </div>

                        <div style="padding: 22px; display: flex; flex-direction: column; justify-content: space-between; flex: 1;">
                            <p style="font-size: 13.5px; color: #475569; line-height: 1.55;">
                                Kelola daftar produk, periksa ketersediaan fisik barang di cabang toko Anda, cek harga jual barang, serta pantau varian dan barcode barang dengan mudah.
                            </p>

                            <div style="margin-top: 24px; padding: 13px 18px; border-radius: 12px; background: #0284C7; color: #ffffff; font-weight: 800; font-size: 13.5px; display: flex; align-items: center; justify-content: space-between;">
                                <span>KELOLA KATALOG & STOK BARANG</span>
                                <span style="font-size: 17px;">&rarr;</span>
                            </div>
                        </div>
                    </a>
                @else
                    <div onclick="openShiftModal()" class="portal-card">
                        <div style="height: 105px; background: linear-gradient(135deg, #0284C7 0%, #0EA5E9 100%); padding: 22px; display: flex; align-items: center; justify-content: space-between; color: #ffffff;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 10.5px; font-weight: 800; opacity: 0.9; text-transform: uppercase;">PENGELOLAAN BARANG</span>
                                    <span style="background: #FEE2E2; color: #991B1B; font-size: 9.5px; font-weight: 800; padding: 2px 8px; border-radius: 4px;">BELUM BUKA SHIFT</span>
                                </div>
                                <h3 style="font-size: 21px; font-weight: 900; margin-top: 4px;">KATALOG & STOK CABANG</h3>
                            </div>
                            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; font-size: 26px;">
                                📦
                            </div>
                        </div>

                        <div style="padding: 22px; display: flex; flex-direction: column; justify-content: space-between; flex: 1;">
                            <p style="font-size: 13.5px; color: #475569; line-height: 1.55;">
                                Mesin kasir saat ini masih tutup. Tekan tombol di bawah untuk <b>melakukan absen masuk</b> terlebih dahulu sebelum dapat mengelola stok & katalog toko.
                            </p>

                            <div style="margin-top: 24px; padding: 13px 18px; border-radius: 12px; background: #0284C7; color: #ffffff; font-weight: 800; font-size: 13.5px; display: flex; align-items: center; justify-content: space-between;">
                                <span>BUKA MESIN KASIR & ABSEN MASUK</span>
                                <span style="font-size: 17px;">&rarr;</span>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            {{-- 3 KOLOM RINGKASAN CEPAT OPERASIONAL TOKO (100% INDONESIA) --}}
            <div class="stats-grid">
                
                {{-- Box 1 --}}
                <div class="stat-box">
                    <div class="stat-icon" style="background: #EFF6FF; color: #1E88E5;">
                        🏪
                    </div>
                    <div>
                        <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block;">Cabang Toko Bertugas</span>
                        <h4 style="font-size: 14px; font-weight: 900; color: #0F172A; margin-top: 2px;">
                            {{ auth()->user()->outlet->name ?? 'Muliku Store' }}
                        </h4>
                        <span style="font-size: 11px; color: #0284C7; font-weight: 600;">Aktif Terhubung Cabang</span>
                    </div>
                </div>

                {{-- Box 2 --}}
                <div class="stat-box">
                    <div class="stat-icon" style="background: #ECFDF5; color: #059669;">
                        📦
                    </div>
                    <div>
                        <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block;">Daftar Barang Aktif</span>
                        <h4 style="font-size: 14px; font-weight: 900; color: #0F172A; margin-top: 2px;">
                            {{ \App\Models\Product::count() }} Jenis Barang
                        </h4>
                        <span style="font-size: 11px; color: #059669; font-weight: 600;">Siap Dijual di Kasir</span>
                    </div>
                </div>

                {{-- Box 3 --}}
                <div class="stat-box">
                    <div class="stat-icon" style="background: #FEF3C7; color: #D97706;">
                        ⚡
                    </div>
                    <div>
                        <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block;">Peran Staf Toko</span>
                        <h4 style="font-size: 14px; font-weight: 900; color: #0F172A; margin-top: 2px;">
                            Staf Kasir Toko
                        </h4>
                        <span style="font-size: 11px; color: #D97706; font-weight: 600;">Hak Akses Kasir Penuh</span>
                    </div>
                </div>

            </div>

            {{-- PANDUAN CEPAT KASIR (BAHASA INDONESIA SEHARI-HARI) --}}
            <div style="background: #ffffff; border-radius: 14px; border: 1px solid #CBD5E1; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; font-size: 12px; color: #475569;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-weight: 900; color: #1E88E5;">💡 TIPS KASIR:</span>
                    <span>Ketik nama barang atau scan kode barcode di Mesin Kasir untuk melayani pembeli lebih cepat.</span>
                </div>
                <span style="font-size: 11px; color: #94A3B8; font-weight: 700;">MULIKU STORE POS v2.0</span>
            </div>

        </div>
    </main>

    {{-- MODAL BUKA MESIN KASIR & ABSEN SHIFT --}}
    <div id="modalBukaShift" class="modal-overlay">
        <div class="modal-box">
            
            <div style="background: linear-gradient(135deg, #1565C0 0%, #1E88E5 100%); padding: 22px; color: #ffffff;">
                <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 4px;">
                    ABSEN MASUK & BUKA SHIFT
                </span>
                <h3 style="font-size: 18px; font-weight: 900; margin: 6px 0 0 0;">Buka Mesin Kasir Hari Ini</h3>
                <p style="font-size: 12px; opacity: 0.9; margin-top: 4px;">
                    Silakan isi nama kasir yang bertugas dan jumlah modal awal laci hari ini.
                </p>
            </div>

            <form method="POST" action="{{ route('buka-shift') }}" style="padding: 22px; display: flex; flex-direction: column; gap: 16px;">
                @csrf
                
                <div>
                    <label style="font-size: 12px; font-weight: 800; color: #334155; display: block; margin-bottom: 6px;">
                        👤 Nama Kasir Bertugas Hari Ini <span style="color: #E11D48;">*</span>
                    </label>
                    <input
                        type="text"
                        id="inputCashierName"
                        name="cashier_name"
                        value=""
                        placeholder="Contoh: Siti / Budi / Rina (Ketik nama Anda)..."
                        required
                        style="width: 100%; padding: 12px 14px; border-radius: 10px; border: 2px solid #CBD5E1; font-size: 14.5px; font-weight: 700; color: #0F172A; outline: none;"
                    />
                </div>

                <div>
                    <label style="font-size: 12px; font-weight: 800; color: #334155; display: block; margin-bottom: 6px;">
                        💵 Uang Hari Ini / Modal Awal Laci (Rp) <span style="color: #E11D48;">*</span>
                    </label>
                    <input
                        type="number"
                        id="inputModalCash"
                        name="initial_cash"
                        value="500000"
                        required
                        style="width: 100%; padding: 12px 14px; border-radius: 10px; border: 2px solid #CBD5E1; font-size: 16px; font-weight: 800; color: #0F172A; outline: none;"
                    />
                </div>

                <div>
                    <label style="font-size: 11px; font-weight: 700; color: #64748B; display: block; margin-bottom: 6px;">
                        Pilihan Modal Cepat:
                    </label>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                        <button type="button" onclick="setModalCash(200000)" style="padding: 8px; border-radius: 8px; border: 1px solid #CBD5E1; background: #F8FAFC; font-size: 12px; font-weight: 800; color: #334155; cursor: pointer;">
                            Rp 200.000
                        </button>
                        <button type="button" onclick="setModalCash(500000)" style="padding: 8px; border-radius: 8px; border: 1px solid #CBD5E1; background: #F8FAFC; font-size: 12px; font-weight: 800; color: #334155; cursor: pointer;">
                            Rp 500.000
                        </button>
                        <button type="button" onclick="setModalCash(1000000)" style="padding: 8px; border-radius: 8px; border: 1px solid #CBD5E1; background: #F8FAFC; font-size: 12px; font-weight: 800; color: #334155; cursor: pointer;">
                            Rp 1.000.000
                        </button>
                    </div>
                </div>

                      <div style="padding-top: 10px; border-top: 1px solid #E2E8F0; display: flex; gap: 10px;">
                    @if($activeShift && request('auto_open_shift') != '1')
                        <button
                            type="button"
                            onclick="closeShiftModal()"
                            style="flex: 1; padding: 12px; border-radius: 10px; background: #E2E8F0; color: #334155; font-size: 13px; font-weight: 800; border: none; cursor: pointer;"
                        >
                            Batal
                        </button>
                    @else
                        @if($activeShift)
                            <button
                                type="button"
                                onclick="alertCannotLogoutOpenShift()"
                                style="flex: 1; padding: 12px; border-radius: 10px; background: #FFF1F2; color: #E11D48; font-size: 13px; font-weight: 800; border: 1px solid #FECDD3; cursor: pointer;"
                            >
                                Keluar dari Akun
                            </button>
                        @else
                            <button
                                type="button"
                                onclick="document.getElementById('logoutFormModal').submit()"
                                style="flex: 1; padding: 12px; border-radius: 10px; background: #FFF1F2; color: #E11D48; font-size: 13px; font-weight: 800; border: 1px solid #FECDD3; cursor: pointer;"
                            >
                                Keluar dari Akun
                            </button>
                        @endif
                    @endif
                    <button
                        type="submit"
                        style="flex: 2; padding: 12px; border-radius: 10px; background: linear-gradient(135deg, #1565C0 0%, #1E88E5 100%); color: #ffffff; font-size: 13px; font-weight: 900; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);"
                    >
                        ✓ BUKA MESIN KASIR & MULAI SHIFT
                    </button>
                </div>

            </form>
            <form id="logoutFormModal" method="POST" action="{{ route('filament.admin.auth.logout') }}" style="display: none;">@csrf</form>

        </div>
    </div>

    {{-- MODAL RIWAYAT & TUTUP SHIFT (ALA REFERENSI REGISTER SHIFTS iSELLER) --}}
    <div id="modalRiwayatShift" class="modal-overlay">
        <div class="modal-box">
            <div style="background: linear-gradient(135deg, #1565C0 0%, #1E88E5 100%); padding: 22px; color: #ffffff; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 4px;">
                        REGISTER SHIFTS
                    </span>
                    <h3 style="font-size: 18px; font-weight: 900; margin: 6px 0 0 0;">Riwayat Shift Kasir Toko</h3>
                </div>
                <button type="button" onclick="closeRiwayatShiftModal()" style="background: transparent; border: none; color: #ffffff; font-size: 20px; font-weight: bold; cursor: pointer;">✕</button>
            </div>

            <div style="padding: 22px;">
                @if($activeShift)
                    @php
                        $shiftSales = 0;
                        $shiftOrdersCount = 0;
                        $ordersQuery = \App\Models\Order::where('payment_status', 'paid')
                            ->where('created_at', '>=', $activeShift->opened_at);
                            
                        if ($activeShift->outlet_id) {
                            $ordersQuery->where('outlet_id', $activeShift->outlet_id);
                        } else {
                            $ordersQuery->where('cashier_id', auth()->id());
                        }
                        
                        $shiftSales = (clone $ordersQuery)->sum('total_amount');
                        $shiftOrdersCount = (clone $ordersQuery)->count();
                        $expectedClosingCash = ($activeShift->initial_cash ?? 0) + $shiftSales;
                    @endphp

                    <div style="background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 14px; padding: 16px; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                <span style="font-weight: 900; font-size: 15px; color: #0F172A;">Hari Ini ({{ $tanggalIndo }})</span>
                                <span style="background: #1976D2; color: #ffffff; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 6px;">Buka</span>
                            </div>
                            <span style="font-size: 12px; color: #64748B;">Absen Masuk Pukul {{ $activeShift->opened_at->format('H:i') }} WIB</span>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 11px; color: #64748B; display: block;">Modal Awal</span>
                            <span style="font-weight: 900; font-size: 15px; color: #0F172A;">Rp {{ number_format($activeShift->initial_cash, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- BOX KOTAK RINCIAN OTOMATIS SISTEM --}}
                    <div style="background: #EFF6FF; border: 1.5px dashed #3B82F6; border-radius: 14px; padding: 16px; margin-bottom: 18px;">
                        <div style="font-size: 11px; font-weight: 900; color: #1E40AF; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between;">
                            <span>📊 Pembacaan Otomatis Sistem (Shift Ini)</span>
                            <span style="background: #DBEAFE; color: #1D4ED8; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 800;">{{ $shiftOrdersCount }} Pesanan Terjual</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 13px; color: #334155; margin-bottom: 6px;">
                            <span>💵 Modal Awal Laci:</span>
                            <span style="font-weight: 800;">Rp {{ number_format($activeShift->initial_cash, 0, ',', '.') }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 13px; color: #16A34A; margin-bottom: 10px;">
                            <span>🛍️ Total Penjualan Shift Ini:</span>
                            <span style="font-weight: 900;">+ Rp {{ number_format($shiftSales, 0, ',', '.') }}</span>
                        </div>
                        <div style="border-top: 1px solid #BFDBFE; padding-top: 10px; display: flex; justify-content: space-between; font-size: 14px; color: #1E3A8A;">
                            <span style="font-weight: 800;">Estimasi Total Uang Fisik Laci:</span>
                            <span style="font-weight: 900; font-size: 16px; color: #1D4ED8;">Rp {{ number_format($expectedClosingCash, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('tutup-shift') }}" style="display: flex; flex-direction: column; gap: 14px;">
                        @csrf
                        <div>
                            <label style="font-size: 12px; font-weight: 800; color: #334155; display: block; margin-bottom: 6px;">
                                Uang Akhir Saat Tutup Kasir (Rp) <span style="color: #2563EB; font-weight: 700;">(Diisi Otomatis oleh Sistem)</span>
                            </label>
                            <input
                                type="number"
                                id="inputClosingCash"
                                name="closing_cash"
                                value="{{ (int) $expectedClosingCash }}"
                                required
                                style="width: 100%; padding: 12px 14px; border-radius: 10px; border: 2px solid #3B82F6; background: #FAFAFA; font-size: 16px; font-weight: 800; color: #0F172A; outline: none;"
                            />
                            <div
                                onclick="document.getElementById('inputClosingCash').value = {{ (int) $expectedClosingCash }}"
                                style="font-size: 11px; color: #2563EB; margin-top: 6px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;"
                            >
                                ⚡ Klik di sini jika ingin mengembalikan ke nilai otomatis sistem (Rp {{ number_format($expectedClosingCash, 0, ',', '.') }})
                            </div>
                        </div>
                        <button
                            type="submit"
                            style="padding: 13px; border-radius: 12px; background: #EF4444; color: #ffffff; font-size: 13.5px; font-weight: 900; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);"
                        >
                            🔒 Tutup Shift & Akhiri Tugas Kasir
                        </button>
                    </form>
                @else
                    <div style="text-align: center; padding: 28px 10px;">
                        <div style="font-size: 36px; margin-bottom: 10px;">🔒</div>
                        <h4 style="font-size: 15px; font-weight: 800; color: #334155;">Belum Ada Shift Terbuka Hari Ini</h4>
                        <p style="font-size: 12px; color: #64748B; margin: 6px 0 16px 0;">Tekan tombol di bawah untuk absen masuk dan membuka mesin kasir baru.</p>
                        <button
                            type="button"
                            onclick="closeRiwayatShiftModal(); openShiftModal();"
                            style="padding: 10px 18px; border-radius: 10px; background: #1976D2; color: #ffffff; font-weight: 800; font-size: 13px; border: none; cursor: pointer;"
                        >
                            + Buka Shift Kasir Sekarang
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- MODAL EXECUTIVE 1: BLOKIR KELUAR AKUN SEBELUM TUTUP SHIFT --}}
    <div id="modalBlockedLogout" class="modal-overlay" style="z-index: 100000;">
        <div class="modal-box" style="max-width: 480px; text-align: center; border: 2px solid #FECDD3; overflow: hidden; padding: 0;">
            <div style="background: linear-gradient(135deg, #E11D48 0%, #BE123C 100%); padding: 28px 22px; color: #ffffff;">
                <div style="font-size: 48px; margin-bottom: 8px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2));">🛡️</div>
                <span style="font-size: 10px; font-weight: 900; text-transform: uppercase; background: rgba(255,255,255,0.22); padding: 3px 10px; border-radius: 6px; letter-spacing: 0.6px;">
                    KEAMANAN SHIFT KASIR AKTIF
                </span>
                <h3 style="font-size: 21px; font-weight: 900; margin: 12px 0 4px 0;">Tidak Dapat Keluar dari Akun!</h3>
                <p style="font-size: 13px; opacity: 0.95; margin: 0; line-height: 1.4;">
                    Shift Kasir atas nama <strong style="color: #FFE4E6; text-decoration: underline;">{{ $activeShift->cashier_name ?? auth()->user()->name ?? 'Kasir' }}</strong> saat ini masih <strong>TERBUKA</strong>.
                </p>
            </div>

            <div style="padding: 22px 24px; text-align: left; background: #FFF1F2; border-bottom: 1px solid #FFE4E6;">
                <p style="font-size: 13px; font-weight: 800; color: #881337; margin: 0 0 10px 0; display: flex; align-items: center; gap: 6px;">
                    <span>⚠️</span> Mengapa Anda harus menutup shift terlebih dahulu?
                </p>
                <ul style="font-size: 12.5px; color: #9F1239; margin: 0; padding-left: 18px; line-height: 1.6;">
                    <li><strong>Rekap Uang Laci Kasir:</strong> Memastikan uang tunai fisik di laci cocok dengan total transaksi sistem.</li>
                    <li><strong>Mencegah Selisih Kas:</strong> Menghindari kebingungan saldo saat pergantian shift kasir berikutnya.</li>
                    <li><strong>Laporan Otomatis:</strong> Seluruh catatan penjualan shift Anda akan tersimpan resmi di Riwayat Shift.</li>
                </ul>
            </div>

            <div style="padding: 20px 24px; display: flex; flex-direction: column; gap: 10px; background: #ffffff;">
                <button
                    type="button"
                    onclick="closeBlockedLogoutModal(); openRiwayatShiftModal();"
                    style="width: 100%; padding: 14px; border-radius: 12px; background: linear-gradient(135deg, #1565C0 0%, #1E88E5 100%); color: #ffffff; font-size: 13.5px; font-weight: 900; border: none; cursor: pointer; box-shadow: 0 4px 14px rgba(21, 101, 192, 0.35); transition: 0.2s;"
                >
                    📋 Buka Menu Tutup Shift & Rekap Kasir Sekarang
                </button>
                <button
                    type="button"
                    onclick="closeBlockedLogoutModal()"
                    style="width: 100%; padding: 12px; border-radius: 10px; background: #F1F5F9; color: #475569; font-size: 13px; font-weight: 800; border: none; cursor: pointer; transition: 0.2s;"
                >
                    Kembali ke Portal Kasir
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL EXECUTIVE 2: WAJIB BUKA SHIFT SEBELUM AKSES --}}
    <div id="modalRequiredShift" class="modal-overlay" style="z-index: 100000;">
        <div class="modal-box" style="max-width: 440px; text-align: center; border: 2px solid #FDE68A; overflow: hidden; padding: 0;">
            <div style="background: linear-gradient(135deg, #D97706 0%, #B45309 100%); padding: 26px 22px; color: #ffffff;">
                <div style="font-size: 46px; margin-bottom: 8px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2));">🔑</div>
                <h3 style="font-size: 20px; font-weight: 900; margin: 0;">Buka Shift Kasir Diperlukan!</h3>
                <p style="font-size: 13px; opacity: 0.95; margin-top: 6px; line-height: 1.4;">
                    Anda wajib mengisi Nama Kasir dan Modal Awal Laci terlebih dahulu sebelum dapat menggunakan menu transaksi.
                </p>
            </div>
            <div style="padding: 22px; background: #ffffff;">
                <button
                    type="button"
                    onclick="document.getElementById('modalRequiredShift').style.display='none'; openShiftModal();"
                    style="width: 100%; padding: 13px; border-radius: 12px; background: linear-gradient(135deg, #1565C0 0%, #1E88E5 100%); color: #ffffff; font-size: 13.5px; font-weight: 900; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(21, 101, 192, 0.3);"
                >
                    Mengerti & Isi Modal Awal Sekarang
                </button>
            </div>
        </div>
    </div>

    {{-- FOOTER SERAGAM --}}
    <footer style="padding: 14px; text-align: center; font-size: 11px; color: #64748B; border-top: 1px solid #E2E8F0; background: #ffffff;">
        &copy; {{ date('Y') }} MULIKU STORE &bull; Sistem Kasir Toko & Pengelolaan Barang
    </footer>

    <script>
        function openShiftModal() {
            document.getElementById('modalBukaShift').style.display = 'flex';
            setTimeout(() => {
                const cashierInput = document.getElementById('inputCashierName');
                if (cashierInput) {
                    cashierInput.focus();
                    cashierInput.select();
                } else {
                    document.getElementById('inputModalCash').focus();
                }
            }, 100);
        }
        function closeShiftModal() {
            @if(!$activeShift || request('auto_open_shift') == '1')
                document.getElementById('modalRequiredShift').style.display = 'flex';
                return;
            @endif
            document.getElementById('modalBukaShift').style.display = 'none';
        }
        function setModalCash(val) {
            document.getElementById('inputModalCash').value = val;
        }

        function openRiwayatShiftModal() {
            document.getElementById('modalRiwayatShift').style.display = 'flex';
        }
        function closeRiwayatShiftModal() {
            document.getElementById('modalRiwayatShift').style.display = 'none';
        }

        function alertCannotLogoutOpenShift() {
            document.getElementById('modalBlockedLogout').style.display = 'flex';
        }
        function closeBlockedLogoutModal() {
            document.getElementById('modalBlockedLogout').style.display = 'none';
        }

        @if(request('auto_open_shift') == '1' || !$activeShift)
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(openShiftModal, 250);
                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            });
        @endif

        @if(request('blocked_logout') == '1' || session('error'))
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(alertCannotLogoutOpenShift, 300);
                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            });
        @endif
    </script>

</body>
</html>
