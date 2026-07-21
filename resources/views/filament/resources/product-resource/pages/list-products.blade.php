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
                <div class="relative flex items-center">
                    <x-heroicon-m-magnifying-glass class="absolute w-4 h-4 text-gray-400 pointer-events-none shrink-0" style="left: 12px !important;" />
                    <input
                        wire:model.live.debounce.400ms="search"
                        type="text"
                        placeholder="Cari produk atau tags..."
                        style="padding-left: 36px !important;"
                        class="w-full pr-4 py-2 text-xs md:text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                    />
                </div>
            </div>
        </div>

        {{-- Tabel Desktop (hidden di Mobile) --------------------------- --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
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
                                <div class="flex items-center gap-1.5">
                                    <a
                                        href="{{ \App\Filament\Resources\ProductResource::getUrl('edit', ['record' => $product]) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                                    >
                                        <x-heroicon-m-pencil-square class="w-3.5 h-3.5 text-gray-400" />
                                        Edit
                                    </a>
                                    @if (!auth()->user()?->outlet_id)
                                        <button
                                            type="button"
                                            wire:click="deleteProduct({{ $product->id }})"
                                            wire:confirm="Apakah Anda yakin ingin menghapus permanen produk '{{ $product->name }}' ini?"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-red-600 dark:text-red-400 bg-white dark:bg-gray-800 border border-red-200 dark:border-red-800/60 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/50 transition"
                                            title="Hapus Produk"
                                        >
                                            <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                        </button>
                                    @endif
                                </div>
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
                    <div class="shrink-0 pt-0.5 flex flex-col sm:flex-row gap-1.5 items-end sm:items-center">
                        <a href="{{ \App\Filament\Resources\ProductResource::getUrl('edit', ['record' => $product]) }}" class="px-3 py-1.5 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 font-extrabold text-xs rounded-lg transition border border-gray-300 dark:border-gray-600 inline-flex items-center gap-1 shadow-sm">
                            <x-heroicon-m-pencil-square class="w-3.5 h-3.5 text-blue-600" />
                            <span>Edit</span>
                        </a>
                        @if (!auth()->user()?->outlet_id)
                            <button
                                type="button"
                                wire:click="deleteProduct({{ $product->id }})"
                                wire:confirm="Apakah Anda yakin ingin menghapus permanen produk '{{ $product->name }}' ini?"
                                class="px-2.5 py-1.5 bg-white dark:bg-gray-800 text-red-600 dark:text-red-400 font-extrabold text-xs rounded-lg transition border border-red-200 dark:border-red-800/60 hover:bg-red-50 dark:hover:bg-red-950/50 inline-flex items-center gap-1 shadow-sm"
                                title="Hapus Produk"
                            >
                                <x-heroicon-o-trash class="w-3.5 h-3.5" />
                            </button>
                        @endif
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

    {{-- ================================================================ --}}
    {{-- Modal Pusat Manajemen Massal & Stok (Bulk Manager Studio)         --}}
    {{-- ================================================================ --}}
    @if ($showBulkModal)
        <div 
            class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 overflow-y-auto animate-fade-in"
            style="background-color: rgba(15, 23, 42, 0.75); backdrop-filter: blur(10px);"
            wire:click.self="closeBulkManager"
        >
            <div class="rounded-3xl shadow-2xl w-full max-w-6xl max-h-[94vh] flex flex-col overflow-hidden my-auto transition-all" style="background-color: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
                
                {{-- 1. Modal Header (Sleek & Modern Minimalist) --}}
                <div class="px-5 py-4 sm:px-6 sm:py-4.5 flex items-center justify-between shrink-0 sticky top-0 z-40 gap-3" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 shadow-sm" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #ffffff;">
                            <x-heroicon-m-squares-2x2 class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-extrabold text-base sm:text-lg tracking-tight truncate" style="color: #0f172a;">Pusat Manajemen Stok & Katalog</h3>
                                <span class="hidden sm:inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" style="background-color: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;">
                                    {{ number_format($this->bulkTotalCount) }} Produk
                                </span>
                            </div>
                            <p class="text-xs truncate" style="color: #64748b;">Kelola status aktif dan hapus produk massal dengan mudah & cepat</p>
                        </div>
                    </div>
                    
                    {{-- TOMBOL KELUAR MODERN & KALEM --}}
                    <button 
                        type="button"
                        wire:click="closeBulkManager" 
                        class="flex items-center gap-2 font-bold transition shrink-0 hover:opacity-90 transform active:scale-95"
                        style="background-color: #fee2e2; color: #dc2626; padding: 8px 16px; border-radius: 9999px; font-size: 13px; border: 1px solid #fca5a5; cursor: pointer;"
                        title="Tutup & Keluar dari Pop-up Ini"
                    >
                        <x-heroicon-m-x-mark class="w-4 h-4 stroke-2" />
                        <span>Tutup</span>
                    </button>
                </div>

                {{-- 2. Smart Filter Grid --}}
                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 shrink-0" style="background-color: #ffffff; border-bottom: 1px solid #f1f5f9;">
                    {{-- Cabang Toko --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider mb-1.5" style="color: #64748b;">🏪 Cabang Toko</label>
                        @if (auth()->user()?->outlet_id)
                            <div class="w-full text-xs sm:text-sm font-bold rounded-xl shadow-sm py-2 px-3 flex items-center gap-1.5" style="border: 1px solid #cbd5e1; background-color: #f1f5f9; color: #334155;">
                                <span>🔒 {{ auth()->user()->outlet?->name ?: 'Cabang Saya' }}</span>
                            </div>
                        @else
                            <select wire:model.live="bulkOutletId" class="w-full text-xs sm:text-sm font-semibold rounded-xl shadow-sm py-2 px-3 transition focus:ring-2 focus:ring-blue-500" style="border: 1px solid #cbd5e1; background-color: #f8fafc; color: #1e293b;">
                                <option value="">Semua Cabang Toko</option>
                                @foreach ($this->outlets as $outlet)
                                    <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    {{-- Kondisi Stok --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider mb-1.5" style="color: #64748b;">📦 Kondisi Stok</label>
                        <select wire:model.live="bulkStockStatus" class="w-full text-xs sm:text-sm font-semibold rounded-xl shadow-sm py-2 px-3 transition focus:ring-2 focus:ring-blue-500" style="border: 1px solid #cbd5e1; background-color: #f8fafc; color: #1e293b;">
                            <option value="">Semua Kondisi Stok</option>
                            <option value="habis">Stok Habis (0 pcs)</option>
                            <option value="ada">Ada Stok (> 0 pcs)</option>
                        </select>
                    </div>
                    {{-- Status Aktif --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider mb-1.5" style="color: #64748b;">⚡ Status Produk</label>
                        <select wire:model.live="bulkActiveStatus" class="w-full text-xs sm:text-sm font-semibold rounded-xl shadow-sm py-2 px-3 transition focus:ring-2 focus:ring-blue-500" style="border: 1px solid #cbd5e1; background-color: #f8fafc; color: #1e293b;">
                            <option value="">Semua Status</option>
                            <option value="aktif">Aktif Dijual</option>
                            <option value="nonaktif">Nonaktif / Tersembunyi</option>
                        </select>
                    </div>
                    {{-- Search --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider mb-1.5" style="color: #64748b;">🔍 Cari Nama / SKU</label>
                        <input type="text" wire:model.live.debounce.400ms="bulkSearch" placeholder="Ketik nama atau SKU barang..." class="w-full text-xs sm:text-sm font-semibold rounded-xl shadow-sm py-2 px-3 transition focus:ring-2 focus:ring-blue-500" style="border: 1px solid #cbd5e1; background-color: #f8fafc; color: #1e293b;" />
                    </div>
                </div>

                {{-- 3. Selection Summary Bar --}}
                <div class="px-5 py-2.5 flex items-center justify-between flex-wrap gap-2 shrink-0 transition-all" style="background-color: #f1f5f9; border-bottom: 1px solid #e2e8f0;">
                    <div class="flex items-center gap-2.5 text-xs sm:text-sm">
                        <span style="font-weight: 700; color: #334155;">
                            Ditemukan <span class="px-2 py-0.5 rounded-md font-bold" style="background-color: #3b82f6; color: #ffffff;">{{ number_format($this->bulkTotalCount) }}</span> produk sesuai filter ini.
                        </span>
                        @if ($this->bulkTotalCount > 0 && count($bulkSelectedIds) < $this->bulkTotalCount)
                            <button type="button" wire:click="selectAllMatchingBulkQuery" class="font-bold text-xs px-3 py-1.5 rounded-lg shadow-sm transition hover:opacity-90" style="background-color: #ffffff; color: #2563eb; border: 1px solid #93c5fd; cursor: pointer;">
                                ✓ Pilih Seluruh {{ number_format($this->bulkTotalCount) }} Produk Sekaligus
                            </button>
                        @endif
                    </div>
                    @if (count($bulkSelectedIds) > 0)
                        <button type="button" wire:click="$set('bulkSelectedIds', [])" class="text-xs font-bold px-3 py-1 rounded-lg transition hover:opacity-90" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; cursor: pointer;">
                            Batalkan Pilihan ({{ count($bulkSelectedIds) }})
                        </button>
                    @endif
                </div>

                {{-- 4. PANEL AKSI MASSAL (MODERN, SLEEK, KALEM, PROFESIONAL - TANPA PINDAHKAN KATEGORI) --}}
                @php
                    $breakdown = $this->bulkSelectionBreakdown;
                @endphp
                @if (!empty($breakdown) && ($breakdown['total_products'] ?? 0) > 0)
                    <div class="p-5 shrink-0 shadow-sm animate-fade-in z-20" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <div class="flex flex-col gap-4 max-w-5xl mx-auto">
                            
                            {{-- Rincian Produk Terpilih --}}
                            <div class="rounded-2xl p-4 flex flex-col gap-2.5 shadow-sm" style="background-color: #ffffff; border: 1px solid #cbd5e1; color: #0f172a;">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-2.5 gap-2" style="border-bottom: 1px solid #f1f5f9;">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: #3b82f6;"></span>
                                        <span class="font-bold text-sm sm:text-base" style="color: #0f172a;">
                                            Terpilih <strong style="color: #2563eb;">{{ number_format($breakdown['total_products']) }} Produk</strong>
                                        </span>
                                        <span class="text-xs font-medium" style="color: #64748b;">(Total keseluruhan stok: <strong style="color: #334155;">{{ number_format($breakdown['total_pcs']) }} pcs</strong>)</span>
                                    </div>
                                    <div class="flex items-center gap-3 font-semibold text-xs">
                                        <span class="flex items-center gap-1" style="color: #059669;"><x-heroicon-m-check-circle class="w-4 h-4" /> {{ $breakdown['active_count'] }} Aktif</span>
                                        <span class="flex items-center gap-1" style="color: #d97706;"><x-heroicon-m-pause-circle class="w-4 h-4" /> {{ $breakdown['inactive_count'] }} Nonaktif</span>
                                        <span class="flex items-center gap-1" style="color: #dc2626;"><x-heroicon-m-exclamation-circle class="w-4 h-4" /> {{ $breakdown['out_of_stock_count'] }} Habis</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-wrap pt-0.5 text-xs">
                                    <span style="color: #64748b; font-weight: 600;">Stok per Cabang:</span>
                                    @forelse ($breakdown['stores'] as $storeName => $sData)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full shadow-sm" style="background-color: #f1f5f9; border: 1px solid #cbd5e1; font-weight: 600;">
                                            <span style="color: #475569;">🏪 {{ $storeName }}:</span>
                                            <span style="color: #0f172a; font-weight: 700;">{{ number_format($sData['count']) }} barang</span>
                                            <span style="color: #64748b; font-size: 11px;">({{ number_format($sData['qty']) }} pcs)</span>
                                        </span>
                                    @empty
                                        <span style="color: #64748b; font-style: italic;">Katalog global</span>
                                    @endforelse
                                </div>
                            </div>

                            {{-- 3 TOMBOL AKSI UTAMA (Sangat Rapi, Kalem, dan Profesional) --}}
                            <div>
                                <div class="text-xs font-bold uppercase tracking-wider mb-2.5 flex items-center gap-1.5" style="color: #475569;">
                                    <span>PILIH AKSI UNTUK {{ number_format($breakdown['total_products']) }} PRODUK TERPILIH:</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                                    
                                    {{-- Tombol 1: Aktifkan di Toko --}}
                                    <button
                                        type="button"
                                        wire:click="confirmBulkAction('activate')"
                                        class="p-4 rounded-2xl shadow-md transition-all transform active:scale-[0.98] flex flex-col items-center justify-center text-center hover:opacity-95"
                                        style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.15); cursor: pointer;"
                                    >
                                        <div class="flex items-center justify-center gap-2 font-bold text-sm">
                                            <x-heroicon-m-check-circle class="w-5 h-5 opacity-90" />
                                            <span>Aktifkan di Toko</span>
                                        </div>
                                        <p class="text-[11px] mt-1 font-medium" style="color: rgba(255, 255, 255, 0.85);">Tampilkan kembali ke katalog & kasir</p>
                                    </button>

                                    {{-- Tombol 2: Nonaktifkan Produk --}}
                                    <button
                                        type="button"
                                        wire:click="confirmBulkAction('deactivate')"
                                        class="p-4 rounded-2xl shadow-md transition-all transform active:scale-[0.98] flex flex-col items-center justify-center text-center hover:opacity-95"
                                        style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.15); cursor: pointer;"
                                    >
                                        <div class="flex items-center justify-center gap-2 font-bold text-sm">
                                            <x-heroicon-m-pause-circle class="w-5 h-5 opacity-90" />
                                            <span>Nonaktifkan Produk</span>
                                        </div>
                                        <p class="text-[11px] mt-1 font-medium" style="color: rgba(255, 255, 255, 0.85);">Sembunyikan sementara dari toko</p>
                                    </button>

                                    {{-- Tombol 3: Hapus dari Toko / Hapus Permanen --}}
                                    @php
                                        $activeScopeId = auth()->user()?->outlet_id ?: $this->bulkOutletId;
                                    @endphp
                                    <button
                                        type="button"
                                        wire:click="confirmBulkAction('delete')"
                                        class="p-4 rounded-2xl shadow-md transition-all transform active:scale-[0.98] flex flex-col items-center justify-center text-center hover:opacity-95"
                                        style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.15); cursor: pointer;"
                                    >
                                        <div class="flex items-center justify-center gap-2 font-bold text-sm">
                                            <x-heroicon-m-trash class="w-5 h-5 opacity-90" />
                                            <span>{{ $activeScopeId ? 'Hapus dari Cabang Ini' : 'Hapus Permanen' }}</span>
                                        </div>
                                        <p class="text-[11px] mt-1 font-medium" style="color: rgba(255, 255, 255, 0.85);">{{ $activeScopeId ? 'Lepas produk dari toko terpilih' : 'Bersihkan produk dari database' }}</p>
                                    </button>

                                </div>
                            </div>

                        </div>
                    </div>
                @endif

                {{-- 5. Scrollable Products Table Inside Modal (SELURUH BARIS BISA DIKLIK) --}}
                <div class="flex-1 overflow-y-auto p-0 min-h-[260px] max-h-[50vh]">
                    <table class="w-full text-xs sm:text-sm">
                        <thead class="sticky top-0 z-10 font-bold border-b transition" style="background-color: #f8fafc; color: #64748b; border-color: #e2e8f0;">
                            <tr>
                                <th class="py-3 px-4 text-left w-12">
                                    <input type="checkbox" wire:model.live="bulkSelectAll" class="rounded cursor-pointer w-4 h-4 transition focus:ring-2 focus:ring-blue-500" style="border-color: #cbd5e1;" />
                                </th>
                                <th class="py-3 px-4 text-left">Produk & SKU <span class="text-xs font-normal text-gray-400 ml-1">(💡 Klik baris mana saja untuk menandai)</span></th>
                                <th class="py-3 px-4 text-left">Stok per Cabang</th>
                                <th class="py-3 px-4 text-left">Harga</th>
                                <th class="py-3 px-4 text-left">Kategori</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="divide-color: #f1f5f9;">
                            @forelse ($this->bulkProducts as $prod)
                                @php
                                    $isSelected = in_array((string)$prod->id, $bulkSelectedIds) || in_array($prod->id, $bulkSelectedIds);
                                @endphp
                                <tr 
                                    wire:click="toggleBulkProductSelection({{ $prod->id }})" 
                                    wire:key="bulk-row-{{ $prod->id }}"
                                    class="transition select-none hover:bg-slate-50"
                                    style="cursor: pointer; {{ $isSelected ? 'background-color: #fef9c3 !important; border-left: 4px solid #f59e0b !important;' : 'background-color: #ffffff;' }}"
                                >
                                    <td class="py-3.5 px-4" wire:click.stop="toggleBulkProductSelection({{ $prod->id }})">
                                        <input 
                                            type="checkbox" 
                                            wire:click.stop="toggleBulkProductSelection({{ $prod->id }})" 
                                            @if($isSelected) checked @endif 
                                            class="rounded cursor-pointer w-4 h-4 transition focus:ring-2 focus:ring-blue-500" 
                                            style="border-color: #cbd5e1; accent-color: #d97706;" 
                                        />
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold sm:text-sm leading-snug" style="color: #0f172a;">{{ $prod->name }}</div>
                                        <div class="text-[11px] font-mono mt-0.5" style="color: #64748b;">{{ $prod->sku }}</div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @php
                                            $userOutletId = auth()->user()?->outlet_id;
                                            $activeOutletScope = $userOutletId ?: $this->bulkOutletId;
                                            $invs = $prod->inventories ?? collect();
                                            if ($activeOutletScope) {
                                                $invs = $invs->where('outlet_id', $activeOutletScope);
                                            }
                                            $totalQty = (int) ($activeOutletScope ? $invs->sum('quantity') : ($prod->total_qty ?? $invs->sum('quantity')));
                                        @endphp
                                        <div class="font-bold sm:text-sm" style="color: {{ $totalQty > 0 ? '#059669' : '#dc2626' }};">
                                            {{ $totalQty > 0 ? number_format($totalQty).' pcs' : 'Habis (0 pcs)' }}
                                        </div>
                                        @if(count($invs) > 0 && !$userOutletId && !$this->bulkOutletId)
                                            <div class="flex flex-wrap gap-1.5 mt-1.5">
                                                @foreach($invs as $i)
                                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold" style="background-color: {{ $i->quantity > 0 ? '#eff6ff' : '#f8fafc' }}; color: {{ $i->quantity > 0 ? '#1d4ed8' : '#64748b' }}; border: 1px solid {{ $i->quantity > 0 ? '#bfdbfe' : '#e2e8f0' }};">
                                                        {{ $i->outlet?->name ?: 'Toko' }}: {{ $i->quantity }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 font-bold sm:text-sm tabular-nums" style="color: #0f172a;">Rp {{ number_format($prod->base_price, 0, ',', '.') }}</td>
                                    <td class="py-3.5 px-4 font-semibold" style="color: #64748b;">
                                        <span class="px-2.5 py-1 rounded-full text-xs" style="background-color: #f1f5f9; color: #475569;">
                                            {{ $prod->category?->name ?? 'UMUM' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 px-6 text-center" style="color: #94a3b8;">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <x-heroicon-o-archive-box class="w-10 h-10 text-slate-300 stroke-1" />
                                            <p class="text-sm font-semibold">Tidak ada produk yang cocok dengan filter pencarian di atas.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if ($bulkLoadedCount < $this->bulkTotalCount)
                        <div class="p-4 text-center border-t" style="border-color: #f1f5f9; background-color: #f8fafc;">
                            <button type="button" wire:click="loadMoreBulk" class="px-6 py-2.5 font-bold text-xs sm:text-sm rounded-xl transition shadow-sm hover:bg-slate-200" style="background-color: #e2e8f0; color: #334155; cursor: pointer;">
                                ↓ Muat 50 Produk Berikutnya (Dari Total {{ number_format($this->bulkTotalCount) }})
                            </button>
                        </div>
                    @endif
                </div>

                {{-- 6. Footer Modal (Minimalist & Clean) --}}
                <div class="px-5 py-3.5 border-t flex items-center justify-between flex-wrap gap-3 shrink-0" style="background-color: #f8fafc; border-color: #e2e8f0;">
                    <span class="text-xs font-medium" style="color: #64748b;">💡 Klik baris mana saja pada tabel di atas untuk menandai produk dengan cepat, lalu pilih aksi massal.</span>
                    <button 
                        type="button"
                        wire:click="closeBulkManager" 
                        class="px-5 py-2 font-bold text-xs sm:text-sm rounded-xl shadow-sm flex items-center gap-1.5 transition hover:opacity-90"
                        style="background-color: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; cursor: pointer;"
                    >
                        <x-heroicon-m-x-mark class="w-4 h-4 stroke-2" />
                        <span>Tutup & Keluar</span>
                    </button>
                </div>

                {{-- CUSTOM MODERN CONFIRMATION POP-UP MODAL (SANGAT USER FRIENDLY & LEBIH BAIK DARI BROWSER ALERT) --}}
                @if ($showConfirmModal)
                    <div 
                        class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto animate-fade-in"
                        style="background-color: rgba(15, 23, 42, 0.85); backdrop-filter: blur(12px); z-index: 999999 !important;"
                        wire:click.self="$set('showConfirmModal', false)"
                    >
                        <div class="rounded-3xl shadow-2xl max-w-md w-full p-6 sm:p-7 text-center overflow-hidden transform transition-all animate-scale-up" style="background-color: #ffffff; border: 1px solid #cbd5e1; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);">
                            
                            {{-- Icon Badge --}}
                            @if ($confirmActionType === 'delete')
                                <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner" style="background-color: #fee2e2; border: 2px solid #fecaca; color: #dc2626;">
                                    <x-heroicon-o-trash class="w-8 h-8 stroke-2" />
                                </div>
                            @elseif ($confirmActionType === 'activate')
                                <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner" style="background-color: #d1fae5; border: 2px solid #a7f3d0; color: #059669;">
                                    <x-heroicon-o-check-circle class="w-8 h-8 stroke-2" />
                                </div>
                            @else
                                <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner" style="background-color: #fef3c7; border: 2px solid #fde68a; color: #d97706;">
                                    <x-heroicon-o-pause-circle class="w-8 h-8 stroke-2" />
                                </div>
                            @endif

                            {{-- Title & Message --}}
                            <h3 class="font-extrabold text-lg sm:text-xl tracking-tight mb-2" style="color: #0f172a;">{{ $confirmTitle }}</h3>
                            <p class="text-sm font-medium mb-4 leading-relaxed" style="color: #475569;">{{ $confirmMessage }}</p>

                            {{-- Safe SubMessage Badge --}}
                            @if ($confirmSubMessage)
                                <div class="p-3.5 rounded-2xl text-left text-xs font-semibold mb-6 leading-relaxed shadow-sm" style="background-color: #f8fafc; color: #64748b; border: 1px solid #e2e8f0;">
                                    {{ $confirmSubMessage }}
                                </div>
                            @endif

                            {{-- Action Buttons --}}
                            <div class="flex items-center gap-3">
                                <button 
                                    type="button"
                                    wire:click="$set('showConfirmModal', false)"
                                    class="flex-1 py-3 px-4 rounded-2xl font-bold text-xs sm:text-sm transition transform active:scale-95 hover:bg-slate-200"
                                    style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; cursor: pointer;"
                                >
                                    Batal / Kembali
                                </button>
                                <button 
                                    type="button"
                                    wire:click="executeConfirmedBulkAction"
                                    class="flex-1 py-3 px-4 rounded-2xl font-black text-xs sm:text-sm shadow-lg transition transform active:scale-95 hover:opacity-95 flex items-center justify-center gap-1.5"
                                    style="background: {{ $confirmActionType === 'delete' ? 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)' : ($confirmActionType === 'activate' ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)') }}; color: #ffffff; border: none; cursor: pointer;"
                                >
                                    @if ($confirmActionType === 'delete')
                                        <x-heroicon-m-trash class="w-4 h-4" />
                                    @elseif ($confirmActionType === 'activate')
                                        <x-heroicon-m-check class="w-4 h-4" />
                                    @else
                                        <x-heroicon-m-pause class="w-4 h-4" />
                                    @endif
                                    <span>{{ $confirmButtonText }}</span>
                                </button>
                            </div>

                        </div>
                    </div>
                @endif

            </div>
        </div>
    @endif

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
