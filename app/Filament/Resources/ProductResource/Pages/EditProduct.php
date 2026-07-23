<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected array $stockData = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $product = $this->record;

        if ($product->outlet_id) {
            $inv = $product->inventories()->where('outlet_id', $product->outlet_id)->first();
            $data['qty'] = $inv ? (int) $inv->quantity : 0;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['qty'])) {
            $this->stockData['qty'] = $data['qty'];
            unset($data['qty']);
        }

        if (empty($data['slug']) || str_starts_with($data['slug'], 'temp-')) {
            $baseSlug = \Illuminate\Support\Str::slug($data['name'] ?? 'produk');
            $slug = $baseSlug;
            $count = 1;
            while (\App\Models\Product::where('slug', $slug)->where('id', '!=', $this->record->id)->exists()) {
                $slug = $baseSlug . '-' . $count++;
            }
            $data['slug'] = $slug;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $product = $this->record;

        if ($product->outlet_id && isset($this->stockData['qty'])) {
            \App\Models\Inventory::updateOrCreate(
                ['product_id' => $product->id, 'outlet_id' => $product->outlet_id],
                ['quantity' => (int) $this->stockData['qty']]
            );
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
