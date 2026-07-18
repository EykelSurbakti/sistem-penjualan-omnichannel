<?php

namespace App\Filament\Resources\InventoryResource\Pages;

use App\Filament\Resources\InventoryResource;
use App\Models\Inventory;
use App\Models\Outlet;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\Url;

class ListInventories extends ListRecords
{
    protected static string $resource = InventoryResource::class;

    // Limit yang bertambah saat scroll
    public int $loadedCount = 50;

    // Tab aktif: semua | stok_sedikit | stok_habis
    #[Url]
    public string $tabFilter = 'semua';

    // Pencarian nama produk
    #[Url]
    public string $search = '';

    // Filter outlet
    #[Url]
    public ?int $outletId = null;

    /**
     * Helper base query untuk Inventaris (hanya produk dengan TrackInventory = true)
     */
    protected function getBaseInventoryQuery()
    {
        $query = Inventory::with(['product', 'outlet'])
            ->whereHas('product', fn ($q) => $q->where('track_inventory', true));

        if (auth()->check() && auth()->user()->outlet_id) {
            $query->where('outlet_id', auth()->user()->outlet_id);
        } elseif ($this->outletId) {
            $query->where('outlet_id', $this->outletId);
        }

        return $query;
    }

    /**
     * Ambil data inventaris sesuai filter.
     */
    public function getInventoriesProperty()
    {
        $query = $this->getBaseInventoryQuery()->orderByDesc('updated_at');

        // Tab filter
        if ($this->tabFilter === 'stok_sedikit') {
            $query->where('quantity', '>', 0)
                  ->whereColumn('quantity', '<=', 'low_stock_threshold');
        } elseif ($this->tabFilter === 'stok_habis') {
            $query->where('quantity', '<=', 0);
        }

        // Pencarian nama produk
        if ($this->search !== '') {
            $keyword = $this->search;
            $query->whereHas('product', fn ($q) => $q
                ->where('name', 'like', "%{$keyword}%")
                ->orWhere('sku',  'like', "%{$keyword}%")
            );
        }

        return $query->limit($this->loadedCount)->get();
    }

    public function getTotalCountProperty(): int
    {
        return $this->getBaseInventoryQuery()->count();
    }

    public function getStokSedikitCountProperty(): int
    {
        return $this->getBaseInventoryQuery()
            ->where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->count();
    }

    public function getStokHabisCountProperty(): int
    {
        return $this->getBaseInventoryQuery()
            ->where('quantity', '<=', 0)
            ->count();
    }

    public function getCurrentTabTotalProperty(): int
    {
        $query = $this->getBaseInventoryQuery();

        if ($this->tabFilter === 'stok_sedikit') {
            $query->where('quantity', '>', 0)
                  ->whereColumn('quantity', '<=', 'low_stock_threshold');
        } elseif ($this->tabFilter === 'stok_habis') {
            $query->where('quantity', '<=', 0);
        }

        if ($this->search !== '') {
            $keyword = $this->search;
            $query->whereHas('product', fn ($q) => $q
                ->where('name', 'like', "%{$keyword}%")
                ->orWhere('sku',  'like', "%{$keyword}%")
            );
        }

        return $query->count();
    }

    public function getOutletsProperty()
    {
        return Outlet::orderBy('name')->get();
    }

    public function loadMore(): void
    {
        $this->loadedCount += 50;
    }

    public function setTab(string $tab): void
    {
        $this->tabFilter   = $tab;
        $this->loadedCount = 50;
    }

    public function updatedSearch(): void
    {
        $this->loadedCount = 50;
    }

    public function updatedOutletId(): void
    {
        $this->loadedCount = 50;
    }

    public function getView(): string
    {
        return 'filament.resources.inventory-resource.pages.list-inventories';
    }
}
