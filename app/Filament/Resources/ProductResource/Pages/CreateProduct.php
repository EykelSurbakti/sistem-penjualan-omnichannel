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
        if (isset($data['qty'])) {
            $this->stockData['qty'] = $data['qty'];
            unset($data['qty']);
        }

        if (auth()->check() && auth()->user()->outlet_id) {
            $data['outlet_id'] = auth()->user()->outlet_id;
        }

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

        if ($product->outlet_id && isset($this->stockData['qty'])) {
            \App\Models\Inventory::updateOrCreate(
                ['product_id' => $product->id, 'outlet_id' => $product->outlet_id],
                ['quantity' => (int) $this->stockData['qty'], 'low_stock_threshold' => 5]
            );
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
