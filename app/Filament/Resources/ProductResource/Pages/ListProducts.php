<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\Url;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    // Limit produk yang ditampilkan — bertambah saat loadMore()
    public int $loadedCount = 50;

    // Tab aktif custom: semua | aktif | nonaktif
    #[Url]
    public string $tabFilter = 'semua';

    // Pencarian
    #[Url]
    public string $search = '';

    // Filter Cabang Toko
    #[Url]
    public ?int $outletId = null;

    // Bulk & Single Selection untuk fitur Hapus Massal di Tabel Utama
    public array $selectedProducts = [];
    public bool $selectAll = false;

    // ==========================================
    // Bulk Manager Studio State & Filters
    // ==========================================
    public bool $showBulkModal = false;
    public string $bulkSearch = '';
    public ?int $bulkCategoryId = null;
    public ?string $bulkYear = null;
    public ?int $bulkOutletId = null;
    public ?string $bulkStockStatus = null; // 'habis', 'ada'
    public ?string $bulkActiveStatus = null; // 'aktif', 'nonaktif'
    public ?int $bulkTargetCategoryId = null;

    public array $bulkSelectedIds = [];
    public bool $bulkSelectAll = false;
    public int $bulkLoadedCount = 50;

    // Custom Confirmation Dialog State
    public bool $showConfirmModal = false;
    public string $confirmActionType = '';
    public string $confirmTitle = '';
    public string $confirmMessage = '';
    public string $confirmSubMessage = '';
    public string $confirmButtonText = '';
    public string $confirmButtonColor = '';

    public function getOutletsProperty()
    {
        return \App\Models\Outlet::where('is_active', true)->get();
    }

    public function getCategoriesProperty()
    {
        return \App\Models\Category::orderBy('name')->get();
    }

    public function getAvailableYearsProperty()
    {
        $years = Product::selectRaw('YEAR(created_at) as yr')
            ->whereNotNull('created_at')
            ->distinct()
            ->orderBy('yr', 'desc')
            ->pluck('yr')
            ->filter()
            ->toArray();
        return !empty($years) ? $years : [date('Y')];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('bulk_manager')
                ->label('⚡ Manajemen Massal & Stok')
                ->icon('heroicon-m-rectangle-stack')
                ->color('warning')
                ->action(fn () => $this->openBulkManager()),
            Actions\CreateAction::make()
                ->label('+ Tambah Produk')
                ->icon('heroicon-m-plus-circle'),
        ];
    }

    /**
     * Ambil produk sesuai tab, pencarian, dan limit.
     */
    protected function getBaseProductQuery()
    {
        $query = ProductResource::getEloquentQuery()->with(['category', 'inventories.outlet']);

        // 1. Jika yang login adalah Kasir / Staf Cabang (punya outlet_id), HANYA tampilkan produk cabang mereka
        if (auth()->check() && auth()->user()->outlet_id) {
            $userOutletId = auth()->user()->outlet_id;
            $query->where(function ($q) use ($userOutletId) {
                $q->where('outlet_id', $userOutletId)
                  ->orWhereHas('inventories', fn($i) => $i->where('outlet_id', $userOutletId));
            });
        }
        // 2. Jika Master Admin memilih dropdown filter toko ($this->outletId), filter ke toko tersebut
        elseif ($this->outletId) {
            $selectedOutletId = $this->outletId;
            $query->where(function ($q) use ($selectedOutletId) {
                $q->where('outlet_id', $selectedOutletId)
                  ->orWhereHas('inventories', fn($i) => $i->where('outlet_id', $selectedOutletId));
            });
        }

        return $query;
    }

    public function getProductsProperty()
    {
        $query = $this->getBaseProductQuery()
            ->orderBy('created_at', 'desc');

        if ($this->tabFilter === 'aktif') {
            $query->where('is_active', true);
        } elseif ($this->tabFilter === 'nonaktif') {
            $query->where('is_active', false);
        }

        if ($this->search !== '') {
            $keyword = $this->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('sku',  'like', "%{$keyword}%");
            });
        }

        return $query->limit($this->loadedCount)->get();
    }

    public function getTotalCountProperty(): int
    {
        return $this->getBaseProductQuery()->count();
    }

    public function getAktifCountProperty(): int
    {
        return $this->getBaseProductQuery()->where('is_active', true)->count();
    }

    public function getNonaktifCountProperty(): int
    {
        return $this->getBaseProductQuery()->where('is_active', false)->count();
    }

    public function getCurrentTabTotalProperty(): int
    {
        $query = $this->getBaseProductQuery();

        if ($this->tabFilter === 'aktif') {
            $query->where('is_active', true);
        } elseif ($this->tabFilter === 'nonaktif') {
            $query->where('is_active', false);
        }

        if ($this->search !== '') {
            $keyword = $this->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('sku',  'like', "%{$keyword}%");
            });
        }

        return $query->count();
    }

    /**
     * Muat 50 produk berikutnya.
     */
    public function loadMore(): void
    {
        $this->loadedCount += 50;
    }

    /**
     * Ganti tab.
     */
    public function setTab(string $tab): void
    {
        $this->tabFilter   = $tab;
        $this->loadedCount = 50;
        $this->selectedProducts = [];
        $this->selectAll = false;
    }

    public function updatedSearch(): void
    {
        $this->loadedCount = 50;
        $this->selectedProducts = [];
        $this->selectAll = false;
    }

    public function updatedOutletId(): void
    {
        $this->loadedCount = 50;
        $this->selectedProducts = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedProducts = $this->products->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedProducts = [];
        }
    }

    public function updatedSelectedProducts(): void
    {
        $loadedIds = $this->products->pluck('id')->map(fn($id) => (string) $id)->toArray();
        $this->selectAll = count($loadedIds) > 0 && count(array_intersect($loadedIds, $this->selectedProducts)) === count($loadedIds);
    }

    /**
     * Hapus satu produk by ID (Khusus Admin / Master)
     */
    public function deleteProduct(int $id): void
    {
        if (auth()->check() && auth()->user()->outlet_id) {
            \Filament\Notifications\Notification::make()
                ->title('Akses Ditolak')
                ->body('Hanya Master Admin yang berhak menghapus produk.')
                ->danger()
                ->send();
            return;
        }

        $product = Product::find($id);
        if (!$product) {
            return;
        }

        try {
            $product->delete();
            $this->selectedProducts = array_values(array_diff($this->selectedProducts, [(string)$id, $id]));

            \Filament\Notifications\Notification::make()
                ->title('Produk Berhasil Dihapus')
                ->body("Produk \"{$product->name}\" beserta data stoknya telah dihapus permanen.")
                ->success()
                ->send();
        } catch (\Illuminate\Database\QueryException $e) {
            \Filament\Notifications\Notification::make()
                ->title('Gagal Menghapus')
                ->body("Produk \"{$product->name}\" tidak dapat dihapus karena sudah memiliki riwayat transaksi pesanan di sistem penjualan.")
                ->danger()
                ->send();
        }
    }

    /**
     * Hapus banyak produk terpilih sekaligus (Khusus Admin / Master)
     */
    public function bulkDelete(): void
    {
        if (auth()->check() && auth()->user()->outlet_id) {
            \Filament\Notifications\Notification::make()
                ->title('Akses Ditolak')
                ->body('Hanya Master Admin yang berhak melakukan penghapusan massal.')
                ->danger()
                ->send();
            return;
        }

        if (empty($this->selectedProducts)) {
            \Filament\Notifications\Notification::make()
                ->title('Pilih Produk')
                ->body('Silakan centang produk yang ingin dihapus terlebih dahulu.')
                ->warning()
                ->send();
            return;
        }

        $products = Product::whereIn('id', $this->selectedProducts)->get();
        $deletedCount = 0;
        $blockedProducts = [];

        foreach ($products as $product) {
            try {
                $product->delete();
                $deletedCount++;
            } catch (\Illuminate\Database\QueryException $e) {
                $blockedProducts[] = $product->name;
            }
        }

        $this->selectedProducts = [];
        $this->selectAll = false;

        if ($deletedCount > 0) {
            \Filament\Notifications\Notification::make()
                ->title('Penghapusan Massal Selesai')
                ->body("Berhasil menghapus {$deletedCount} produk terpilih.")
                ->success()
                ->send();
        }

        if (!empty($blockedProducts)) {
            $blockedNames = implode(', ', array_slice($blockedProducts, 0, 3));
            if (count($blockedProducts) > 3) {
                $blockedNames .= ' dan ' . (count($blockedProducts) - 3) . ' lainnya';
            }
            \Filament\Notifications\Notification::make()
                ->title('Sebagian Produk Gagal Dihapus')
                ->body("Sebanyak " . count($blockedProducts) . " produk ({$blockedNames}) tidak dapat dihapus karena memiliki riwayat pesanan penjualan.")
                ->warning()
                ->send();
        }
    }

    // ==========================================
    // Bulk Manager Studio Methods
    // ==========================================
    public function openBulkManager(): void
    {
        $this->showBulkModal = true;
        $this->bulkSearch = '';
        $this->bulkCategoryId = null;
        $this->bulkYear = null;
        $this->bulkOutletId = auth()->check() && auth()->user()->outlet_id ? auth()->user()->outlet_id : null;
        $this->bulkStockStatus = null;
        $this->bulkActiveStatus = null;
        $this->bulkSelectedIds = [];
        $this->bulkSelectAll = false;
        $this->bulkLoadedCount = 50;
    }

    public function closeBulkManager(): void
    {
        $this->showBulkModal = false;
        $this->bulkSelectedIds = [];
        $this->bulkSelectAll = false;
        $this->showConfirmModal = false;
    }

    protected function getBulkFilteredProductsQuery()
    {
        $query = Product::query()->with(['category', 'inventories.outlet', 'outlet']);

        if ($this->bulkCategoryId) {
            $query->where('category_id', $this->bulkCategoryId);
        }

        if ($this->bulkYear) {
            $query->whereYear('created_at', $this->bulkYear);
        }

        $activeOutletScope = auth()->check() && auth()->user()->outlet_id ? auth()->user()->outlet_id : $this->bulkOutletId;

        if ($activeOutletScope) {
            $query->where(function ($q) use ($activeOutletScope) {
                $q->where('outlet_id', $activeOutletScope)
                  ->orWhereHas('inventories', fn($i) => $i->where('outlet_id', $activeOutletScope));
            });
        }

        if ($this->bulkStockStatus === 'habis') {
            if ($activeOutletScope) {
                $query->whereDoesntHave('inventories', fn($q) => $q->where('outlet_id', $activeOutletScope)->where('quantity', '>', 0));
            } else {
                $query->whereDoesntHave('inventories', fn($q) => $q->where('quantity', '>', 0));
            }
        } elseif ($this->bulkStockStatus === 'ada') {
            if ($activeOutletScope) {
                $query->whereHas('inventories', fn($q) => $q->where('outlet_id', $activeOutletScope)->where('quantity', '>', 0));
            } else {
                $query->whereHas('inventories', fn($q) => $q->where('quantity', '>', 0));
            }
        }

        if ($this->bulkActiveStatus === 'aktif') {
            $query->where('is_active', true);
        } elseif ($this->bulkActiveStatus === 'nonaktif') {
            $query->where('is_active', false);
        }

        if ($this->bulkSearch !== '') {
            $kw = $this->bulkSearch;
            $query->where(function ($q) use ($kw) {
                $q->where('name', 'like', "%{$kw}%")
                  ->orWhere('sku', 'like', "%{$kw}%");
            });
        }

        return $query;
    }

    public function getBulkProductsProperty()
    {
        return $this->getBulkFilteredProductsQuery()
            ->orderBy('created_at', 'desc')
            ->limit($this->bulkLoadedCount)
            ->get();
    }

    public function getBulkTotalCountProperty(): int
    {
        return $this->getBulkFilteredProductsQuery()->count();
    }

    public function loadMoreBulk(): void
    {
        $this->bulkLoadedCount += 50;
    }

    public function updatedBulkSearch(): void { $this->resetBulkSelection(); }
    public function updatedBulkCategoryId(): void { $this->resetBulkSelection(); }
    public function updatedBulkYear(): void { $this->resetBulkSelection(); }
    public function updatedBulkOutletId(): void { $this->resetBulkSelection(); }
    public function updatedBulkStockStatus(): void { $this->resetBulkSelection(); }
    public function updatedBulkActiveStatus(): void { $this->resetBulkSelection(); }

    protected function resetBulkSelection(): void
    {
        $this->bulkLoadedCount = 50;
        $this->bulkSelectedIds = [];
        $this->bulkSelectAll = false;
    }

    public function updatedBulkSelectAll($value): void
    {
        if ($value) {
            $this->bulkSelectedIds = $this->bulkProducts->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->bulkSelectedIds = [];
        }
    }

    public function selectAllMatchingBulkQuery(): void
    {
        $this->bulkSelectedIds = $this->getBulkFilteredProductsQuery()
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();
        $this->bulkSelectAll = true;
    }

    public function toggleBulkProductSelection($id): void
    {
        $idStr = (string) $id;
        if (in_array($idStr, $this->bulkSelectedIds)) {
            $this->bulkSelectedIds = array_diff($this->bulkSelectedIds, [$idStr]);
            $this->bulkSelectAll = false;
        } else {
            $this->bulkSelectedIds[] = $idStr;
        }
    }

    /**
     * Hitung Rincian Detail Produk Terpilih by Cabang Toko & Status
     */
    public function getBulkSelectionBreakdownProperty(): array
    {
        if (empty($this->bulkSelectedIds)) {
            return [];
        }

        $products = Product::whereIn('id', $this->bulkSelectedIds)
            ->with(['inventories.outlet', 'outlet'])
            ->get();

        $storeBreakdown = [];
        $totalPcsGlobal = 0;
        $outOfStockCount = 0;
        $activeCount = 0;
        $inactiveCount = 0;

        foreach ($this->outlets as $out) {
            $storeBreakdown[$out->name] = ['count' => 0, 'qty' => 0];
        }

        $activeOutletScope = (auth()->check() && auth()->user()->outlet_id) ? auth()->user()->outlet_id : ($this->bulkOutletId ?: null);

        foreach ($products as $p) {
            if ($p->is_active) { $activeCount++; } else { $inactiveCount++; }

            $pTotalQty = 0;
            if ($p->inventories && count($p->inventories) > 0) {
                foreach ($p->inventories as $inv) {
                    if ($activeOutletScope && $inv->outlet_id != $activeOutletScope) {
                        continue;
                    }
                    $qty = (int) $inv->quantity;
                    $pTotalQty += $qty;
                    $storeName = $inv->outlet?->name ?: 'Toko Lain';
                    if (!isset($storeBreakdown[$storeName])) {
                        $storeBreakdown[$storeName] = ['count' => 0, 'qty' => 0];
                    }
                    $storeBreakdown[$storeName]['count']++;
                    $storeBreakdown[$storeName]['qty'] += $qty;
                }
            } else {
                if ($p->outlet) {
                    if (!$activeOutletScope || $p->outlet_id == $activeOutletScope) {
                        $storeName = $p->outlet->name;
                        if (!isset($storeBreakdown[$storeName])) {
                            $storeBreakdown[$storeName] = ['count' => 0, 'qty' => 0];
                        }
                        $storeBreakdown[$storeName]['count']++;
                    }
                }
            }

            $totalPcsGlobal += $pTotalQty;
            if ($pTotalQty <= 0) {
                $outOfStockCount++;
            }
        }

        $filteredStores = array_filter($storeBreakdown, fn($s) => $s['count'] > 0);

        return [
            'total_products' => count($products),
            'total_pcs' => $totalPcsGlobal,
            'out_of_stock_count' => $outOfStockCount,
            'active_count' => $activeCount,
            'inactive_count' => $inactiveCount,
            'stores' => $filteredStores,
        ];
    }

    public function confirmBulkAction(string $type): void
    {
        if (empty($this->bulkSelectedIds)) {
            \Filament\Notifications\Notification::make()
                ->title('Pilih Produk')
                ->body('Silakan pilih atau tandai produk terlebih dahulu pada tabel di atas.')
                ->warning()
                ->send();
            return;
        }

        $breakdown = $this->bulkSelectionBreakdown;
        $totalProd = $breakdown['total_products'];
        $totalPcs = $breakdown['total_pcs'];
        $storesCount = count($breakdown['stores']);

        if ($type === 'delete') {
            $activeOutletScope = (auth()->check() && auth()->user()->outlet_id) ? auth()->user()->outlet_id : ($this->bulkOutletId ?: null);
            $activeOutletName = $activeOutletScope ? (\App\Models\Outlet::find($activeOutletScope)?->name ?? 'Cabang Terpilih') : null;

            if ($activeOutletName) {
                $this->confirmTitle = "Hapus {$totalProd} Produk dari {$activeOutletName}?";
                $this->confirmMessage = "Anda akan menghapus/melepas {$totalProd} produk terpilih (stok {$totalPcs} pcs) dari daftar barang di cabang {$activeOutletName}.";
                $this->confirmSubMessage = "🛡️ Proteksi Omnichannel: Jika produk ini juga terdaftar di cabang toko lain (misal: Muliku Plastik02), produk TIDAK akan hilang dari toko lain tersebut. Sistem hanya menghapus data & stok produk di cabang {$activeOutletName}.";
            } else {
                $this->confirmTitle = "Hapus Permanen {$totalProd} Produk?";
                $this->confirmMessage = "Anda akan menghapus {$totalProd} produk terpilih (dengan total stok {$totalPcs} pcs) secara permanen dari database. Tindakan ini tidak dapat dibatalkan.";
                
                if ($storesCount > 1) {
                    $details = [];
                    foreach ($breakdown['stores'] as $storeName => $info) {
                        $details[] = "{$info['count']} dari toko {$storeName}";
                    }
                    $this->confirmSubMessage = "⚠️ Perincian lintas toko: " . implode(', ', $details) . ". Produk yang memiliki riwayat penjualan tidak akan terhapus demi keamanan laporan keuangan.";
                } else {
                    $storeName = !empty($breakdown['stores']) ? array_key_first($breakdown['stores']) : 'Toko';
                    $this->confirmSubMessage = "⚠️ Catatan: Produk yang memiliki riwayat transaksi/penjualan di {$storeName} akan otomatis dilindungi sistem dan tidak akan terhapus.";
                }
            }

            $this->confirmButtonText = 'Ya, Hapus Sekarang';
            $this->confirmActionType = 'delete';
        } elseif ($type === 'activate') {
            $this->confirmTitle = "Aktifkan {$totalProd} Produk Terpilih?";
            $this->confirmMessage = "Semua {$totalProd} produk yang Anda tandai akan segera diaktifkan dan dapat dilihat oleh kasir serta pelanggan di semua saluran penjualan.";
            $this->confirmSubMessage = "✅ Produk aktif akan langsung siap untuk diproses pada menu kasir (POS) dan katalog toko.";
            $this->confirmButtonText = 'Ya, Aktifkan Sekarang';
            $this->confirmActionType = 'activate';
        } elseif ($type === 'deactivate') {
            $this->confirmTitle = "Nonaktifkan / Sembunyikan {$totalProd} Produk?";
            $this->confirmMessage = "Semua {$totalProd} produk yang Anda tandai akan dinonaktifkan (disembunyikan) dari layar kasir dan katalog toko, namun data dan stok tidak akan hilang.";
            $this->confirmSubMessage = "⏸️ Anda selalu dapat mengaktifkan kembali produk-produk ini kapan saja melalui menu ini.";
            $this->confirmButtonText = 'Ya, Nonaktifkan';
            $this->confirmActionType = 'deactivate';
        }

        $this->showConfirmModal = true;
    }

    public function executeConfirmedBulkAction(): void
    {
        $this->showConfirmModal = false;

        if ($this->confirmActionType === 'delete') {
            $this->bulkDeleteFromManager();
        } elseif ($this->confirmActionType === 'activate') {
            $this->bulkUpdateStatusFromManager(true);
        } elseif ($this->confirmActionType === 'deactivate') {
            $this->bulkUpdateStatusFromManager(false);
        }
    }

    public function bulkDeleteFromManager(): void
    {
        if (empty($this->bulkSelectedIds)) {
            \Filament\Notifications\Notification::make()
                ->title('Pilih Produk')
                ->body('Silakan pilih produk terlebih dahulu.')
                ->warning()
                ->send();
            return;
        }

        $activeOutletScope = (auth()->check() && auth()->user()->outlet_id) ? auth()->user()->outlet_id : ($this->bulkOutletId ?: null);
        $query = Product::whereIn('id', $this->bulkSelectedIds);
        if ($activeOutletScope) {
            $query->where(function ($q) use ($activeOutletScope) {
                $q->where('outlet_id', $activeOutletScope)
                  ->orWhereHas('inventories', fn($i) => $i->where('outlet_id', $activeOutletScope));
            });
        }
        $products = $query->with('inventories')->get();
        $deletedCount = 0;
        $blockedProducts = [];

        foreach ($products as $product) {
            try {
                if ($activeOutletScope) {
                    $hasOtherStores = \App\Models\Inventory::where('product_id', $product->id)
                        ->where('outlet_id', '!=', $activeOutletScope)
                        ->exists();
                    if ($hasOtherStores || ($product->outlet_id && $product->outlet_id != $activeOutletScope)) {
                        \App\Models\Inventory::where('product_id', $product->id)
                            ->where('outlet_id', $activeOutletScope)
                            ->delete();
                        if ($product->outlet_id == $activeOutletScope) {
                            $product->outlet_id = null;
                            $product->save();
                        }
                        $deletedCount++;
                        continue;
                    }
                }
                $product->delete();
                $deletedCount++;
            } catch (\Illuminate\Database\QueryException $e) {
                $blockedProducts[] = $product->name;
            }
        }

        $this->bulkSelectedIds = [];
        $this->bulkSelectAll = false;

        if ($deletedCount > 0) {
            $msg = $activeOutletScope 
                ? "Berhasil menghapus/melepas {$deletedCount} produk dari cabang " . (\App\Models\Outlet::find($activeOutletScope)?->name ?? 'Terpilih') . ". (Cabang toko lain yang memiliki produk ini tetap aman)."
                : "Berhasil menghapus {$deletedCount} produk secara global dari katalog.";
            \Filament\Notifications\Notification::make()
                ->title('Penghapusan Massal Selesai')
                ->body($msg)
                ->success()
                ->send();
        }

        if (!empty($blockedProducts)) {
            $blockedNames = implode(', ', array_slice($blockedProducts, 0, 3));
            if (count($blockedProducts) > 3) {
                $blockedNames .= ' dan ' . (count($blockedProducts) - 3) . ' lainnya';
            }
            \Filament\Notifications\Notification::make()
                ->title('Sebagian Produk Gagal Dihapus')
                ->body("Sebanyak " . count($blockedProducts) . " produk ({$blockedNames}) dilindungi karena memiliki riwayat penjualan.")
                ->warning()
                ->send();
        }
    }

    public function bulkUpdateCategoryFromManager(): void
    {
        if (empty($this->bulkSelectedIds) || empty($this->bulkTargetCategoryId)) {
            \Filament\Notifications\Notification::make()
                ->title('Pilih Produk & Kategori Tujuan')
                ->body('Silakan pilih produk dan kategori tujuan baru.')
                ->warning()
                ->send();
            return;
        }

        $query = Product::whereIn('id', $this->bulkSelectedIds);
        if (auth()->check() && auth()->user()->outlet_id) {
            $userOutletId = auth()->user()->outlet_id;
            $query->where(function ($q) use ($userOutletId) {
                $q->where('outlet_id', $userOutletId)
                  ->orWhereHas('inventories', fn($i) => $i->where('outlet_id', $userOutletId));
            });
        }
        $count = $query->update([
            'category_id' => $this->bulkTargetCategoryId,
        ]);

        $catName = \App\Models\Category::find($this->bulkTargetCategoryId)?->name ?? 'Kategori Baru';

        $this->bulkSelectedIds = [];
        $this->bulkSelectAll = false;
        $this->bulkTargetCategoryId = null;

        \Filament\Notifications\Notification::make()
            ->title('Kategori Berhasil Diperbarui')
            ->body("Sebanyak {$count} produk telah dipindahkan ke kategori \"{$catName}\".")
            ->success()
            ->send();
    }

    public function bulkUpdateStatusFromManager(bool $isActive): void
    {
        if (empty($this->bulkSelectedIds)) {
            \Filament\Notifications\Notification::make()
                ->title('Pilih Produk')
                ->body('Silakan pilih produk terlebih dahulu.')
                ->warning()
                ->send();
            return;
        }

        $query = Product::whereIn('id', $this->bulkSelectedIds);
        if (auth()->check() && auth()->user()->outlet_id) {
            $userOutletId = auth()->user()->outlet_id;
            $query->where(function ($q) use ($userOutletId) {
                $q->where('outlet_id', $userOutletId)
                  ->orWhereHas('inventories', fn($i) => $i->where('outlet_id', $userOutletId));
            });
        }
        $count = $query->update([
            'is_active' => $isActive,
        ]);

        $statusLabel = $isActive ? 'Aktif' : 'Nonaktif';

        $this->bulkSelectedIds = [];
        $this->bulkSelectAll = false;

        \Filament\Notifications\Notification::make()
            ->title('Status Berhasil Diubah')
            ->body("Sebanyak {$count} produk telah diubah statusnya menjadi {$statusLabel}.")
            ->success()
            ->send();
    }

    public function getView(): string
    {
        return 'filament.resources.product-resource.pages.list-products';
    }
}
