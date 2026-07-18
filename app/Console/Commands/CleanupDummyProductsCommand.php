<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupDummyProductsCommand extends Command
{
    protected $signature = 'cleanup:dummy-products {--file= : Path to CSV file}';
    protected $description = 'Remove all products that are NOT in the NIKE-MALIKU-PLASTIK.csv dataset';

    public function handle()
    {
        $filePath = $this->option('file') ?: 'C:\Users\eykel\Documents\MALIKU\NIKE-MALIKU-PLASTIK.csv';

        if (!file_exists($filePath)) {
            $this->error("File CSV tidak ditemukan: {$filePath}");
            return 1;
        }

        $this->info("Membaca seluruh SKU dari CSV: {$filePath}...");

        $validSkus = [];
        $validNames = [];

        if (($handle = fopen($filePath, "r")) !== false) {
            $headers = fgetcsv($handle, 0, ",");
            $headerMap = [];
            foreach ($headers as $index => $colName) {
                $headerMap[trim($colName)] = $index;
            }

            $skuIdx = $headerMap['SKU'] ?? -1;
            $nameIdx = $headerMap['Name'] ?? -1;

            while (($row = fgetcsv($handle, 0, ",")) !== false) {
                if ($nameIdx !== -1) {
                    $name = trim($row[$nameIdx] ?? '');
                    if ($name !== '') {
                        $validNames[$name] = true;
                    }
                }
                if ($skuIdx !== -1) {
                    $sku = trim($row[$skuIdx] ?? '');
                    if ($sku !== '') {
                        $validSkus[$sku] = true;
                    }
                }
            }
            fclose($handle);
        }

        $this->info("Ditemukan " . count($validSkus) . " SKU valid dan " . count($validNames) . " Nama valid di CSV.");

        // Find products in DB that neither match a valid SKU nor valid Name, OR whose SKU starts with 'ACN-' or old dummy prefixes if they are not in CSV
        $allProducts = Product::all();
        $toDeleteIds = [];

        foreach ($allProducts as $p) {
            $skuMatch = isset($validSkus[$p->sku]);
            $nameMatch = isset($validNames[$p->name]);

            // Jika SKU tidak ada di CSV dan (Nama juga tidak persis ada di CSV atau SKU lama/dummy seperti ACN-)
            if (!$skuMatch && !$nameMatch) {
                $toDeleteIds[] = $p->id;
            } elseif (!$skuMatch && str_starts_with($p->sku, 'ACN-')) {
                // Contoh: ACN-110, ACN-139 adalah dummy SKU kita sebelumnya
                $toDeleteIds[] = $p->id;
            }
        }

        $this->info("Akan menghapus " . count($toDeleteIds) . " barang dummy lama...");

        if (empty($toDeleteIds)) {
            $this->info("Tidak ada barang dummy yang perlu dihapus.");
            return 0;
        }

        DB::beginTransaction();
        try {
            // Delete order items & orders associated only with deleted products if needed, or nullify/cascade
            // First delete order_items referencing these products
            OrderItem::whereIn('product_id', $toDeleteIds)->delete();

            // Delete inventories referencing these products
            Inventory::whereIn('product_id', $toDeleteIds)->delete();

            // Delete the products
            Product::whereIn('id', $toDeleteIds)->delete();

            DB::commit();

            $totalRemaining = Product::count();
            $this->info("✔ Berhasil menghapus " . count($toDeleteIds) . " barang dummy!");
            $this->info("Total barang tersisa di katalog sekarang: {$totalRemaining}");

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Gagal menghapus: " . $e->getMessage());
            return 1;
        }
    }
}
