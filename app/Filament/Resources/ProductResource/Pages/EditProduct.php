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
        $user = auth()->user();

        if ($user?->outlet_id) {
            $inv = $product->inventories()->where('outlet_id', $user->outlet_id)->first();
            $data['qty_user_outlet'] = $inv ? (int) $inv->quantity : 0;
        } else {
            foreach (\App\Models\Outlet::all() as $outlet) {
                $inv = $product->inventories()->where('outlet_id', $outlet->id)->first();
                $data['qty_outlet_' . $outlet->id] = $inv ? (int) $inv->quantity : 0;
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        foreach ($data as $key => $val) {
            if ($key === 'qty_user_outlet' || str_starts_with($key, 'qty_outlet_')) {
                $this->stockData[$key] = $val;
                unset($data[$key]);
            }
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
        $user = auth()->user();

        if ($user?->outlet_id) {
            if (isset($this->stockData['qty_user_outlet'])) {
                \App\Models\Inventory::updateOrCreate(
                    ['product_id' => $product->id, 'outlet_id' => $user->outlet_id],
                    ['quantity' => (int) $this->stockData['qty_user_outlet']]
                );
            }
        } else {
            foreach (\App\Models\Outlet::all() as $outlet) {
                $qtyKey = 'qty_outlet_' . $outlet->id;
                if (isset($this->stockData[$qtyKey])) {
                    \App\Models\Inventory::updateOrCreate(
                        ['product_id' => $product->id, 'outlet_id' => $outlet->id],
                        ['quantity' => (int) $this->stockData[$qtyKey]]
                    );
                }
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
