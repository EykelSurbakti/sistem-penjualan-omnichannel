<x-filament-panels::page>
    @php
        $user = auth()->user();
        $isKasir = !is_null($user?->outlet_id);
    @endphp

    @if($isKasir)
        {{-- =========================================================================
             DASBOR KASIR CABANG
             ========================================================================= --}}
        <div class="space-y-4">
            <div class="p-5 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
                <div>
                    <h2 class="text-lg font-black text-gray-900 dark:text-white">
                        Selamat Datang, {{ $user->name }}
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Cabang: <span class="font-bold text-blue-600 dark:text-blue-400">{{ $user->outlet?->name ?? 'Toko' }}</span>
                    </p>
                </div>
                <a
                    href="{{ url('/portal-kasir') }}"
                    class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow transition"
                >
                    Buka Portal Kerja Kasir
                </a>
            </div>
        </div>

    @else
        {{-- =========================================================================
             DASBOR MONITORING EKSEKUTIF PEMILIK (SANGAT PADAT, JELAS NAMA TOKO & ANTI SCROLL PANJANG)
             ========================================================================= --}}
        @php
            $period = $this->period ?? session('monitoring_period', 'today');
            $ordersQuery = \App\Models\Order::query();
            if (auth()->check() && auth()->user()->outlet_id) {
                $ordersQuery->where('outlet_id', auth()->user()->outlet_id);
            } elseif ($selectedMonitoringOutletId) {
                $ordersQuery->where('outlet_id', $selectedMonitoringOutletId);
            }

            $now = \Illuminate\Support\Carbon::now();
            if ($period === 'today') {
                $ordersQuery->whereDate('created_at', $now->toDateString());
            } elseif ($period === 'yesterday') {
                $ordersQuery->whereDate('created_at', $now->copy()->subDay()->toDateString());
            } elseif ($period === '7_days') {
                $ordersQuery->where('created_at', '>=', $now->copy()->subDays(7)->startOfDay());
            } elseif ($period === 'this_month') {
                $ordersQuery->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
            } elseif ($period === 'this_year') {
                $ordersQuery->whereYear('created_at', $now->year);
            } // else all_time

            $paidQuery = (clone $ordersQuery)->where('payment_status', 'paid');
            $totalSales = $paidQuery->sum('total_amount');
            $totalOrders = (clone $ordersQuery)->count();
            $avgSales = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

            $activeOutletName = 'Semua Toko (Konsolidasi)';
            if ($selectedMonitoringOutletId) {
                $outletObj = \App\Models\Outlet::find($selectedMonitoringOutletId);
                if ($outletObj) $activeOutletName = $outletObj->name;
            }

            $periodLabels = [
                'today' => 'Hari Ini',
                'yesterday' => 'Kemarin',
                '7_days' => '7 Hari Terakhir',
                'this_month' => 'Bulan Ini',
                'this_year' => 'Tahun Ini',
                'all_time' => 'Semua Waktu'
            ];
        @endphp

        <div class="space-y-4">

            {{-- 1. HEADER MONITORING & GLOBAL PERIOD FILTER (ALA iSELLER) --}}
            <div class="p-4 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm space-y-3">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                            MONITORING EKSEKUTIF
                        </span>
                        <h2 class="text-base font-black text-gray-900 dark:text-white leading-tight">
                            {{ $activeOutletName }} &bull; <span class="text-blue-600 dark:text-blue-400">{{ $periodLabels[$period] ?? 'Hari Ini' }}</span>
                        </h2>
                    </div>
                </div>

                {{-- Baris 1: Filter Toko --}}
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Pilih Cabang Toko:</span>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                        <button
                            wire:click="setMonitoringOutlet(null)"
                            style="padding: 5px 12px; border-radius: 8px; font-size: 11px; font-weight: 800; border: 1px solid {{ is_null($selectedMonitoringOutletId) ? '#2563EB' : '#E5E7EB' }}; cursor: pointer; transition: 0.2s; {{ is_null($selectedMonitoringOutletId) ? 'background: #2563EB; color: #ffffff;' : 'background: #F3F4F6; color: #374151;' }}"
                        >
                            Semua Toko (Konsolidasi)
                        </button>

                        @foreach($this->outlets as $outlet)
                            <button
                                wire:click="setMonitoringOutlet({{ $outlet->id }})"
                                style="padding: 5px 12px; border-radius: 8px; font-size: 11px; font-weight: 800; border: 1px solid {{ $selectedMonitoringOutletId === $outlet->id ? '#2563EB' : '#E5E7EB' }}; cursor: pointer; transition: 0.2s; {{ $selectedMonitoringOutletId === $outlet->id ? 'background: #2563EB; color: #ffffff;' : 'background: #F3F4F6; color: #374151;' }}"
                            >
                                {{ $outlet->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Baris 2: Global Periode Waktu --}}
                <div class="pt-2 border-t border-gray-100 dark:border-gray-800/60">
                    <span class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Filter Periode Transaksi:</span>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                        @foreach($periodLabels as $key => $label)
                            <button
                                wire:click="setPeriod('{{ $key }}')"
                                style="padding: 5px 12px; border-radius: 8px; font-size: 11px; font-weight: 800; border: 1px solid {{ $period === $key ? '#10B981' : '#E5E7EB' }}; cursor: pointer; transition: 0.2s; {{ $period === $key ? 'background: #10B981; color: #ffffff;' : 'background: #F3F4F6; color: #374151;' }}"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 2. KARTU 4 METRIK RINGKAS (TERKONEKSI DENGAN GLOBAL FILTER) --}}
            <div class="p-4 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
                <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px;">
                    
                    {{-- Metrik 1: Penjualan Bersih --}}
                    <div style="padding: 4px;">
                        <span style="font-size: 11px; font-weight: 700; color: #6B7280; text-transform: uppercase; display: block;">
                            Penjualan Bersih
                        </span>
                        <div style="font-size: 17px; font-weight: 900; color: #10B981; margin-top: 2px;">
                            Rp {{ number_format($totalSales, 0, ',', '.') }}
                        </div>
                    </div>

                    {{-- Metrik 2: Penjualan Kotor --}}
                    <div style="padding: 4px;">
                        <span style="font-size: 11px; font-weight: 700; color: #6B7280; text-transform: uppercase; display: block;">
                            Penjualan Kotor
                        </span>
                        <div style="font-size: 17px; font-weight: 900; margin-top: 2px;" class="text-gray-900 dark:text-white">
                            Rp {{ number_format($totalSales, 0, ',', '.') }}
                        </div>
                    </div>

                    {{-- Metrik 3: Rata-rata per Transaksi --}}
                    <div style="padding: 4px;">
                        <span style="font-size: 11px; font-weight: 700; color: #6B7280; text-transform: uppercase; display: block;">
                            Rata-rata / Transaksi
                        </span>
                        <div style="font-size: 17px; font-weight: 900; color: #2563EB; margin-top: 2px;">
                            Rp {{ number_format($avgSales, 0, ',', '.') }}
                        </div>
                    </div>

                    {{-- Metrik 4: Total Transaksi --}}
                    <div style="padding: 4px;">
                        <span style="font-size: 11px; font-weight: 700; color: #6B7280; text-transform: uppercase; display: block;">
                            Total Transaksi
                        </span>
                        <div style="font-size: 17px; font-weight: 900; margin-top: 2px;" class="text-gray-900 dark:text-white">
                            {{ number_format($totalOrders, 0, ',', '.') }} <span style="font-size: 12px; font-weight: 600; color: #6B7280;">Pesanan</span>
                        </div>
                    </div>

                </div>
                @if($totalOrders === 0)
                    <div class="mt-3 pt-2 border-t border-gray-100 dark:border-gray-800 text-center text-xs text-gray-400 italic">
                        Belum ada transaksi tercatat pada periode <span class="font-bold">{{ $periodLabels[$period] ?? 'Hari Ini' }}</span> untuk toko yang dipilih.
                    </div>
                @endif
            </div>

            {{-- 3. WIDGET GRAFIK FILAMENT COMPACT (TINGGI MAX 210PX AGAR HEMAT RUANG) --}}
            <x-filament-widgets::widgets
                :widgets="$this->getVisibleWidgets()"
                :columns="$this->getColumns()"
            />

        </div>
    @endif
</x-filament-panels::page>
