<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected array $stockData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Pisahkan data stok cabang agar tidak mengganggu insert tabel products
        foreach ($data as $key => $val) {
            if ($key === 'qty_user_outlet' || str_starts_with($key, 'qty_outlet_')) {
                $this->stockData[$key] = $val;
                unset($data[$key]);
            }
        }

        if (auth()->check() && auth()->user()->outlet_id) {
            $data['outlet_id'] = auth()->user()->outlet_id;
        }

        // Auto-generate slug unik di belakang layar
        $baseSlug = \Illuminate\Support\Str::slug($data['name'] ?? 'produk');
        $slug = $baseSlug;
        $count = 1;
        while (\App\Models\Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $count++;
        }
        $data['slug'] = $slug;

        return $data;
    }

    protected function afterCreate(): void
    {
        $product = $this->record;
        $user = auth()->user();

        if ($user?->outlet_id && isset($this->stockData['qty_user_outlet'])) {
            // Jika Kasir cabang yang buat: simpan stok langsung ke cabang Kasir tersebut
            \App\Models\Inventory::updateOrCreate(
                ['product_id' => $product->id, 'outlet_id' => $user->outlet_id],
                ['quantity' => (int) $this->stockData['qty_user_outlet'], 'low_stock_threshold' => 5]
            );
        } else {
            // Jika Master Admin yang buat: simpan stok untuk semua cabang
            foreach (\App\Models\Outlet::all() as $outlet) {
                $qtyKey = 'qty_outlet_' . $outlet->id;
                $qty = isset($this->stockData[$qtyKey]) ? (int) $this->stockData[$qtyKey] : 0;
                \App\Models\Inventory::updateOrCreate(
                    ['product_id' => $product->id, 'outlet_id' => $outlet->id],
                    ['quantity' => $qty, 'low_stock_threshold' => 5]
                );
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
