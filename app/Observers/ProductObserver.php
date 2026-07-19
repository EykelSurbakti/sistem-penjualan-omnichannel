<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ActivityLog;

class ProductObserver
{
    public function created(Product $product): void
    {
        ActivityLog::record(
            'CREATE',
            'Barang & Stok',
            "Menambahkan barang baru: '{$product->name}' (SKU: {$product->sku}) dengan harga jual Rp " . number_format($product->base_price, 0, ',', '.'),
            $product,
            null,
            $product->toArray()
        );
    }

    public function updated(Product $product): void
    {
        if ($product->isDirty('base_price') || $product->isDirty('cost_price')) {
            $oldPrice = number_format($product->getOriginal('base_price'), 0, ',', '.');
            $newPrice = number_format($product->base_price, 0, ',', '.');
            ActivityLog::record(
                'PRICE_CHANGE',
                'Barang & Stok',
                "Mengubah harga jual barang '{$product->name}' dari Rp {$oldPrice} menjadi Rp {$newPrice}",
                $product,
                [
                    'base_price' => $product->getOriginal('base_price'),
                    'cost_price' => $product->getOriginal('cost_price')
                ],
                [
                    'base_price' => $product->base_price,
                    'cost_price' => $product->cost_price
                ]
            );
        } elseif (count($product->getDirty()) > 0) {
            ActivityLog::record(
                'UPDATE',
                'Barang & Stok',
                "Mengedit data rincian barang '{$product->name}' (" . implode(', ', array_keys($product->getDirty())) . ")",
                $product,
                $product->getOriginal(),
                $product->getChanges()
            );
        }
    }

    public function deleted(Product $product): void
    {
        ActivityLog::record(
            'DELETE',
            'Barang & Stok',
            "Menghapus barang dari katalog: '{$product->name}' (SKU: {$product->sku})",
            $product,
            $product->toArray(),
            null
        );
    }
}
