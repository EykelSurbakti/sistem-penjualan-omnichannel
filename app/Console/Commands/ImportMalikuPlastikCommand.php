<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ImportMalikuPlastikCommand extends Command
{
    protected $signature = 'import:maliku-plastik {--file= : Path to CSV file}';
    protected $description = 'Import dataset NIKE-MALIKU-PLASTIK.csv and create Maliku Plastik outlet and Nike user';

    public function handle()
    {
        $filePath = $this->option('file') ?: 'C:\Users\eykel\Documents\MALIKU\NIKE-MALIKU-PLASTIK.csv';

        if (!file_exists($filePath)) {
            $this->error("File CSV tidak ditemukan di path: {$filePath}");
            return 1;
        }

        $this->info("Memulai proses pembuatan Toko Maliku Plastik & User Nike...");

        // 1. Create or get Outlet "Maliku Plastik"
        $outlet = Outlet::updateOrCreate(
            ['name' => 'Maliku Plastik'],
            [
                'code' => 'MALIKU-PLASTIK',
                'address' => 'Toko Maliku Plastik',
                'phone' => '081234567890',
                'is_active' => true,
            ]
        );
        $this->info("✔ Toko Berhasil Dibuat/Diperbarui: {$outlet->name} (ID: {$outlet->id})");

        // 2. Create or get User "Nike" for this outlet
        $user = User::updateOrCreate(
            ['email' => 'nike@maliku.com'],
            [
                'name' => 'Nike',
                'password' => Hash::make('password'),
                'outlet_id' => $outlet->id,
            ]
        );
        $this->info("✔ User Admin/Kasir Berhasil Dibuat/Diperbarui: {$user->name} (Email: nike@maliku.com | Password: password)");

        $this->info("Membaca file CSV: {$filePath}...");

        if (($handle = fopen($filePath, "r")) !== false) {
            $headers = fgetcsv($handle, 0, ",");
            if (!$headers) {
                $this->error("Header CSV tidak valid.");
                fclose($handle);
                return 1;
            }

            // Map headers
            $headerMap = [];
            foreach ($headers as $index => $colName) {
                $headerMap[trim($colName)] = $index;
            }

            $skuIdx = $headerMap['SKU'] ?? -1;
            $nameIdx = $headerMap['Name'] ?? -1;
            $buyingPriceIdx = $headerMap['BuyingPrice'] ?? -1;
            $priceIdx = $headerMap['Price'] ?? -1;
            $isActiveIdx = $headerMap['IsActive'] ?? -1;
            $alertIdx = $headerMap['AlertAtStock'] ?? -1;

            // Find any inventory column like "Inventory MULIKU STORE 03" or "Inventory ..."
            $invIdx = -1;
            foreach ($headerMap as $colName => $idx) {
                if (stripos($colName, 'Inventory') !== false && stripos($colName, 'Track') === false) {
                    $invIdx = $idx;
                    break;
                }
            }

            if ($nameIdx === -1) {
                $this->error("Kolom 'Name' tidak ditemukan di CSV.");
                fclose($handle);
                return 1;
            }

            $count = 0;
            $updated = 0;
            $created = 0;

            DB::beginTransaction();
            try {
                while (($row = fgetcsv($handle, 0, ",")) !== false) {
                    $name = trim($row[$nameIdx] ?? '');
                    if ($name === '') {
                        continue;
                    }

                    $sku = trim($row[$skuIdx] ?? '');
                    if ($sku === '') {
                        $sku = 'SKU-' . strtoupper(Str::random(8)) . '-' . $count;
                    }

                    $costPrice = (float) ($buyingPriceIdx !== -1 ? ($row[$buyingPriceIdx] ?? 0) : 0);
                    $basePrice = (float) ($priceIdx !== -1 ? ($row[$priceIdx] ?? 0) : 0);
                    $isActiveStr = $isActiveIdx !== -1 ? trim(strtolower($row[$isActiveIdx] ?? '')) : 'true';
                    $isActive = ($isActiveStr === 'true' || $isActiveStr === '1');

                    $invQty = 0;
                    if ($invIdx !== -1) {
                        $invQty = (int) floatval($row[$invIdx] ?? 0);
                    }

                    $lowStock = 5;
                    if ($alertIdx !== -1 && trim($row[$alertIdx] ?? '') !== '') {
                        $lowStock = (int) floatval($row[$alertIdx]);
                    }

                    // Check if product exists by SKU
                    $product = Product::where('sku', $sku)->first();

                    if (!$product) {
                        // Create unique slug
                        $slug = Str::slug($name);
                        if (Product::where('slug', $slug)->exists()) {
                            $slug .= '-' . substr(md5($sku . $count), 0, 6);
                        }

                        $product = Product::create([
                            'sku' => $sku,
                            'name' => $name,
                            'slug' => $slug,
                            'base_price' => $basePrice,
                            'cost_price' => $costPrice,
                            'has_variants' => false,
                            'is_active' => $isActive,
                        ]);
                        $created++;
                    } else {
                        $product->update([
                            'name' => $name,
                            'base_price' => $basePrice,
                            'cost_price' => $costPrice,
                            'is_active' => $isActive,
                        ]);
                        $updated++;
                    }

                    // Create or update inventory for Maliku Plastik outlet
                    Inventory::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'product_variant_id' => null,
                            'outlet_id' => $outlet->id,
                        ],
                        [
                            'quantity' => $invQty,
                            'reserved_quantity' => 0,
                            'low_stock_threshold' => $lowStock,
                        ]
                    );

                    $count++;

                    if ($count % 500 === 0) {
                        $this->info("... diproses {$count} produk ...");
                    }
                }

                DB::commit();
                fclose($handle);

                $this->info("=================================================");
                $this->info("✔ IMPORT BERHASIL SECARA KESELURUHAN!");
                $this->info("Total Produk Diproses: {$count}");
                $this->info("Produk Baru Dibuat: {$created}");
                $this->info("Produk Diperbarui: {$updated}");
                $this->info("Semua stok dihubungkan ke Toko: {$outlet->name}");
                $this->info("User Kasir/Admin Toko: {$user->name} (Email: nike@maliku.com | Pass: password)");
                $this->info("=================================================");

                return 0;
            } catch (\Exception $e) {
                DB::rollBack();
                fclose($handle);
                $this->error("Terjadi kesalahan saat import: " . $e->getMessage());
                return 1;
            }
        }

        return 1;
    }
}
