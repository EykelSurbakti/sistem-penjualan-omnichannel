<?php

namespace App\Observers;

use App\Models\Inventory;
use App\Models\ActivityLog;

class InventoryObserver
{
    public function updated(Inventory $inventory): void
    {
        if ($inventory->isDirty('quantity')) {
            $old = (int) $inventory->getOriginal('quantity');
            $new = (int) $inventory->quantity;
            $diff = $new - $old;
            
            $prodName = $inventory->product?->name ?: 'Barang #' . $inventory->product_id;
            $outletName = $inventory->outlet?->name ?: 'Cabang #' . $inventory->outlet_id;
            
            if ($diff > 0) {
                $desc = "Penambahan stok barang '{$prodName}' di {$outletName} (+{$diff} Pcs, dari {$old} Pcs menjadi {$new} Pcs)";
            } else {
                $desc = "Pengurangan/penyesuaian stok barang '{$prodName}' di {$outletName} ({$diff} Pcs, dari {$old} Pcs menjadi {$new} Pcs)";
            }

            ActivityLog::record(
                'STOCK_ADJUSTMENT',
                'Barang & Stok',
                $desc,
                $inventory,
                ['quantity' => $old],
                ['quantity' => $new]
            );
        }
    }
}
