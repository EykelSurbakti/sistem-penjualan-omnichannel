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

    public function getOutletsProperty()
    {
        return \App\Models\Outlet::where('is_active', true)->get();
    }

    protected function getHeaderActions(): array
    {
        return [
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
        return 'filament.resources.product-resource.pages.list-products';
    }
}
