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
                    <div style="padding: 14px; background: #F8FAFC; border-top: 1px solid #E2E8F0; display: flex; gap: 8px;">
                        <button wire:click="closeSuccessModal" style="flex: 1; padding: 10px; border-radius: 8px; background: #E2E8F0; border: none; font-weight: 800; font-size: 11px; cursor: pointer;">
                            TRANSAKSI BARU
                        </button>
                        <a href="{{ url('/admin/orders') }}" style="flex: 1; padding: 10px; border-radius: 8px; background: #1976D2; color: #ffffff; text-decoration: none; text-align: center; font-weight: 800; font-size: 11px; cursor: pointer;">
                            LIHAT RIWAYAT
                        </a>
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
