<x-filament-panels::page>
    <div class="space-y-4">

        {{-- BILAH FILTER CABANG & PERIODE LAPORAN --}}
        <div class="p-4 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                
                {{-- Kiri: Judul & Filter Toko --}}
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        @if($activeReport !== 'menu')
                            <button
                                wire:click="setActiveReport('menu')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-extrabold hover:bg-blue-100 transition"
                            >
                                <x-heroicon-m-arrow-left class="w-4 h-4" />
                                Kembali ke Daftar Laporan
                            </button>
                        @endif
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                            PUSAT ANALISIS & LAPORAN BISNIS
                        </span>
                    </div>

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
                </div>

                {{-- Kanan: Pilihan Periode Waktu --}}
                <div style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                    <button
                        wire:click="setPeriod('today')"
                        style="padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; border: 1px solid {{ $period === 'today' ? '#2563EB' : '#E5E7EB' }}; cursor: pointer; {{ $period === 'today' ? 'background: #EFF6FF; color: #2563EB; font-weight: 800;' : 'background: #ffffff; color: #6B7280;' }}"
                    >
                        Hari Ini
                    </button>
                    <button
                        wire:click="setPeriod('7_days')"
                        style="padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; border: 1px solid {{ $period === '7_days' ? '#2563EB' : '#E5E7EB' }}; cursor: pointer; {{ $period === '7_days' ? 'background: #EFF6FF; color: #2563EB; font-weight: 800;' : 'background: #ffffff; color: #6B7280;' }}"
                    >
                        7 Hari Terakhir
                    </button>
                    <button
                        wire:click="setPeriod('this_month')"
                        style="padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; border: 1px solid {{ $period === 'this_month' ? '#2563EB' : '#E5E7EB' }}; cursor: pointer; {{ $period === 'this_month' ? 'background: #EFF6FF; color: #2563EB; font-weight: 800;' : 'background: #ffffff; color: #6B7280;' }}"
                    >
                        Bulan Ini
                    </button>
                    <button
                        wire:click="setPeriod('this_year')"
                        style="padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; border: 1px solid {{ $period === 'this_year' ? '#2563EB' : '#E5E7EB' }}; cursor: pointer; {{ $period === 'this_year' ? 'background: #EFF6FF; color: #2563EB; font-weight: 800;' : 'background: #ffffff; color: #6B7280;' }}"
                    >
                        Tahun Ini
                    </button>
                </div>

            </div>
        </div>

        {{-- =====================================================================
             VIEW 1: KATALOG MENU LAPORAN (PERSIS TAMPILAN LAPORAN ISELLER)
             ===================================================================== --}}
        @if($activeReport === 'menu')
            <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
                <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">
                    Pilih Kategori Laporan Bisnis
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    
                    {{-- 1. Laporan Penjualan --}}
                    <div
                        wire:click="setActiveReport('penjualan')"
                        class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 hover:border-blue-500 dark:hover:border-blue-500 hover:shadow-md transition cursor-pointer flex items-center gap-4 bg-gray-50/50 dark:bg-gray-800/40 group"
                    >
                        <div class="p-3 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 group-hover:scale-110 transition">
                            <x-heroicon-m-chart-bar class="w-6 h-6" />
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-gray-900 dark:text-white group-hover:text-blue-600 transition">
                                Laporan Penjualan
                            </h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Rekap omset harian, jumlah transaksi & rata-rata per belanja
                            </p>
                        </div>
                    </div>

                    {{-- 2. Laporan Penjualan Produk --}}
                    <div
                        wire:click="setActiveReport('produk')"
                        class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 hover:border-blue-500 dark:hover:border-blue-500 hover:shadow-md transition cursor-pointer flex items-center gap-4 bg-gray-50/50 dark:bg-gray-800/40 group"
                    >
                        <div class="p-3 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 group-hover:scale-110 transition">
                            <x-heroicon-m-cube class="w-6 h-6" />
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-gray-900 dark:text-white group-hover:text-blue-600 transition">
                                Laporan Penjualan Produk
                            </h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Analisis produk terlaris, kontribusi kuantitas & omset per SKU
                            </p>
                        </div>
                    </div>

                    {{-- 3. Laporan Pembayaran --}}
                    <div
                        wire:click="setActiveReport('pembayaran')"
                        class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 hover:border-blue-500 dark:hover:border-blue-500 hover:shadow-md transition cursor-pointer flex items-center gap-4 bg-gray-50/50 dark:bg-gray-800/40 group"
                    >
                        <div class="p-3 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 group-hover:scale-110 transition">
                            <x-heroicon-m-credit-card class="w-6 h-6" />
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-gray-900 dark:text-white group-hover:text-blue-600 transition">
                                Laporan Pembayaran
                            </h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Rincian metode pembayaran Tunai, QRIS, Transfer Bank & Kartu
                            </p>
                        </div>
                    </div>

                    {{-- 4. Laba Rugi (Profit & Loss) --}}
                    <div
                        wire:click="setActiveReport('laba_rugi')"
                        class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 hover:border-blue-500 dark:hover:border-blue-500 hover:shadow-md transition cursor-pointer flex items-center gap-4 bg-gray-50/50 dark:bg-gray-800/40 group"
                    >
                        <div class="p-3 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition">
                            <x-heroicon-m-banknotes class="w-6 h-6" />
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-gray-900 dark:text-white group-hover:text-emerald-600 transition">
                                Laba Rugi (Profit & Loss)
                            </h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Perhitungan omset bersih dikurangi HPP untuk mengetahui margin laba
                            </p>
                        </div>
                    </div>

                    {{-- 5. Laporan Kasir & Shift --}}
                    <div
                        wire:click="setActiveReport('kasir')"
                        class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 hover:border-blue-500 dark:hover:border-blue-500 hover:shadow-md transition cursor-pointer flex items-center gap-4 bg-gray-50/50 dark:bg-gray-800/40 group"
                    >
                        <div class="p-3 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 group-hover:scale-110 transition">
                            <x-heroicon-m-user-group class="w-6 h-6" />
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-gray-900 dark:text-white group-hover:text-blue-600 transition">
                                Laporan Kasir & Shift
                            </h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Log akurasi setoran laci kasir & riwayat buka/tutup shift per cabang
                            </p>
                        </div>
                    </div>

                </div>
            </div>

        {{-- =====================================================================
             VIEW 2: RINCIAN LAPORAN PENJUALAN
             ===================================================================== --}}
        @elseif($activeReport === 'penjualan')
            @php $rep = $this->penjualanReport; @endphp
            <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Laporan Omset Penjualan</h3>
                        <p class="text-xs text-gray-500">Ringkasan transaksi dan omset berdasar periode terpilih</p>
                    </div>
                    <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow transition">
                        Cetak Laporan / PDF
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
                        <span class="text-xs font-bold text-gray-500">Total Omset</span>
                        <div class="text-xl font-black text-emerald-600 mt-1">Rp {{ number_format($rep['summary']['total_omset'], 0, ',', '.') }}</div>
                    </div>
                    <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
                        <span class="text-xs font-bold text-gray-500">Total Transaksi</span>
                        <div class="text-xl font-black text-gray-900 dark:text-white mt-1">{{ number_format($rep['summary']['total_transaksi'], 0, ',', '.') }} Pesanan</div>
                    </div>
                    <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
                        <span class="text-xs font-bold text-gray-500">Rata-rata per Transaksi</span>
                        <div class="text-xl font-black text-blue-600 mt-1">Rp {{ number_format($rep['summary']['rata_rata'], 0, ',', '.') }}</div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-800 text-xs font-black uppercase text-gray-500">
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4 text-center">Jumlah Transaksi</th>
                                <th class="py-3 px-4 text-right">Total Omset</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                            @forelse($rep['rows'] as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d M Y') }}</td>
                                    <td class="py-3 px-4 text-center">{{ $row->total_transaksi }} Pesanan</td>
                                    <td class="py-3 px-4 text-right font-black text-emerald-600">Rp {{ number_format($row->total_omset, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-gray-500">Belum ada transaksi pada periode ini</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        {{-- =====================================================================
             VIEW 3: RINCIAN LAPORAN PRODUK TERLARIS
             ===================================================================== --}}
        @elseif($activeReport === 'produk')
            @php $prod = $this->produkReport; @endphp
            <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Laporan Penjualan Produk</h3>
                        <p class="text-xs text-gray-500">Daftar produk terlaris berdasarkan kuantitas & nilai omset</p>
                    </div>
                    <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow transition">
                        Cetak Laporan / PDF
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-800 text-xs font-black uppercase text-gray-500">
                                <th class="py-3 px-4">Nama Produk</th>
                                <th class="py-3 px-4">SKU</th>
                                <th class="py-3 px-4 text-center">Terjual (Unit)</th>
                                <th class="py-3 px-4 text-right">Total Omset</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                            @forelse($prod['rows'] as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $item->product_name }}</td>
                                    <td class="py-3 px-4 text-gray-500 text-xs">{{ $item->product_sku ?: '-' }}</td>
                                    <td class="py-3 px-4 text-center font-bold">{{ $item->total_qty }} Unit</td>
                                    <td class="py-3 px-4 text-right font-black text-blue-600">Rp {{ number_format($item->total_omset, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-gray-500">Belum ada penjualan produk pada periode ini</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        {{-- =====================================================================
             VIEW 4: LABA RUGI (PROFIT & LOSS)
             ===================================================================== --}}
        @elseif($activeReport === 'laba_rugi')
            @php $lr = $this->labaRugiReport; @endphp
            <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Laba Rugi (Profit & Loss Report)</h3>
                        <p class="text-xs text-gray-500">Kalkulasi omset kotor dikurangi Harga Pokok Penjualan (HPP)</p>
                    </div>
                    <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow transition">
                        Cetak Laporan / PDF
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-5 rounded-xl bg-blue-50/50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800">
                        <span class="text-xs font-bold text-blue-600 uppercase">Total Omset Bruto</span>
                        <div class="text-2xl font-black text-gray-900 dark:text-white mt-1">Rp {{ number_format($lr['gross_revenue'], 0, ',', '.') }}</div>
                    </div>
                    <div class="p-5 rounded-xl bg-red-50/50 dark:bg-red-900/10 border border-red-200 dark:border-red-800">
                        <span class="text-xs font-bold text-red-600 uppercase">Perkiraan HPP Produk</span>
                        <div class="text-2xl font-black text-red-600 mt-1">Rp {{ number_format($lr['total_hpp'], 0, ',', '.') }}</div>
                    </div>
                    <div class="p-5 rounded-xl bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-200 dark:border-emerald-800">
                        <span class="text-xs font-bold text-emerald-600 uppercase">Laba Bersih Kotor (Margin {{ $lr['margin_pct'] }}%)</span>
                        <div class="text-2xl font-black text-emerald-600 mt-1">Rp {{ number_format($lr['net_profit'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

        {{-- =====================================================================
             VIEW 5: LAPORAN PEMBAYARAN
             ===================================================================== --}}
        @elseif($activeReport === 'pembayaran')
            @php $pem = $this->pembayaranReport; @endphp
            <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Laporan Metode Pembayaran</h3>
                        <p class="text-xs text-gray-500">Sebaran transaksi berdasarkan Tunai, QRIS, Transfer & Kartu</p>
                    </div>
                    <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow transition">
                        Cetak Laporan / PDF
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-800 text-xs font-black uppercase text-gray-500">
                                <th class="py-3 px-4">Metode Pembayaran</th>
                                <th class="py-3 px-4 text-center">Jumlah Transaksi</th>
                                <th class="py-3 px-4 text-right">Total Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                            @forelse($pem['rows'] as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white uppercase">{{ $row->payment_method ?: 'Tunai' }}</td>
                                    <td class="py-3 px-4 text-center font-bold">{{ $row->total_transaksi }} Pesanan</td>
                                    <td class="py-3 px-4 text-right font-black text-emerald-600">Rp {{ number_format($row->total_omset, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-gray-500">Belum ada transaksi pembayaran</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        {{-- =====================================================================
             VIEW 6: LAPORAN KASIR & SHIFT
             ===================================================================== --}}
        @elseif($activeReport === 'kasir')
            @php $shifts = $this->kasirReport; @endphp
            <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Laporan Kasir & Shift Toko</h3>
                        <p class="text-xs text-gray-500">Rekapitulasi pembukaan laci, modal awal, setoran akhir kasir, dan status sesi per cabang</p>
                    </div>
                    <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow transition">
                        Cetak Laporan / PDF
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-800 text-xs font-black uppercase text-gray-500">
                                <th class="py-3 px-4">Toko & Nama Kasir</th>
                                <th class="py-3 px-4">Waktu Sesi Shift</th>
                                <th class="py-3 px-4 text-right">Modal Awal</th>
                                <th class="py-3 px-4 text-right">Setoran Akhir Tutup</th>
                                <th class="py-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                            @forelse($shifts as $shift)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-gray-900 dark:text-white">
                                            👤 {{ $shift->cashier_name ?? $shift->user?->name ?? 'Kasir' }}
                                        </div>
                                        @if($shift->cashier_name && $shift->cashier_name !== ($shift->user?->name ?? ''))
                                            <div class="text-xs text-gray-500 font-medium mt-0.5">
                                                Akun: {{ $shift->user?->name }}
                                            </div>
                                        @endif
                                        <div class="text-xs text-blue-600 dark:text-blue-400 font-semibold mt-0.5">
                                            🏪 {{ $shift->outlet?->name ?: 'Toko Utama' }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-600 dark:text-gray-300">
                                        <div><span class="font-bold">Buka:</span> {{ \Carbon\Carbon::parse($shift->opened_at)->format('d/m/Y H:i') }} WIB</div>
                                        @if($shift->closed_at)
                                            <div class="mt-0.5"><span class="font-bold">Tutup:</span> {{ \Carbon\Carbon::parse($shift->closed_at)->format('d/m/Y H:i') }} WIB</div>
                                        @else
                                            <div class="mt-0.5 text-emerald-600 font-extrabold">&bull; Sesi Sedang Berjalan</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right font-black text-gray-900 dark:text-white">
                                        Rp {{ number_format($shift->initial_cash, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-black text-blue-600 dark:text-blue-400">
                                        @if($shift->status === 'open')
                                            <span class="text-xs text-gray-400 italic">Belum Setor (Aktif)</span>
                                        @else
                                            Rp {{ number_format($shift->closing_cash, 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($shift->status === 'open')
                                            <span class="px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-extrabold text-[10px] uppercase tracking-wide">
                                                BERJALAN
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 font-bold text-[10px] uppercase tracking-wide">
                                                SELESAI
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-500">
                                        Belum ada riwayat shift kasir tercatat pada periode ini
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
