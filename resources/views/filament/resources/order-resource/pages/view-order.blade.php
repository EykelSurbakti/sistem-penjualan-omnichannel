<x-filament-panels::page>
    @php
        $order = $this->record;
        $items = $order->items()->with('product')->get();
        $outlet = $order->outlet;
        $customer = $order->customer;
        $cashier = $order->cashier;
    @endphp

    <div x-data="{ showPrintModal: false }" class="space-y-6">

        {{-- HEADER BAR ALA iSELLER --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-gray-200 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Pesanan</span>
                <span class="text-gray-300 dark:text-gray-700">/</span>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                        {{ $order->order_number }}
                    </h1>
                    <span class="px-3 py-1 rounded-full text-xs font-black uppercase {{ $order->payment_status === 'paid' ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/15 text-amber-600 dark:text-amber-400' }}">
                        {{ $order->payment_status === 'paid' ? 'Selesai / Lunas' : 'Belum Lunas' }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <button
                    @click="showPrintModal = true"
                    type="button"
                    class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black text-xs uppercase tracking-wider shadow-md shadow-blue-600/30 transition flex items-center gap-2"
                >
                    <x-heroicon-m-printer class="w-4 h-4" />
                    <span>Print Struk</span>
                </button>
            </div>
        </div>

        {{-- LAYOUT 2 KOLOM (KIRI: RINCIAN PESANAN & TIMELINE | KANAN: INFORMASI UMUM & OUTLET) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- KOLOM KIRI (2/3 WIDTH) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- KARTU 1: RINCIAN PESANAN --}}
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800">
                        <h2 class="text-lg font-black text-gray-900 dark:text-white">Rincian Pesanan</h2>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-0.5">
                            Barang yang terpenuhi ({{ $items->count() }})
                        </p>
                    </div>

                    {{-- TABEL BARANG --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-800 text-[11px] font-black uppercase text-gray-400 dark:text-gray-500 bg-gray-50/50 dark:bg-gray-850/50">
                                    <th class="py-3 px-6">Produk</th>
                                    <th class="py-3 px-4 text-right">Item Price</th>
                                    <th class="py-3 px-4 text-center">Jumlah</th>
                                    <th class="py-3 px-6 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                                @forelse($items as $item)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition">
                                        <td class="py-4 px-6 font-extrabold text-gray-900 dark:text-white">
                                            {{ $item->product->name ?? 'Produk MALIKU' }}
                                        </td>
                                        <td class="py-4 px-4 text-right font-bold text-gray-600 dark:text-gray-300">
                                            Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                        </td>
                                        <td class="py-4 px-4 text-center font-bold text-gray-700 dark:text-gray-300">
                                            x {{ $item->quantity }}
                                        </td>
                                        <td class="py-4 px-6 text-right font-black text-gray-900 dark:text-white">
                                            Rp {{ number_format($item->total_price, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="py-4 px-6 font-bold text-gray-900 dark:text-white">
                                            Item Penjualan Retail MALIKU
                                        </td>
                                        <td class="py-4 px-4 text-right font-bold text-gray-600 dark:text-gray-300">
                                            Rp {{ number_format($order->subtotal, 0, ',', '.') }}
                                        </td>
                                        <td class="py-4 px-4 text-center font-bold text-gray-700 dark:text-gray-300">
                                            x 1
                                        </td>
                                        <td class="py-4 px-6 text-right font-black text-gray-900 dark:text-white">
                                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- RINGKASAN SUB TOTAL & TOTAL --}}
                    <div class="px-6 py-4 bg-gray-50/70 dark:bg-gray-800/40 border-t border-gray-200 dark:border-gray-800 space-y-2 text-sm">
                        <div class="flex justify-between items-center text-gray-600 dark:text-gray-400 font-semibold">
                            <span>Sub Total</span>
                            <span class="font-black text-gray-900 dark:text-white">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-right text-[11px] font-bold text-gray-400 dark:text-gray-500">
                            Harga sudah termasuk pajak
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-700/80 text-base font-black text-gray-900 dark:text-white">
                            <span>Total</span>
                            <span class="text-blue-600 dark:text-blue-400">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm font-bold text-gray-700 dark:text-gray-300">
                            <span>Tunai (Cash)</span>
                            <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- BANNER STATUS ALA iSELLER --}}
                    <div class="p-6 space-y-3 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
                        <div class="p-4 rounded-xl bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0">
                                    <x-heroicon-m-banknotes class="w-5 h-5" />
                                </div>
                                <span class="text-xs sm:text-sm font-extrabold text-blue-900 dark:text-blue-200">
                                    Pesanan ini sudah dibayarkan.
                                </span>
                            </div>
                            <button type="button" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-black transition">
                                Pengembalian
                            </button>
                        </div>

                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700/60 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                <x-heroicon-m-check class="w-5 h-5 font-bold" />
                            </div>
                            <span class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300">
                                Semua barang pada pesanan telah terpenuhi.
                            </span>
                        </div>
                    </div>
                </div>

                {{-- KARTU 2: KOMENTAR & CATATAN INTERNAL --}}
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm space-y-4">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Komentar</h3>
                    <div class="space-y-3">
                        <textarea
                            rows="3"
                            placeholder="Tinggalkan pesan atau catatan internal transaksi..."
                            class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/80 text-gray-900 dark:text-white text-sm p-3 focus:ring-2 focus:ring-blue-600"
                        ></textarea>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-400">Catatan hanya dipakai untuk internal dan tidak akan diperlihatkan ke pelanggan.</span>
                            <button type="button" class="px-4 py-2 rounded-xl bg-gray-900 dark:bg-gray-700 text-white text-xs font-bold hover:bg-blue-600 transition">
                                Tulis
                            </button>
                        </div>
                    </div>
                </div>

                {{-- KARTU 3: TIMELINE RIWAYAT PESANAN --}}
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm space-y-5">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Timeline Riwayat</h3>
                    
                    <div class="relative pl-6 border-l-2 border-blue-500/30 space-y-6">
                        <div class="relative">
                            <div class="absolute -left-[31px] top-1 w-3.5 h-3.5 rounded-full bg-blue-600 border-2 border-white dark:border-gray-900"></div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <p class="text-xs font-extrabold text-gray-900 dark:text-white">
                                    App: {{ $outlet->name ?? 'MALIKU STORE 03' }} • Sejumlah Rp {{ number_format($order->total_amount, 0, ',', '.') }} pembayaran pada Tunai diproses oleh {{ $cashier->name ?? 'Kasir' }}
                                </p>
                                <span class="text-[11px] font-bold text-gray-400">{{ $order->created_at->format('h:i A') }}</span>
                            </div>
                        </div>

                        <div class="relative">
                            <div class="absolute -left-[31px] top-1 w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white dark:border-gray-900"></div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <p class="text-xs font-bold text-gray-700 dark:text-gray-300">
                                    App: {{ $cashier->name ?? 'Kasir' }} membuat pesanan ini
                                </p>
                                <span class="text-[11px] font-bold text-gray-400">{{ $order->created_at->format('h:i A') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- KOLOM KANAN SIDEBAR (1/3 WIDTH) --}}
            <div class="space-y-6">

                {{-- INFORMASI UMUM --}}
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm space-y-4">
                    <h3 class="text-base font-black text-gray-900 dark:text-white pb-3 border-b border-gray-100 dark:border-gray-800">
                        Informasi Umum
                    </h3>

                    <div class="space-y-3.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-gray-400 font-semibold">Tanggal</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $order->created_at->format('M d, Y h:iA') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400 font-semibold">Referensi</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $order->order_number }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-semibold">Status</span>
                            <span class="px-2.5 py-0.5 rounded-md bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 font-extrabold">
                                Selesai
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400 font-semibold">Channel</span>
                            <span class="font-bold text-gray-900 dark:text-white">Point of Sale (POS)</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400 font-semibold">Kasir</span>
                            <span class="font-bold text-blue-600 dark:text-blue-400">{{ $cashier->name ?? 'Nike Kasir' }}</span>
                        </div>
                    </div>
                </div>

                {{-- ALAMAT OUTLET --}}
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm space-y-3.5">
                    <h3 class="text-base font-black text-gray-900 dark:text-white pb-3 border-b border-gray-100 dark:border-gray-800">
                        Alamat Outlet
                    </h3>

                    <div class="flex items-start gap-2.5">
                        <x-heroicon-m-map-pin class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" />
                        <div>
                            <p class="text-xs font-bold text-gray-900 dark:text-white">
                                {{ $outlet->name ?? 'MALIKU STORE 03' }}
                            </p>
                            <p class="text-[11px] text-gray-500 mt-0.5">Air Hitam, Lampung Barat</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 pt-2 border-t border-gray-100 dark:border-gray-800">
                        <x-heroicon-m-computer-desktop class="w-4 h-4 text-gray-400" />
                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">Register #12 - POS MALIKU</span>
                    </div>
                </div>

                {{-- PELANGGAN --}}
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm space-y-3">
                    <h3 class="text-base font-black text-gray-900 dark:text-white pb-2 border-b border-gray-100 dark:border-gray-800">
                        Pelanggan
                    </h3>
                    <div class="flex items-center gap-2.5">
                        <x-heroicon-m-user class="w-4 h-4 text-gray-400" />
                        <span class="text-xs font-bold text-gray-800 dark:text-gray-200">
                            {{ $customer->name ?? 'Tidak ada pelanggan spesifik' }}
                        </span>
                    </div>
                </div>

                {{-- ALAMAT --}}
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm space-y-3">
                    <h3 class="text-base font-black text-gray-900 dark:text-white pb-2 border-b border-gray-100 dark:border-gray-800">
                        Alamat
                    </h3>
                    <div class="flex items-center gap-2.5 text-xs text-gray-500 dark:text-gray-400">
                        <x-heroicon-m-map-pin class="w-4 h-4 text-gray-400" />
                        <span>Tidak ada alamat spesifik (POS Retail)</span>
                    </div>
                </div>

            </div>

        </div>

        {{-- MODAL PRINT STRUK --}}
        <div x-show="showPrintModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 backdrop-blur-sm p-4">
            <div @click.away="showPrintModal = false" class="bg-white dark:bg-gray-900 rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-800 text-center space-y-4">
                <h3 class="text-lg font-black text-gray-900 dark:text-white">Preview Struk Pesanan {{ $order->order_number }}</h3>
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-950 font-mono text-xs text-left space-y-2 border border-gray-200 dark:border-gray-800 text-gray-900 dark:text-gray-100">
                    <div class="text-center font-bold pb-2 border-b border-dashed border-gray-300 dark:border-gray-700">
                        <p class="text-sm">MALIKU COMMERCE</p>
                        <p class="text-[10px] text-gray-500">{{ $outlet->name ?? 'MALIKU STORE 03' }}</p>
                    </div>
                    <div class="flex justify-between">
                        <span>No: {{ $order->order_number }}</span>
                        <span>{{ $order->created_at->format('d/m/y H:i') }}</span>
                    </div>
                    <div class="py-2 border-y border-dashed border-gray-300 dark:border-gray-700 space-y-1">
                        @foreach($items as $item)
                            <div class="flex justify-between">
                                <span>{{ $item->quantity }}x {{ $item->product->name ?? 'Produk' }}</span>
                                <span>Rp {{ number_format($item->total_price, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-between font-bold pt-1">
                        <span>TOTAL TUNAI:</span>
                        <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button
                        @click="showPrintModal = false"
                        class="w-1/2 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 font-bold text-xs text-gray-700 dark:text-gray-300"
                    >
                        Tutup
                    </button>
                    <button
                        @click="window.print(); showPrintModal = false"
                        class="w-1/2 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 font-extrabold text-xs text-white shadow-md shadow-blue-600/30"
                    >
                        Cetak Sekarang
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
