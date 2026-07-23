<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class MigrateToStrictOutlet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-to-strict-outlet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate UMUM products to strict outlet isolation and fix mismatched inventories.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Memulai proses migrasi Produk UMUM -> Strict Outlet Isolation...");

        DB::beginTransaction();
        try {
            // ==========================================
            // 1. Pecah Produk UMUM (outlet_id = null)
            // ==========================================
            $this->info("Mencari produk UMUM (outlet_id = null)...");
            $products = Product::whereNull('outlet_id')->get();
            $countNewProducts = 0;

            foreach ($products as $product) {
                $inventories = $product->inventories()->get();
                $clonedForOutlets = [];

                foreach ($inventories as $inv) {
                    $outletId = $inv->outlet_id;
                    if (!$outletId) continue;
                    
                    $existing = Product::where('sku', $product->sku)->where('outlet_id', $outletId)->first();
                    
                    if (!$existing) {
                        $newProduct = $product->replicate();
                        $newProduct->outlet_id = $outletId;
                        $newProduct->slug = $product->slug . '-' . $outletId . '-' . substr(md5(uniqid()), 0, 4);
                        $newProduct->save();
                        $countNewProducts++;
                    } else {
                        $newProduct = $existing;
                    }
                    
                    $clonedForOutlets[$outletId] = $newProduct->id;
                    
                    $inv->product_id = $newProduct->id;
                    $inv->save();
                }

                $orderItems = OrderItem::where('product_id', $product->id)->get();
                foreach ($orderItems as $item) {
                    $order = $item->order;
                    if ($order && $order->outlet_id && isset($clonedForOutlets[$order->outlet_id])) {
                        $item->product_id = $clonedForOutlets[$order->outlet_id];
                        $item->save();
                    }
                }

                // Hapus produk UMUM lama
                $product->delete();
            }
            $this->info("Berhasil memecah {$countNewProducts} produk spesifik toko baru dari Produk UMUM lama.");

            // ==========================================
            // 2. Fix Mismatched Inventories
            // ==========================================
            $this->info("Mencari stok inventaris yang salah kamar (outlet produk beda dengan outlet inventaris)...");
            $mismatchedInventories = Inventory::join('products', 'inventories.product_id', '=', 'products.id')
                ->whereColumn('inventories.outlet_id', '!=', 'products.outlet_id')
                ->select('inventories.*', 'products.sku as product_sku')
                ->get();

            $countInv = 0;
            foreach ($mismatchedInventories as $inv) {
                $wrongProduct = Product::find($inv->product_id);
                if (!$wrongProduct) continue;

                $correctProduct = Product::where('sku', $wrongProduct->sku)
                    ->where('outlet_id', $inv->outlet_id)
                    ->first();

                if (!$correctProduct) {
                    $correctProduct = $wrongProduct->replicate();
                    $correctProduct->outlet_id = $inv->outlet_id;
                    $correctProduct->slug = $wrongProduct->slug . '-' . $inv->outlet_id . '-' . substr(md5(uniqid()), 0, 4);
                    $correctProduct->save();
                    $countNewProducts++;
                }

                $inv->product_id = $correctProduct->id;
                $inv->save();
                $countInv++;
            }
            $this->info("Berhasil memperbaiki {$countInv} baris stok (Inventaris) yang salah kamar.");

            // ==========================================
            // 3. Fix Mismatched Order Items
            // ==========================================
            $this->info("Mencari riwayat pesanan (Order Items) yang salah kamar...");
            $mismatchedOrderItems = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereColumn('orders.outlet_id', '!=', 'products.outlet_id')
                ->select('order_items.*', 'products.sku as product_sku', 'orders.outlet_id as order_outlet_id')
                ->get();

            $countItems = 0;
            foreach ($mismatchedOrderItems as $item) {
                $wrongProduct = Product::find($item->product_id);
                if (!$wrongProduct) continue;

                $correctProduct = Product::where('sku', $wrongProduct->sku)
                    ->where('outlet_id', $item->order_outlet_id)
                    ->first();

                if (!$correctProduct) {
                    $correctProduct = $wrongProduct->replicate();
                    $correctProduct->outlet_id = $item->order_outlet_id;
                    $correctProduct->slug = $wrongProduct->slug . '-' . $item->order_outlet_id . '-' . substr(md5(uniqid()), 0, 4);
                    $correctProduct->save();
                    $countNewProducts++;
                }

                $item->product_id = $correctProduct->id;
                $item->save();
                $countItems++;
            }
            $this->info("Berhasil memperbaiki {$countItems} baris riwayat penjualan (OrderItem) yang salah kamar.");

            DB::commit();
            $this->info("=== MIGRASI SELESAI DAN SUKSES 100% ===");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Gagal melakukan migrasi: " . $e->getMessage());
        }
    }
}
