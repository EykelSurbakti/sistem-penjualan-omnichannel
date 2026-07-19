<x-filament-panels::page>
    <div class="space-y-4">

        {{-- =====================================================================
             PANEL DETAIL PESANAN (MUNCUL JIKA KARTU PESANAN DIKLIK - ALA ISELLER SCREENSHOT 2)
             ===================================================================== --}}
        @if($this->selectedOrder)
            @php $order = $this->selectedOrder; @endphp
            <div class="space-y-4">
                
                {{-- Tombol Kembali & Header Detail --}}
                <div class="p-4 rounded-2xl bg-blue-600 text-white flex items-center justify-between shadow-sm">
                    <button
                        wire:click="closeDetail()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition"
                    >
                        <x-heroicon-m-arrow-left class="w-4 h-4" />
                        Kembali ke Daftar
                    </button>
                    <div class="text-right">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-200 block">RINCIAN PESANAN</span>
                        <h3 class="text-lg font-black">{{ $order->order_number }}</h3>
                    </div>
                </div>

                {{-- Informasi Umum --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-4">
                    <h4 class="text-sm font-black text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">
                        Informasi Umum
                    </h4>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 font-semibold">Channel Penjualan</span>
                            <span class="font-extrabold text-blue-600 bg-blue-50 dark:bg-blue-900/30 px-2.5 py-1 rounded-lg">
                                {{ $order->channel?->name ?: 'Point of Sale (POS)' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 font-semibold">Waktu Transaksi</span>
                            <span class="font-bold text-gray-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('d F Y, H:i') }} WIB
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 font-semibold">Cabang Toko</span>
                            <span class="font-bold text-gray-900 dark:text-white">
                                {{ $order->outlet?->name ?: 'Toko Utama' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 font-semibold">Kasir Bertugas</span>
                            <span class="font-bold text-gray-900 dark:text-white">
                                {{ $order->cashier?->name ?: 'Kasir' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 font-semibold">Pelanggan</span>
                            <span class="font-bold text-gray-900 dark:text-white">
                                {{ $order->customer?->name ?: 'Umum (Pelanggan Walk-in)' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 font-semibold">Status & Metode Bayar</span>
                            <div class="flex items-center gap-1.5">
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500 text-white font-extrabold text-[10px] uppercase">
                                    {{ $order->payment_status === 'paid' ? 'LUNAS' : strtoupper($order->payment_status) }}
                                </span>
                                <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold text-[10px] uppercase">
                                    {{ $order->payment_method ?: 'TUNAI' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Daftar Produk Pesanan --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-4">
                    <h4 class="text-sm font-black text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">
                        Daftar Barang Dipesan
                    </h4>

                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($order->items as $item)
                            <div class="py-3 flex items-center justify-between gap-3 text-xs">
                                <div>
                                    <div class="font-bold text-gray-900 dark:text-white">
                                        {{ $item->product?->name ?: 'Produk' }}
                                    </div>
                                    <div class="text-gray-500 mt-0.5">
                                        {{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                    </div>
                                </div>
                                <div class="font-black text-gray-900 dark:text-white">
                                    Rp {{ number_format($item->total_price, 0, ',', '.') }}
                                </div>
                            </div>
                        @empty
                            <div class="py-4 text-center text-gray-500 text-xs">
                                Tidak ada rincian barang
                            </div>
                        @endforelse
                    </div>

                    {{-- Total Pembayaran --}}
                    <div class="pt-3 border-t border-gray-200 dark:border-gray-800 flex items-center justify-between">
                        <span class="text-sm font-black text-gray-900 dark:text-white">TOTAL PEMBAYARAN</span>
                        <span class="text-lg font-black text-emerald-600">
                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

            </div>

        {{-- =====================================================================
             VIEW UTAMA: DAFTAR KARTU PESANAN ALA ISELLER (SCREENSHOT 1)
             ===================================================================== --}}
        @else
            {{-- Bilah Filter Toko & Pencarian --}}
            <div class="p-4 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-3">
                
                {{-- Pilihan Toko Lengkap (Anti-Hilang) --}}
                <div style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                    <button
                        wire:click="setOutlet(null)"
                        style="padding: 6px 14px; border-radius: 10px; font-size: 11px; font-weight: 800; border: 1px solid {{ is_null($selectedOutletId) ? '#2563EB' : '#E5E7EB' }}; cursor: pointer; transition: 0.2s; {{ is_null($selectedOutletId) ? 'background: #2563EB; color: #ffffff;' : 'background: #F3F4F6; color: #374151;' }}"
                    >
                        Semua Toko (Konsolidasi)
                    </button>
                    @foreach($this->outlets as $outlet)
                        <button
                            wire:click="setOutlet({{ $outlet->id }})"
                            style="padding: 6px 14px; border-radius: 10px; font-size: 11px; font-weight: 800; border: 1px solid {{ $selectedOutletId === $outlet->id ? '#2563EB' : '#E5E7EB' }}; cursor: pointer; transition: 0.2s; {{ $selectedOutletId === $outlet->id ? 'background: #2563EB; color: #ffffff;' : 'background: #F3F4F6; color: #374151;' }}"
                        >
                            {{ $outlet->name }}
                        </button>
                    @endforeach
                </div>

                {{-- Input Pencarian --}}
                <div class="relative flex items-center">
                    <x-heroicon-m-magnifying-glass class="w-4 h-4 text-gray-400 absolute pointer-events-none shrink-0" style="left: 12px !important;" />
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari nomor pesanan atau nama kasir..."
                        style="padding-left: 36px !important;"
                        class="w-full pr-4 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>

            </div>

            {{-- Daftar Pesanan Berupa Kartu Modern Ala iSeller --}}
            @php
                $orders = $this->orders;
            @endphp

            @if($orders->isEmpty())
                <div class="p-10 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-center space-y-2">
                    <div class="text-gray-400">Belum ada transaksi pesanan yang sesuai</div>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($orders as $order)
                        <div
                            wire:click="selectOrder({{ $order->id }})"
                            class="p-4 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 hover:border-blue-500 dark:hover:border-blue-500 shadow-sm transition cursor-pointer space-y-2.5"
                        >
                            {{-- Baris 1: Nomor Pesanan & Waktu --}}
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-black text-gray-900 dark:text-white">
                                    {{ $order->order_number }}
                                </span>
                                <span class="text-xs font-semibold text-gray-400">
                                    {{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }} WIB • {{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('d M Y') }}
                                </span>
                            </div>

                            {{-- Baris 2: Toko & Kasir --}}
                            <div class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                                <div class="p-1 rounded bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                                    <x-heroicon-m-computer-desktop class="w-3.5 h-3.5" />
                                </div>
                                <span class="font-extrabold text-gray-900 dark:text-white">
                                    {{ $order->outlet?->name ?: 'Toko' }}
                                </span>
                                <span class="text-gray-400">•</span>
                                <span class="font-medium text-gray-500">
                                    Kasir: {{ $order->cashier?->name ?: '-' }}
                                </span>
                            </div>

                            {{-- Baris 3: Status LUNAS / DIPENUHI & Nominal Rp --}}
                            <div class="flex items-center justify-between pt-1 border-t border-gray-100 dark:border-gray-800/60">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-500 text-white font-black text-[10px] tracking-wide">
                                        {{ $order->payment_status === 'paid' ? 'LUNAS' : strtoupper($order->payment_status) }}
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full bg-blue-500 text-white font-black text-[10px] tracking-wide">
                                        DIPENUHI
                                    </span>
                                </div>
                                <div class="text-base font-black text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        @endif

    </div>
</x-filament-panels::page>
