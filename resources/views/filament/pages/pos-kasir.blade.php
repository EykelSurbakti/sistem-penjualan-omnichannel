<x-filament-panels::page>
    <style>
        /* Sembunyikan seluruh elemen navigasi bawaan Filament */
        aside.fi-sidebar, header.fi-topbar, header.fi-header, .fi-user-menu {
            display: none !important;
        }
        body {
            overflow: hidden !important;
        }
        .custom-scroll::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        /* Penyesuaian Otomatis (Auto-Adjust) untuk Tab Advan A8 dan Tablet Android */
        @media (max-width: 900px) {
            .pos-cart-aside {
                width: 320px !important;
                max-width: 44vw !important;
            }
        }
        @media (max-width: 650px) {
            /* Jika tablet diputar potret (vertikal) atau di HP */
            .pos-main-flex {
                flex-direction: column !important;
            }
            .pos-cart-aside {
                width: 100% !important;
                max-width: 100% !important;
                height: 46vh !important;
                border-left: none !important;
                border-top: 2px solid #CBD5E1 !important;
            }
        }
        /* PENGATURAN CETAK STRUK THERMAL 58MM KHUSUS PRINTER IWARE C58BT / BLUETOOTH / USB */
        @media print {
            /* Gunakan visibility (BUKAN display:none) agar nested child tetap bisa terlihat */
            body * {
                visibility: hidden !important;
            }
            /* Tampilkan struk dan semua isinya */
            #thermal-receipt,
            #thermal-receipt * {
                visibility: visible !important;
            }
            /* Posisikan struk di pojok kiri atas kertas 58mm */
            #thermal-receipt {
                display: block !important;
                position: fixed !important;
                left: 0 !important;
                top: 0 !important;
                width: 58mm !important;
                max-width: 58mm !important;
                margin: 0 !important;
                padding: 3mm 4mm 6mm 4mm !important;
                box-sizing: border-box !important;
                font-family: 'Courier New', Courier, monospace !important;
                font-size: 11px !important;
                line-height: 1.4 !important;
                color: #000000 !important;
                background: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            @page {
                size: 58mm auto;
                margin: 0mm;
            }
        }
    </style>

    {{-- LAYOUT UTAMA KASIR: POSITION FIXED FULL VIEWPORT DENGAN 100dvh AGAR PAS DI TAB ADVAN A8 (TIDAK TERPOTONG NAV BAR ANDROID) --}}
    <div style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; z-index: 40; width: 100vw; height: 100dvh; max-height: 100%; display: flex; flex-direction: column; background: #F1F5F9; color: #0F172A; font-family: sans-serif; user-select: none; overflow: hidden;">
        
        {{-- ========================================================
             1. NAVBAR SUPER COMPACT & SLEEK (48px)
             ======================================================== --}}
        <header style="background: linear-gradient(135deg, #1565C0 0%, #1E88E5 100%); height: 48px; padding: 0 16px; display: flex; align-items: center; justify-content: space-between; gap: 10px; color: #ffffff; box-shadow: 0 2px 5px rgba(0,0,0,0.12); flex-shrink: 0;">
            
            {{-- Kiri: Tombol Kembali & Nama Toko --}}
            <div style="display: flex; align-items: center; gap: 10px; flex-shrink: 0;">
                <a href="{{ url('/portal-kasir') }}"
                   title="Kembali ke Menu Awal"
                   style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 6px; background: rgba(255,255,255,0.18); color: #ffffff; font-weight: 900; font-size: 13px; text-decoration: none; border: 1px solid rgba(255,255,255,0.25);">
                    ←
                </a>

                <div style="display: flex; align-items: center; gap: 6px;">
                    <h1 style="font-size: 14px; font-weight: 900; margin: 0; color: #ffffff; white-space: nowrap; letter-spacing: 0.3px;">
                        {{ auth()->user()->outlet->name ?? 'Muliku Store' }}
                    </h1>
                    <span style="background: #34D399; color: #064E3B; font-size: 9.5px; font-weight: 800; padding: 1px 6px; border-radius: 4px; text-transform: uppercase;">
                        POS
                    </span>
                </div>
            </div>

            {{-- Kanan: Badge Kasir --}}
            <div style="display: flex; align-items: center; flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 6px; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25);">
                    <span style="width: 7px; height: 7px; border-radius: 50%; background: #34D399; box-shadow: 0 0 6px #34D399;"></span>
                    <span style="font-size: 11.5px; font-weight: 800; color: #ffffff; white-space: nowrap;">
                        {{ $this->activeShift?->cashier_name ?? auth()->user()->name ?? 'Kasir' }}
                    </span>
                </div>
            </div>

        </header>

        {{-- ========================================================
             2. AREA KERJA (FLEX 1 DENGAN OVERFLOW HIDDEN AGAR TIDAK TERPOTONG)
             ======================================================== --}}
        <div class="pos-main-flex" style="flex: 1; display: flex; min-height: 0; overflow: hidden;">
            
            {{-- PANEL KIRI: KATALOG PRODUK --}}
            <main style="flex: 1; display: flex; flex-direction: column; background: #F8FAFC; border-right: 1px solid #CBD5E1; min-height: 0; min-width: 0;">
                
                {{-- Bar Kendali Katalog: Search Bar & Kategori --}}
                <div style="background: #ffffff; border-bottom: 1px solid #CBD5E1; padding: 10px 14px; display: flex; flex-direction: column; gap: 10px; flex-shrink: 0; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    
                    {{-- Baris Atas: Kolom Pencarian --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                        <div style="flex: 1; max-width: 520px; position: relative; display: flex; align-items: center;">
                            <x-heroicon-m-magnifying-glass style="width: 16px; height: 16px; position: absolute; left: 12px; color: #64748B; pointer-events: none;" />
                            <input
                                type="text"
                                wire:model.live.debounce.250ms="search"
                                placeholder="Cari barang berdasarkan nama atau kode SKU..."
                                style="width: 100%; padding: 7px 30px 7px 36px; border-radius: 8px; border: 1.5px solid #E2E8F0; background: #F8FAFC; color: #0F172A; font-size: 12.5px; font-weight: 600; outline: none; transition: all 0.2s;"
                                onfocus="this.style.borderColor='#1E88E5'; this.style.background='#ffffff'; this.style.boxShadow='0 0 0 3px rgba(30,136,229,0.12)';"
                                onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC'; this.style.boxShadow='none';"
                            />
                            @if($search)
                                <button wire:click="$set('search', '')" style="position: absolute; right: 10px; border: none; background: transparent; color: #64748B; cursor: pointer; font-weight: bold; font-size: 13px; display: flex; align-items: center;" title="Hapus pencarian">
                                    ✕
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Baris Bawah: Tombol Semua Produk --}}
                    <div class="custom-scroll" style="display: flex; gap: 6px; overflow-x: auto; padding-top: 2px;">
                        <button
                            wire:click="selectCategory(null)"
                            style="padding: 5.5px 12px; border-radius: 6px; font-size: 11.5px; font-weight: 800; border: none; cursor: pointer; white-space: nowrap; transition: 0.15s; background: #1976D2; color: #ffffff; box-shadow: 0 1px 2px rgba(25,118,210,0.3);"
                        >
                            Semua Produk
                        </button>
                    </div>
                </div>

                {{-- Daftar Produk --}}
                <div class="custom-scroll" style="flex: 1; min-height: 0; overflow-y: auto; padding: 12px;">
                    @php
                        $products = $this->products;
                    @endphp

                    @if($products->isEmpty())
                        <div style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 20px;">
                            <div style="width: 44px; height: 44px; border-radius: 50%; background: #E2E8F0; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 6px;">
                                📦
                            </div>
                            <h3 style="font-size: 13px; font-weight: 800; color: #334155; margin: 0;">Barang Tidak Ditemukan</h3>
                        </div>
                    @else
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(145px, 1fr)); gap: 10px;">
                            @foreach($products as $product)
                                @php
                                    $inStock = ($product->stock_in_outlet ?? 0) > 0;
                                    $price = (float) $product->base_price;
                                @endphp
                                <div
                                    wire:click="addToCart({{ $product->id }})"
                                    style="background: #ffffff; border-radius: 8px; border: 1px solid #E2E8F0; box-shadow: 0 1px 2px rgba(0,0,0,0.04); overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; cursor: pointer; transition: 0.15s; {{ !$inStock ? 'opacity: 0.5; filter: grayscale(1);' : '' }}"
                                >
                                    {{-- Thumbnail Compact --}}
                                    <div style="height: 62px; background: linear-gradient(135deg, #EFF6FF 0%, #E0F2FE 100%); display: flex; align-items: center; justify-content: center; position: relative; border-bottom: 1px solid #F1F5F9;">
                                        <div style="width: 28px; height: 28px; border-radius: 6px; background: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.08); display: flex; align-items: center; justify-content: center; font-size: 15px;">
                                            🛍️
                                        </div>
                                        <span style="position: absolute; top: 4px; right: 4px; font-size: 9.5px; font-weight: 800; padding: 1px 5px; border-radius: 4px; {{ $inStock ? 'background: #D1FAE5; color: #065F46;' : 'background: #FEE2E2; color: #991B1B;' }}">
                                            {{ $inStock ? $product->stock_in_outlet : 'Habis' }}
                                        </span>
                                    </div>

                                    {{-- Info Produk Compact --}}
                                    <div style="padding: 8px 10px; display: flex; flex-direction: column; justify-content: space-between; flex: 1;">
                                        <div>
                                            <span style="font-size: 9.5px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block;">
                                                {{ $product->sku }}
                                            </span>
                                            <h4 style="font-size: 11.5px; font-weight: 800; color: #0F172A; margin: 2px 0 0 0; line-height: 1.25; height: 28px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                                {{ $product->name }}
                                            </h4>
                                        </div>

                                        <div style="margin-top: 6px; padding-top: 5px; border-top: 1px solid #F1F5F9; display: flex; align-items: center; justify-content: space-between;">
                                            <span style="font-size: 12.5px; font-weight: 900; color: #1976D2;">
                                                Rp {{ number_format($price, 0, ',', '.') }}
                                            </span>
                                            <span style="font-size: 10px; font-weight: 800; color: #1976D2; background: #EFF6FF; padding: 1px 5px; border-radius: 4px;">
                                                +
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </main>

            {{-- ========================================================
                 PANEL KANAN: KERANJANG BELANJA (FLEX COLUMN DENGAN MIN-HEIGHT: 0 AGAR FOOTER TAK TERPOTONG)
                 ======================================================== --}}
            <aside class="pos-cart-aside" style="width: 325px; min-width: 280px; flex-shrink: 0; display: flex; flex-direction: column; min-height: 0; background: #ffffff; box-shadow: -2px 0 8px rgba(0,0,0,0.05); z-index: 20;">
                
                {{-- Header Keranjang --}}
                <div style="padding: 10px 14px; background: #F8FAFC; border-bottom: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
                    <div>
                        <span style="font-size: 9.5px; font-weight: 800; text-transform: uppercase; color: #1976D2; display: block;">
                            Keranjang Pembeli
                        </span>
                        <h2 style="font-size: 13px; font-weight: 900; color: #0F172A; margin: 1px 0 0 0;">
                            Penjualan Langsung
                        </h2>
                    </div>

                    @if(count($cart) > 0)
                        <button
                            wire:click="$set('showClearCartModal', true)"
                            style="padding: 6px 12px; border-radius: 6px; background: #FEE2E2; color: #DC2626; border: 1px solid #FECACA; font-size: 11px; font-weight: 800; cursor: pointer; transition: 0.15s; display: flex; align-items: center; gap: 4px; min-height: 32px;"
                        >
                            🗑️ Kosongkan
                        </button>
                    @endif
                </div>

                {{-- Daftar Barang di Keranjang (BERGULIR MANDIRI) --}}
                <div class="custom-scroll" style="flex: 1; min-height: 0; overflow-y: auto; padding: 10px; display: flex; flex-direction: column; gap: 8px;">
                    @if(empty($cart))
                        <div style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; color: #94A3B8; padding: 14px;">
                            <div style="width: 44px; height: 44px; border-radius: 50%; background: #EFF6FF; display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 6px;">
                                🛒
                            </div>
                            <h4 style="font-size: 13px; font-weight: 800; color: #334155; margin: 0;">Keranjang Masih Kosong</h4>
                            <p style="font-size: 11px; color: #64748B; margin-top: 3px;">Klik produk di kiri untuk menambahkan.</p>
                        </div>
                    @else
                        @foreach($cart as $id => $item)
                            <div style="padding: 10px 12px; border-radius: 10px; background: #F8FAFC; border: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0;">
                                    <span style="min-width: 26px; height: 26px; border-radius: 6px; background: #1976D2; color: #ffffff; font-weight: 900; font-size: 11.5px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        {{ $item['quantity'] }}x
                                    </span>
                                    <div style="min-width: 0;">
                                        <h5 style="font-size: 12.5px; font-weight: 800; color: #0F172A; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ $item['name'] }}
                                        </h5>
                                        <span style="font-size: 10.5px; color: #64748B; display: block; font-weight: 600;">
                                            @ Rp {{ number_format($item['price'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>

                                <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                    <span style="font-size: 12.5px; font-weight: 900; color: #0F172A;">
                                        Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                    </span>

                                    <div style="display: flex; gap: 4px;">
                                        <button wire:click="updateQuantity({{ $id }}, -1)" style="width: 28px; height: 28px; border-radius: 6px; background: #E2E8F0; border: none; font-weight: 900; cursor: pointer; color: #334155; font-size: 14px; display: flex; align-items: center; justify-content: center; transition: 0.1s;">-</button>
                                        <button wire:click="updateQuantity({{ $id }}, 1)" style="width: 28px; height: 28px; border-radius: 6px; background: #1E88E5; border: none; font-weight: 900; cursor: pointer; color: #ffffff; font-size: 14px; display: flex; align-items: center; justify-content: center; transition: 0.1s;">+</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- FOOTER PEMBAYARAN: SELALU DI BAWAH & BERGULIR JIKA LAYAR TABLET PENDEK --}}
                <div class="custom-scroll" style="padding: 12px 14px; background: #F8FAFC; border-top: 1px solid #E2E8F0; display: flex; flex-direction: column; gap: 10px; flex-shrink: 0; max-height: 48vh; overflow-y: auto;">
                    
                    {{-- Total Bar --}}
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13.5px; font-weight: 900; color: #0F172A;">
                        <span>TOTAL BAYAR:</span>
                        <span style="color: #1976D2; font-size: 17px;">Rp {{ number_format($this->total, 0, ',', '.') }}</span>
                    </div>

                    @if(count($cart) > 0)
                        {{-- Pilihan Uang Cepat (Touch Friendly) --}}
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px;">
                            <button
                                wire:click="setQuickCash({{ $this->total }})"
                                style="padding: 8px 4px; min-height: 36px; border-radius: 8px; border: 1.5px solid #CBD5E1; font-size: 11px; font-weight: 800; cursor: pointer; transition: 0.15s; {{ $cashReceived == $this->total ? 'background: #1976D2; color: #ffffff; border-color: #1976D2;' : 'background: #ffffff; color: #334155;' }}"
                            >
                                Pas
                            </button>
                            @foreach($this->quickCashSuggestions as $suggestion)
                                @if($loop->index < 3)
                                    <button
                                        wire:click="setQuickCash({{ $suggestion }})"
                                        style="padding: 8px 4px; min-height: 36px; border-radius: 8px; border: 1.5px solid #CBD5E1; font-size: 11px; font-weight: 800; cursor: pointer; transition: 0.15s; {{ $cashReceived == $suggestion ? 'background: #1976D2; color: #ffffff; border-color: #1976D2;' : 'background: #ffffff; color: #334155;' }}"
                                    >
                                        {{ number_format($suggestion / 1000, 0) }}rb
                                    </button>
                                @endif
                            @endforeach
                        </div>

                        {{-- Input Uang Tunai & Kembalian (Large Touch Area) --}}
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                            <div>
                                <label style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #64748B; display: block; margin-bottom: 4px;">Diterima (Rp)</label>
                                <input
                                    type="number"
                                    wire:model.live="cashReceived"
                                    placeholder="0"
                                    style="width: 100%; padding: 8px 10px; min-height: 40px; border-radius: 8px; border: 1.5px solid #CBD5E1; font-weight: 900; font-size: 14px; color: #0F172A; background: #ffffff; outline: none;"
                                />
                            </div>
                            <div>
                                <label style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #64748B; display: block; margin-bottom: 4px;">Kembalian</label>
                                <div style="padding: 8px 10px; min-height: 40px; border-radius: 8px; background: #ECFDF5; border: 1.5px solid #A7F3D0; color: #047857; font-weight: 900; font-size: 14px; display: flex; align-items: center;">
                                    Rp {{ number_format($this->changeDue, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- TOMBOL BAYAR SEKARANG (EXTRA RESPONSIF & MUDAH DISENTUH) --}}
                    <button
                        wire:click="processCashPayment"
                        @if(empty($cart)) disabled @endif
                        style="width: 100%; padding: 14px 16px; min-height: 48px; border-radius: 10px; background: linear-gradient(135deg, #1565C0 0%, #1E88E5 100%); color: #ffffff; border: none; font-size: 13.5px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 12px rgba(25,118,210,0.35); transition: 0.15s; {{ empty($cart) ? 'opacity: 0.55; cursor: not-allowed;' : '' }}"
                    >
                        <span>BAYAR SEKARANG</span>
                        <span style="font-size: 15px;">Rp {{ number_format($this->total, 0, ',', '.') }} &rarr;</span>
                    </button>

                </div>

            </aside>

        </div>

        {{-- STRUK THERMAL 58MM — IWARE C58BT (tersembunyi di layar, hanya muncul saat print) --}}
        @if($lastOrderSummary)
            @php
                $rcptNumber = preg_replace('/[^0-9]/', '', $lastOrderSummary['order_number'] ?? '817559');
                if(empty($rcptNumber)) $rcptNumber = '817559';
            @endphp
            <div id="thermal-receipt" style="display:none; width:58mm; max-width:58mm; padding:3mm 4mm 6mm 4mm; box-sizing:border-box; font-family:'Courier New',Courier,monospace; font-size:11px; line-height:1.4; color:#000; background:#fff;">

                {{-- ① HEADER --}}
                <div style="text-align:center; margin-bottom:10px;">
                    <div style="font-size:13px; font-weight:bold;">Muliku Plastik store</div>
                    <div style="font-size:12px; font-weight:bold; margin-top:1px;">{{ strtoupper($lastOrderSummary['outlet_name'] ?? 'MULIKU STORE 02') }}</div>
                    <div style="font-size:10px; margin-top:5px; line-height:1.5;">
                        Jalan raya bungin pekon purawiwi<br>
                        tan kecamatan kebun tebu<br>
                        Indonesia, Lampung<br>
                        Lampung Barat<br>
                        081278295297
                    </div>
                    <div style="margin-top:8px; font-size:11px;">Receipt No. {{ $lastOrderSummary['order_number'] ?? '-' }}</div>
                </div>

                {{-- ② DATE & CASHIER (rata kiri, kolom label lebar tetap) --}}
                <table style="width:100%; font-size:11px; border-collapse:collapse; margin-bottom:10px;">
                    <tr>
                        <td style="width:70px; vertical-align:top; padding:1px 0;">Order Date</td>
                        <td style="vertical-align:top; padding:1px 0;">{{ $lastOrderSummary['date'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="vertical-align:top; padding:1px 0;">Cashier</td>
                        <td style="vertical-align:top; padding:1px 0; word-break:break-word;">{{ $lastOrderSummary['cashier_name'] ?? '-' }}</td>
                    </tr>
                </table>

                {{-- ③ DAFTAR BARANG --}}
                <div style="border-top:1px dashed #000; border-bottom:1px dashed #000; padding:6px 0; margin:8px 0;">
                    @php $totalItems = 0; @endphp
                    @if(!empty($lastOrderSummary['items']))
                        @foreach($lastOrderSummary['items'] as $item)
                            @php
                                $qty   = (int)($item['quantity'] ?? 1);
                                $price = (float)($item['price'] ?? 0);
                                $totalItems += $qty;
                                $itemTotal = number_format($price * $qty, 0, ',', '.');
                                // Bersihkan karakter non-ASCII (seperti tanda petik melengkung ’ yang berubah jadi ┌ÇÖ di printer thermal)
                                $search = ["\xE2\x80\x98", "\xE2\x80\x99", "\xE2\x80\x9A", "\xE2\x80\x9B", "\xE2\x80\x9C", "\xE2\x80\x9D", "\xE2\x80\x93", "\xE2\x80\x94"];
                                $replace = ["'", "'", "'", "'", '"', '"', "-", "-"];
                                $cleanName = preg_replace('/[^\x20-\x7E]/', '', str_replace($search, $replace, ($item['name'] ?? 'ITEM')));
                                $rawName = strtoupper(trim($cleanName));
                                $maxLen = 20;
                                $displayName = $qty . ' ' . (mb_strlen($rawName) > $maxLen ? mb_substr($rawName, 0, $maxLen) : $rawName);
                            @endphp
                            <div style="display:flex; justify-content:space-between; font-size:11px; margin-bottom:3px;">
                                <span style="flex:1; padding-right:4px; word-break:break-word;">{{ $displayName }}</span>
                                <span style="white-space:nowrap; flex-shrink:0;">{{ $itemTotal }}</span>
                            </div>
                            @if($qty > 1)
                                <div style="font-size:10px; padding-left:12px; margin-bottom:3px;">@ {{ number_format($price, 0, ',', '.') }}</div>
                            @endif
                        @endforeach
                    @endif
                </div>
                <div style="margin-bottom:8px; font-size:11px;">{{ $totalItems }} Items</div>

                {{-- ④ SUBTOTAL / TOTAL (tanpa garis, dua baris rapat) --}}
                <table style="width:100%; border-collapse:collapse; font-size:11px;">
                    <tr>
                        <td style="padding:1px 0;">Subtotal</td>
                        <td style="text-align:right; padding:1px 0;">{{ number_format($lastOrderSummary['total_amount'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding:1px 0;">TOTAL</td>
                        <td style="text-align:right; padding:1px 0;">{{ number_format($lastOrderSummary['total_amount'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                </table>

                {{-- ⑤ CASH & CHANGE DUE (spasi di atas agar seperti foto) --}}
                <table style="width:100%; border-collapse:collapse; font-size:11px; margin-top:12px;">
                    <tr>
                        <td style="padding:1px 0;">Cash</td>
                        <td style="text-align:right; padding:1px 0;">{{ number_format($lastOrderSummary['cash_received'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                </table>
                <table style="width:100%; border-collapse:collapse; font-size:11px; margin-top:12px;">
                    <tr>
                        <td style="padding:1px 0;">Change Due</td>
                        <td style="text-align:right; padding:1px 0;">{{ number_format($lastOrderSummary['change_amount'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                </table>

                {{-- ⑦ FOOTER: Instagram logo + Thanks --}}
                <div style="text-align:center; margin-top:14px; font-size:11px;">
                    <div style="display:flex; align-items:center; justify-content:center; gap:4px;">
                        {{-- Logo Instagram (SVG inline) --}}
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; flex-shrink:0;">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
                        </svg>
                        <span>@mulikustore</span>
                    </div>
                    <div style="margin-top:10px;">Thanks for shopping</div>
                </div>

                {{-- ⑧ BARCODE (via JsBarcode SVG, presisi di tengah) --}}
                <div style="text-align:center; margin-top:12px; width:100%;">
                    <div style="display:flex; justify-content:center; width:100%;">
                        <svg id="thermal-barcode-svg" data-barcode="{{ $rcptNumber }}"
                             style="display:block; max-width:100%; height:auto;"></svg>
                    </div>
                    <div style="font-size:11px; text-align:center; margin-top:3px; letter-spacing:1px;">{{ $rcptNumber }}</div>
                </div>

            </div>

        @assets
        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
        @endassets

        @script
        <script>
            window.printThermal = function() {
                var receipt    = document.getElementById('thermal-receipt');
                var svgEl      = document.getElementById('thermal-barcode-svg');
                var barcodeVal = svgEl ? svgEl.getAttribute('data-barcode') : '';

                // 1. Posisikan receipt JAUH di luar layar untuk render barcode
                //    (tidak pernah kelihatan user — hanya agar browser bisa hitung ukuran SVG)
                if (receipt) {
                    receipt.style.display    = 'block';
                    receipt.style.position   = 'fixed';
                    receipt.style.top        = '-99999px';  // jauh di luar viewport
                    receipt.style.left       = '-99999px';
                    receipt.style.visibility = 'hidden';    // benar-benar tidak kelihatan
                    receipt.style.zIndex     = '-1';
                }

                // 2. Render barcode ke SVG (browser bisa render karena element sudah di-display:block)
                if (svgEl && barcodeVal && typeof JsBarcode !== 'undefined') {
                    try {
                        JsBarcode(svgEl, barcodeVal, {
                            format:       'CODE128',
                            width:        2.2,
                            height:       52,
                            displayValue: false,
                            margin:       4,
                            marginTop:    4,
                            marginBottom: 4,
                            marginLeft:   4,
                            marginRight:  4,
                            background:   '#ffffff',
                            lineColor:    '#000000'
                        });
                        svgEl.style.display = 'block';
                        svgEl.style.margin  = '0 auto';
                    } catch(e) { console.error('JsBarcode error:', e); }
                }

                // 3. Setelah barcode render (beri 300ms), panggil print
                //    CSS @media print yang akan mengatur #thermal-receipt agar terlihat & di posisi benar
                //    JS tidak perlu set visibility/position — cukup panggil window.print()
                setTimeout(function() {
                    window.print();
                }, 300);
            };

            // Setelah print selesai / dialog ditutup: reset receipt ke hidden
            window.addEventListener('afterprint', function() {
                var el = document.getElementById('thermal-receipt');
                if (el) {
                    el.style.display    = 'none';
                    el.style.position   = '';
                    el.style.top        = '';
                    el.style.left       = '';
                    el.style.visibility = '';
                    el.style.zIndex     = '';
                }
            });
        </script>
        @endscript
        @endif

        {{-- MODAL STRUK SUKSES --}}
        @if($showSuccessModal && $lastOrderSummary)
            <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 50; padding: 16px;">
                <div style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 380px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
                    <div style="background: #059669; padding: 20px; text-align: center; color: #ffffff;">
                        <div style="width: 46px; height: 46px; border-radius: 50%; background: #ffffff; color: #059669; font-size: 24px; font-weight: 900; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px auto;">✓</div>
                        <h3 style="font-size: 16px; font-weight: 900; margin: 0;">Pembayaran Tunai Berhasil!</h3>
                        <p style="font-size: 11px; margin: 3px 0 0 0; opacity: 0.9;">No. Struk: #{{ $lastOrderSummary['order_reference'] ?? $lastOrderSummary['order_number'] ?? '-' }}</p>
                    </div>
                    <div style="padding: 16px; display: flex; flex-direction: column; gap: 8px; font-size: 12px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748B;">Total Pembelian:</span>
                            <span style="font-weight: 800;">Rp {{ number_format($lastOrderSummary['total_amount'] ?? $lastOrderSummary['total'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748B;">Uang Tunai Diterima:</span>
                            <span style="font-weight: 800;">Rp {{ number_format($lastOrderSummary['cash_received'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 1px solid #E2E8F0; font-size: 14px; font-weight: 900;">
                            <span style="color: #0F172A;">Uang Kembalian:</span>
                            <span style="color: #059669;">Rp {{ number_format($lastOrderSummary['change_amount'] ?? $lastOrderSummary['change_due'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div style="padding: 14px; background: #F8FAFC; border-top: 1px solid #E2E8F0; display: flex; flex-direction: column; gap: 8px;">
                        @if(isset($lastOrderSummary['order_id']) && $lastOrderSummary['order_id'])
                            @php
                                // Format 32-column exact receipt string directly in Blade for direct deep links
                                $fClean = function ($text) {
                                    if (!$text) return '';
                                    $search = ["\xE2\x80\x98", "\xE2\x80\x99", "\xE2\x80\x9A", "\xE2\x80\x9B", "\xE2\x80\x9C", "\xE2\x80\x9D", "\xE2\x80\x93", "\xE2\x80\x94"];
                                    $replace = ["'", "'", "'", "'", '"', '"', "-", "-"];
                                    return preg_replace('/[^\x20-\x7E]/', '', str_replace($search, $replace, $text));
                                };
                                $fRow32 = function ($left, $right) {
                                    $maxLeftLen = 32 - strlen($right) - 1;
                                    if (strlen($left) > $maxLeftLen) $left = substr($left, 0, $maxLeftLen);
                                    return $left . str_repeat(' ', max(1, 32 - strlen($left) - strlen($right))) . $right;
                                };
                                $fCenter32 = function ($text) {
                                    $text = trim($text);
                                    if (strlen($text) >= 32) return substr($text, 0, 32);
                                    return str_repeat(' ', (int)floor((32 - strlen($text)) / 2)) . $text;
                                };
                                $fCashier32 = function ($name) {
                                    $label = "Cashier      ";
                                    $maxRight = 32 - strlen($label);
                                    if (strlen($name) <= $maxRight) return $label . $name;
                                    return $label . substr($name, 0, $maxRight) . "\n" . str_repeat(' ', strlen($label)) . substr($name, $maxRight, $maxRight);
                                };

                                $rLines = [];
                                $rLines[] = $fCenter32("Muliku Plastik store");
                                $rLines[] = $fCenter32(strtoupper($fClean($lastOrderSummary['outlet_name'] ?? 'MULIKU STORE 02')));
                                $rLines[] = $fCenter32("Jalan raya bungin pekon purawiwi");
                                $rLines[] = $fCenter32("tan kecamatan kebun tebu");
                                $rLines[] = $fCenter32("Indonesia, Lampung");
                                $rLines[] = $fCenter32("Lampung Barat");
                                $rLines[] = $fCenter32("081278295297");
                                $rLines[] = $fCenter32("Receipt No. " . ($lastOrderSummary['order_number'] ?? 'POS-000'));
                                $rLines[] = " ";
                                $rLines[] = $fRow32("Order Date", now()->format('d/m/Y H:i:s'));
                                $rLines[] = $fCashier32($fClean($lastOrderSummary['cashier_name'] ?? 'Kasir'));
                                $rLines[] = "--------------------------------";

                                $tItems = 0;
                                foreach (($lastOrderSummary['items'] ?? []) as $item) {
                                    $q = (int)$item['quantity'];
                                    $tItems += $q;
                                    $pF = number_format($item['price'], 0, ',', '.');
                                    $tF = number_format($item['price'] * $q, 0, ',', '.');
                                    $n = strtoupper($fClean($item['name'] ?? 'ITEM'));
                                    if ($q == 1) {
                                        $rLines[] = $fRow32("1 " . $n, $tF);
                                    } else {
                                        $rLines[] = $fRow32($q . " " . $n, $tF);
                                        $rLines[] = "  @ " . $pF;
                                    }
                                }
                                $rLines[] = "--------------------------------";
                                $rLines[] = $tItems . " Items";
                                $rLines[] = $fRow32("Subtotal", number_format($lastOrderSummary['total_amount'] ?? 0, 0, ',', '.'));
                                $rLines[] = $fRow32("TOTAL", number_format($lastOrderSummary['total_amount'] ?? 0, 0, ',', '.'));
                                $rLines[] = " ";
                                $rLines[] = $fRow32("Cash", number_format($lastOrderSummary['cash_received'] ?? 0, 0, ',', '.'));
                                $rLines[] = $fRow32("Change Due", number_format($lastOrderSummary['change_amount'] ?? 0, 0, ',', '.'));
                                $rLines[] = " ";
                                $rLines[] = $fCenter32("IG: mulikustore");
                                $rLines[] = $fCenter32("Thanks for shopping");

                                $cleanOrderRef = preg_replace('/[^a-zA-Z0-9]/', '', ($lastOrderSummary['order_number'] ?? ''));
                                $receiptString = implode("\n", $rLines) . "\n\n" . $fCenter32($cleanOrderRef) . "\n\n\n\n";
                                $thermerUrl = url('/api/thermer-receipt/' . $lastOrderSummary['order_id']) . '?cash=' . ($lastOrderSummary['cash_received'] ?? 0) . '&change=' . ($lastOrderSummary['change_amount'] ?? $lastOrderSummary['change_due'] ?? 0) . '&cashier=' . urlencode($lastOrderSummary['cashier_name'] ?? '');
                                $btprinterDirectLink = "btprinter://print?content=" . urlencode($receiptString);

                                // Generator Gambar Raster Barcode (GS v 0) - 100% kompatibel di semua printer thermal IWARE C58BT
                                $genBarcodeImage = function ($codeStr) {
                                    if (!$codeStr) return '';
                                    $c39 = [
                                        '0'=>'000110100','1'=>'100100001','2'=>'001100001','3'=>'101100000','4'=>'000110001',
                                        '5'=>'100110000','6'=>'001110000','7'=>'000100101','8'=>'100100100','9'=>'001100100',
                                        'A'=>'100001001','B'=>'001001001','C'=>'101001000','D'=>'000011001','E'=>'100011000',
                                        'F'=>'001011000','G'=>'000001101','H'=>'100001100','I'=>'001001100','J'=>'000011100',
                                        'K'=>'100000011','L'=>'001000011','M'=>'101000010','N'=>'000010011','O'=>'100010010',
                                        'P'=>'001010010','Q'=>'000000111','R'=>'100000110','S'=>'001000110','T'=>'000010110',
                                        'U'=>'110000001','V'=>'011000001','W'=>'111000000','X'=>'010010001','Y'=>'110010000',
                                        'Z'=>'011010000','-'=>'010000101','*'=>'001001011'
                                    ];
                                    $clean = strtoupper(preg_replace('/[^a-zA-Z0-9\-]/', '', $codeStr));
                                    $full = '*' . $clean . '*';
                                    $dots = '';
                                    foreach (str_split($full) as $ch) {
                                        $pat = $c39[$ch] ?? $c39['0'];
                                        for ($i = 0; $i < 9; $i++) {
                                            $isBar = ($i % 2 == 0);
                                            $isWide = ($pat[$i] == '1');
                                            $dots .= str_repeat($isBar ? '1' : '0', $isWide ? 3 : 1);
                                        }
                                        $dots .= '0'; // Inter-character gap
                                    }
                                    $pad = max(0, floor((384 - strlen($dots)) / 2));
                                    $dots = str_repeat('0', $pad) . $dots;
                                    while (strlen($dots) < 384) $dots .= '0';
                                    $row = '';
                                    for ($i = 0; $i < 384; $i += 8) {
                                        $row .= chr(bindec(substr($dots, $i, 8)));
                                    }
                                    return "\x1D\x76\x30\x00\x30\x00\x3C\x00" . str_repeat($row, 60);
                                };

                                // Generator Gambar Raster (GS v 0) untuk Logo Instagram + @mulikustore (UKURAN BESAR / BOLD)
                                $genIgHeader = function () {
                                    if (!function_exists('imagecreate')) return "@mulikustore\n";
                                    $im = imagecreate(384, 40);
                                    imagecolorallocate($im, 255, 255, 255);
                                    $black = imagecolorallocate($im, 0, 0, 0);

                                    $temp = imagecreate(115, 18);
                                    imagecolorallocate($temp, 255, 255, 255);
                                    $tBlack = imagecolorallocate($temp, 0, 0, 0);
                                    imagestring($temp, 5, 2, 1, "@mulikustore", $tBlack);

                                    $textW = floor(112 * 1.8);
                                    $textH = floor(16 * 1.8);
                                    $igW = 28;
                                    $gap = 10;
                                    $totalW = $igW + $gap + $textW;
                                    $startX = floor((384 - $totalW) / 2);
                                    $startY = 6;

                                    for ($t = 0; $t < 3; $t++) {
                                        imagerectangle($im, $startX + $t, $startY + $t, $startX + $igW - 1 - $t, $startY + $igW - 1 - $t, $black);
                                    }
                                    for ($r = 12; $r <= 14; $r++) {
                                        imagearc($im, $startX + 14, $startY + 14, $r, $r, 0, 360, $black);
                                    }
                                    imagefilledrectangle($im, $startX + 21, $startY + 6, $startX + 23, $startY + 8, $black);

                                    imagecopyresized($im, $temp, $startX + $igW + $gap, $startY - 1, 0, 0, $textW, $textH, 112, 16);
                                    imagedestroy($temp);

                                    $rowBytes = '';
                                    for ($y = 0; $y < 40; $y++) {
                                        for ($x = 0; $x < 384; $x += 8) {
                                            $byte = 0;
                                            for ($b = 0; $b < 8; $b++) {
                                                if ($x + $b < 384 && imagecolorat($im, $x + $b, $y) === $black) {
                                                    $byte |= (1 << (7 - $b));
                                                }
                                            }
                                            $rowBytes .= chr($byte);
                                        }
                                    }
                                    imagedestroy($im);
                                    return "\x1D\x76\x30\x00\x30\x00\x28\x00" . $rowBytes;
                                };

                                // Untuk RawBT: urutan sempurna -> [Gambar Logo IG + @mulikustore] -> Thanks for shopping -> [Gambar Barcode] -> Nomor Resi rapat di bawah barcode
                                $topLines = implode("\n", array_slice($rLines, 0, count($rLines) - 2));
                                $igRaster = $genIgHeader();
                                $barcodeRaster = $genBarcodeImage($cleanOrderRef);
                                $rawbtBinaryPayload = $topLines . "\n\n" . $igRaster . "\n" . $fCenter32("Thanks for shopping") . "\n\n" . $barcodeRaster . $fCenter32($cleanOrderRef) . "\n\n\n\n";
                                $rawbtDirectLink = "rawbt:base64," . base64_encode($rawbtBinaryPayload);
                            @endphp
                            <a href="{{ $rawbtDirectLink }}" onclick="this.style.pointerEvents='none'; setTimeout(() => this.style.pointerEvents='auto', 3000);" style="width: 100%; padding: 14px; border-radius: 8px; background: #EA580C; color: #ffffff; text-decoration: none; font-weight: 900; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.35);">
                                <span>🖨️ CETAK STRUK</span>
                            </a>
                        @else
                            <button onclick="window.printThermal()" style="width: 100%; padding: 14px; border-radius: 8px; background: #EA580C; color: #ffffff; border: none; font-weight: 900; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.35);">
                                <span>🖨️ CETAK STRUK</span>
                            </button>
                        @endif
                        <div style="display: flex; gap: 8px;">
                            <button wire:click="closeSuccessModal" style="flex: 1; padding: 10px; border-radius: 8px; background: #E2E8F0; border: none; font-weight: 800; font-size: 11px; cursor: pointer;">
                                TRANSAKSI BARU
                            </button>
                            <a href="{{ url('/admin/orders') }}" style="flex: 1; padding: 10px; border-radius: 8px; background: #1976D2; color: #ffffff; text-decoration: none; text-align: center; font-weight: 800; font-size: 11px; cursor: pointer;">
                                LIHAT RIWAYAT
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- MODAL KONFIRMASI KOSONGKAN KERANJANG YANG EKSKLUSIF & MODERN --}}
        @if($showClearCartModal)
            <div style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 60; padding: 16px;">
                <div style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 350px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.25); border: 1px solid #E2E8F0; animation: modalPop 0.2s ease-out;">
                    <div style="padding: 24px 20px 16px 20px; text-align: center;">
                        <div style="width: 54px; height: 54px; border-radius: 50%; background: #FEF2F2; color: #DC2626; font-size: 26px; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px auto; border: 4px solid #FEE2E2; box-shadow: 0 4px 10px rgba(220,38,38,0.15);">
                            🗑️
                        </div>
                        <h3 style="font-size: 16px; font-weight: 900; color: #0F172A; margin: 0;">Kosongkan Keranjang?</h3>
                        <p style="font-size: 12px; color: #64748B; margin: 8px 0 0 0; line-height: 1.5;">
                            Semua <strong style="color: #0F172A;">{{ count($cart) }} barang</strong> yang sudah dimasukkan ke keranjang akan dihapus dan diulang dari nol.
                        </p>
                    </div>
                    <div style="padding: 14px 16px; background: #F8FAFC; border-top: 1px solid #E2E8F0; display: flex; gap: 10px;">
                        <button
                            wire:click="$set('showClearCartModal', false)"
                            style="flex: 1; padding: 10px; border-radius: 10px; background: #E2E8F0; color: #334155; border: none; font-weight: 800; font-size: 12px; cursor: pointer; transition: 0.15s;"
                        >
                            Batal
                        </button>
                        <button
                            wire:click="clearCart"
                            style="flex: 1; padding: 10px; border-radius: 10px; background: #DC2626; color: #ffffff; border: none; font-weight: 900; font-size: 12px; cursor: pointer; transition: 0.15s; box-shadow: 0 3px 8px rgba(220, 38, 38, 0.35);"
                        >
                            Ya, Kosongkan
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
