<x-filament-panels::page>
    {{-- ================================================================ --}}
    {{-- Panel Utama                                                       --}}
    {{-- ================================================================ --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">

        {{-- Bar Pilihan Cabang Toko (Store Selector) ------------------------- --}}
        @if (!auth()->user()?->outlet_id)
            <div class="bg-gray-50 dark:bg-gray-800/70 border-b border-gray-200 dark:border-gray-700 px-4 py-3 flex items-center justify-between flex-col sm:flex-row gap-3">
                {{-- Tombol Pill untuk Desktop --}}
                <div class="hidden sm:flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5 w-full sm:w-auto">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mr-1 shrink-0">
                        Cabang:
                    </span>
                    <button
                        wire:click="$set('outletId', null)"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-extrabold transition-all shrink-0 border {{ empty($outletId) ? '!bg-gray-900 !text-white !border-gray-900 dark:!bg-white dark:!text-gray-900 shadow-sm' : '!bg-white !text-gray-700 !border-gray-300 hover:!bg-gray-100 dark:!bg-gray-800 dark:!text-gray-200 dark:!border-gray-600' }}"
                        @if(empty($outletId)) style="background-color: #111827 !important; color: #ffffff !important; border-color: #111827 !important;" @endif
                    >
                        🏬 Semua Toko (Konsolidasi)
                    </button>
                    @foreach ($this->outlets as $outlet)
                        <button
                            wire:click="$set('outletId', {{ $outlet->id }})"
                            class="px-3.5 py-1.5 rounded-lg text-xs font-extrabold transition-all shrink-0 border {{ $outletId == $outlet->id ? '!bg-gray-900 !text-white !border-gray-900 dark:!bg-white dark:!text-gray-900 shadow-sm' : '!bg-white !text-gray-700 !border-gray-300 hover:!bg-gray-100 dark:!bg-gray-800 dark:!text-gray-200 dark:!border-gray-600' }}"
                            @if($outletId == $outlet->id) style="background-color: #111827 !important; color: #ffffff !important; border-color: #111827 !important;" @endif
                        >
                            🏪 {{ $outlet->name }}
                        </button>
                    @endforeach
                </div>

                {{-- Dropdown Select khusus Mobile (agak tidak kepotong & mudah ditap jempol) --}}
                <div class="block sm:hidden w-full">
                    <select
                        wire:model.live="outletId"
                        class="w-full text-xs font-extrabold border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-blue-500 shadow-sm"
                    >
                        <option value="">🏬 Semua Toko (Konsolidasi)</option>
                        @foreach ($this->outlets as $outlet)
                            <option value="{{ $outlet->id }}">🏪 {{ $outlet->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        {{-- Tab Bar + Filter ------------------------------------------- --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 border-b border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50/50 dark:bg-gray-800/30 flex-wrap">

            {{-- Tab Buttons --}}
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar max-w-full py-0.5">
                @foreach ([
                    'semua'    => ['label' => 'Semua Produk', 'count' => $this->totalCount],
                    'aktif'    => ['label' => 'Aktif',        'count' => $this->aktifCount],
                    'nonaktif' => ['label' => 'Nonaktif',     'count' => $this->nonaktifCount],
                ] as $tab => $meta)
                    <button
                        wire:click="setTab('{{ $tab }}')"
                        class="
                            flex items-center gap-1.5 px-3.5 py-2 text-xs md:text-sm font-bold rounded-lg transition whitespace-nowrap shrink-0 border
                            {{ $tabFilter === $tab
                                ? '!bg-gray-900 !text-white !border-gray-900 dark:!bg-white dark:!text-gray-900 shadow-sm'
                                : '!bg-white dark:!bg-gray-800 !text-gray-600 dark:!text-gray-300 !border-gray-300 dark:!border-gray-700 hover:!bg-gray-100 dark:hover:!bg-gray-700' }}
                        "
                        @if($tabFilter === $tab) style="background-color: #111827 !important; color: #ffffff !important; border-color: #111827 !important;" @endif
                    >
                        <span>{{ $meta['label'] }}</span>
                        <span class="
                            text-[11px] font-black px-2 py-0.5 rounded-full
                            {{ $tabFilter === $tab
                                ? '!bg-white/20 !text-white'
                                : '!bg-gray-100 !text-gray-700 dark:!bg-gray-700 dark:!text-gray-300' }}
                        ">
                            {{ number_format($meta['count']) }}
                        </span>
                    </button>
                @endforeach
            </div>

            {{-- Pencarian --}}
            <div class="w-full lg:w-72 shrink-0">
                <div class="relative">
                    <x-heroicon-m-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
                    <input
                        wire:model.live.debounce.400ms="search"
                        type="text"
                        placeholder="Cari produk atau tags..."
                        class="w-full pl-9 pr-4 py-2 text-xs md:text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                    />
                </div>
            </div>
        </div>

        {{-- Tabel Desktop (hidden di Mobile) --------------------------- --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                        <th class="px-4 py-3 text-left w-8">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600" />
                        </th>
                        <th class="px-4 py-3 text-left" style="min-width:260px">Nama</th>
                        <th class="px-4 py-3 text-left" style="min-width:240px">Inventaris & Stok Toko</th>
                        <th class="px-4 py-3 text-left" style="min-width:110px">Harga</th>
                        <th class="px-4 py-3 text-left" style="min-width:110px">Kategori</th>
                        <th class="px-4 py-3 text-left" style="min-width:145px">Tanggal Dibuat</th>
                        <th class="px-4 py-3 text-left" style="min-width:100px">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($this->products as $product)
                        <tr
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors"
                            wire:key="prod-dt-{{ $product->id }}"
                        >
                            {{-- Checkbox --}}
                            <td class="px-4 py-3">
                                <input type="checkbox" class="rounded border-gray-300 text-blue-600" />
                            </td>

                            {{-- Nama + SKU dengan thumbnail icon iSeller --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center shrink-0 text-gray-400 dark:text-gray-500">
                                        <x-heroicon-o-cube class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <a href="{{ \App\Filament\Resources\ProductResource::getUrl('edit', ['record' => $product]) }}"
                                           class="font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 block leading-tight">
                                            {{ $product->name }}
                                        </a>
                                        <span class="text-xs text-gray-400 dark:text-gray-500 font-mono tracking-wide">
                                            {{ $product->sku }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- Inventaris & Stok Toko lengkap breakdown --}}
                            <td class="px-4 py-3">
                                @php
                                    $userOutletId = auth()->user()?->outlet_id;
                                    $activeOutletId = $userOutletId ?: $this->outletId;
                                    $invs = $product->inventories ?? collect();
                                    $filteredInv = $activeOutletId ? $invs->where('outlet_id', $activeOutletId)->first() : null;
                                    $qty = $activeOutletId ? ($filteredInv ? (int)$filteredInv->quantity : 0) : (int)($product->total_qty ?? $invs->sum('quantity'));
                                @endphp
                                <div class="font-bold text-gray-900 dark:text-white text-sm">
                                    @if ($qty > 0)
                                        <span class="text-emerald-600 dark:text-emerald-400 font-extrabold">{{ number_format($qty) }} pcs</span> dalam stok
                                    @else
                                        <span class="text-red-600 dark:text-red-400 font-extrabold">Stok habis (0 pcs)</span>
                                    @endif
                                </div>
                                @if(count($invs) > 0 && !$userOutletId && !$this->outletId)
                                    <div class="flex flex-wrap gap-1 mt-1.5">
                                        @foreach($invs as $inv)
                                            @php
                                                $invQty = (int)$inv->quantity;
                                            @endphp
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold border {{ $invQty > 0 ? 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800' : 'bg-gray-100 text-gray-500 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700' }}">
                                                <span>🏪 {{ $inv->outlet?->name ?: 'Toko' }}:</span>
                                                <span class="font-black {{ $invQty <= 0 ? 'text-red-500' : '' }}">{{ number_format($invQty) }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>

                            {{-- Harga --}}
                            <td class="px-4 py-3 font-semibold text-gray-800 dark:text-gray-100 tabular-nums">
                                {{ number_format($product->base_price, 0, ',', '.') }}
                            </td>

                            {{-- Kategori --}}
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                {{ $product->category?->name ?? 'UMUM' }}
                            </td>

                            {{-- Tanggal Dibuat --}}
                            <td class="px-4 py-3 text-gray-400 dark:text-gray-500 text-xs">
                                {{ $product->created_at?->format('d/m/Y H:i') ?? '-' }}
                            </td>

                            {{-- Aksi --}}
                            <td class="px-4 py-3">
                                <a
                                    href="{{ \App\Filament\Resources\ProductResource::getUrl('edit', ['record' => $product]) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                                >
                                    <x-heroicon-m-pencil-square class="w-3.5 h-3.5 text-gray-400" />
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-20 text-center text-gray-400 dark:text-gray-500">
                                <x-heroicon-o-cube class="mx-auto w-12 h-12 mb-3 opacity-30" />
                                <p class="text-sm font-medium">Tidak ada produk ditemukan</p>
                                <p class="text-xs mt-1">Coba ganti kata kunci pencarian atau tab filter di atas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card List (block di Mobile, hidden di Desktop) ------ --}}
        <div class="block md:hidden divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($this->products as $product)
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-800/40 transition flex items-start justify-between gap-3" wire:key="prod-mb-{{ $product->id }}">
                    <div class="flex items-start gap-3 min-w-0 flex-1">
                        <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center shrink-0 text-gray-400 dark:text-gray-500 mt-0.5">
                            <x-heroicon-o-cube class="w-5 h-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <a href="{{ \App\Filament\Resources\ProductResource::getUrl('edit', ['record' => $product]) }}" class="font-bold text-sm text-gray-900 dark:text-white hover:text-blue-600 block leading-tight truncate">
                                {{ $product->name }}
                            </a>
                            <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                <span class="text-[11px] font-mono font-semibold text-gray-500 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded border border-gray-200 dark:border-gray-700">
                                    {{ $product->sku }}
                                </span>
                                <span class="text-xs font-extrabold text-blue-600 dark:text-blue-400">
                                    Rp {{ number_format($product->base_price, 0, ',', '.') }}
                                </span>
                                <span class="text-[10px] font-medium text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800/50 px-1.5 py-0.5 rounded">
                                    {{ $product->category?->name ?? 'UMUM' }}
                                </span>
                            </div>
                            {{-- Stok di Mobile --}}
                            <div class="mt-2.5">
                                @php
                                    $userOutletId = auth()->user()?->outlet_id;
                                    $activeOutletId = $userOutletId ?: $this->outletId;
                                    $invs = $product->inventories ?? collect();
                                    $filteredInv = $activeOutletId ? $invs->where('outlet_id', $activeOutletId)->first() : null;
                                    $qty = $activeOutletId ? ($filteredInv ? (int)$filteredInv->quantity : 0) : (int)($product->total_qty ?? $invs->sum('quantity'));
                                @endphp
                                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs font-bold {{ $qty > 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-red-50 text-red-700 dark:bg-red-950/50 dark:text-red-300 border border-red-200 dark:border-red-800' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $qty > 0 ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                    <span>{{ $qty > 0 ? number_format($qty).' pcs dalam stok' : 'Stok habis (0 pcs)' }}</span>
                                </div>
                                @if(count($invs) > 0 && !$userOutletId && !$this->outletId)
                                    <div class="flex flex-wrap gap-1 mt-1.5">
                                        @foreach($invs as $inv)
                                            @php $invQty = (int)$inv->quantity; @endphp
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $invQty > 0 ? 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800' : 'bg-gray-100 text-gray-500 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700' }}">
                                                <span>{{ $inv->outlet?->name ?: 'Toko' }}:</span>
                                                <span class="font-black {{ $invQty <= 0 ? 'text-red-500' : '' }}">{{ number_format($invQty) }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="shrink-0 pt-0.5">
                        <a href="{{ \App\Filament\Resources\ProductResource::getUrl('edit', ['record' => $product]) }}" class="px-3 py-1.5 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 font-extrabold text-xs rounded-lg transition border border-gray-300 dark:border-gray-600 inline-flex items-center gap-1 shadow-sm">
                            <x-heroicon-m-pencil-square class="w-3.5 h-3.5 text-blue-600" />
                            <span>Edit</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-gray-400 dark:text-gray-500">
                    <x-heroicon-o-cube class="mx-auto w-12 h-12 mb-3 opacity-30" />
                    <p class="text-sm font-medium">Tidak ada produk ditemukan</p>
                </div>
            @endforelse
        </div>

        {{-- Footer ------------------------------------------------------ --}}
        <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
            <span>
                Menampilkan <strong class="text-gray-700 dark:text-gray-200">{{ number_format(min($loadedCount, $this->currentTabTotal)) }}</strong>
                dari <strong class="text-gray-700 dark:text-gray-200">{{ number_format($this->currentTabTotal) }}</strong> produk
            </span>

            @if ($loadedCount < $this->currentTabTotal)
                <span id="infinite-scroll-sentinel" class="w-1 h-1 inline-block" wire:loading.remove wire:target="loadMore"></span>
                <span wire:loading wire:target="loadMore" class="flex items-center gap-1.5 text-blue-600 dark:text-blue-400">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    Memuat lebih banyak...
                </span>
            @else
                <span class="text-green-600 dark:text-green-400 font-semibold">✓ Semua produk telah dimuat</span>
            @endif
        </div>
    </div>

    {{-- Infinite Scroll Script ------------------------------------------ --}}
    @push('scripts')
    <script>
    (function () {
        let observer = null;

        function setup() {
            if (observer) { observer.disconnect(); observer = null; }
            const el = document.getElementById('infinite-scroll-sentinel');
            if (!el) return;
            observer = new IntersectionObserver(function (entries) {
                if (entries[0].isIntersecting) {
                    @this.call('loadMore');
                }
            }, { rootMargin: '400px', threshold: 0 });
            observer.observe(el);
        }

        document.addEventListener('DOMContentLoaded', setup);

        if (typeof Livewire !== 'undefined') {
            Livewire.hook('commit', ({ succeed }) => {
                succeed(() => setTimeout(setup, 100));
            });
        }
    })();
    </script>
    @endpush
</x-filament-panels::page>
