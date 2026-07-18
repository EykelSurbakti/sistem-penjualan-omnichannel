<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        ini_set('memory_limit', '-1');
        set_time_limit(600);

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // 1. Bersihkan seluruh tabel outlet, produk, dan inventaris agar tidak ada sisa ID acak/duplikat
            DB::table('inventories')->delete();
            DB::table('products')->delete();
            DB::table('orders')->delete();
            DB::table('order_items')->delete();
            DB::table('outlets')->delete();

            // 2. Masukkan tepat 3 outlet resmi dengan ID pasti (1, 2, 4) menyamai lokal 100%
            DB::table('outlets')->insert([
                ['id' => 1, 'code' => 'OUT-MLK-01', 'name' => 'Muliku Plastik01', 'address' => 'Jl. Plastik No. 1, Jakarta', 'phone' => '08222222222', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 2, 'code' => 'OUT-MLK-02', 'name' => 'Muliku Plastik02', 'address' => 'Jl. Plastik No. 2, Jakarta', 'phone' => '08333333333', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 4, 'code' => 'MALIKU-PLASTIK', 'name' => 'Muliku Prabotan', 'address' => 'Jl. Prabotan Utama No. 1, Jakarta', 'phone' => '08111111111', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ]);

            try {
                DB::statement('ALTER TABLE outlets AUTO_INCREMENT = 10;');
            } catch (\Throwable $e) {}

            // 3. Pastikan Kategori ada
            DB::table('categories')->updateOrInsert(['slug' => 'plastik-wadah-serbaguna'], ['name' => 'Plastik & Wadah Serbaguna', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('categories')->updateOrInsert(['slug' => 'alat-tulis-kantor-atk'], ['name' => 'Alat Tulis & Kantor (ATK)', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('categories')->updateOrInsert(['slug' => 'peta-dunia'], ['name' => 'Peta Dunia', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);

            // 4. Standarisasi penunjukan akun user ke outlet resmi ID 1, 2, atau 4
            DB::table('users')->where('email', 'mulikuplastik01@gmail.com')->update(['outlet_id' => 1]);
            DB::table('users')->where('email', 'mulikuplastik02@gmail.com')->update(['outlet_id' => 2]);
            DB::table('users')->where('email', 'mulikuprabotan@gmail.com')->update(['outlet_id' => 4]);
            DB::table('users')->where('email', 'nike@maliku.com')->update(['outlet_id' => 4]);
            DB::table('users')->whereNotIn('outlet_id', [1, 2, 4])->whereNotNull('outlet_id')->update(['outlet_id' => 4]);

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // 5. Muat ulang seluruh 11.792 produk dari cloud_products.json secara streaming chunks
            $jsonPath = __DIR__ . '/../data/cloud_products.json';
            if (!File::exists($jsonPath)) {
                return;
            }

            $jsonContent = File::get($jsonPath);
            $data = json_decode($jsonContent, true);
            unset($jsonContent);

            if (empty($data)) {
                return;
            }

            $categoriesMap = DB::table('categories')->pluck('id', 'slug');
            $outletsMap = DB::table('outlets')->pluck('id', 'code');
            $defaultCatId = DB::table('categories')->first()->id ?? 1;
            $defaultOutletId = 4; // Muliku Prabotan

            // Insert Produk
            if (!empty($data['products'])) {
                foreach (array_chunk($data['products'], 500) as $chunk) {
                    $insertBatch = [];
                    foreach ($chunk as $p) {
                        $insertBatch[] = [
                            'sku' => $p['sku'],
                            'outlet_id' => $outletsMap[$p['outlet_code']] ?? $defaultOutletId,
                            'category_id' => $categoriesMap[$p['category_slug']] ?? $defaultCatId,
                            'name' => $p['name'],
                            'slug' => $p['slug'],
                            'base_price' => $p['base_price'],
                            'cost_price' => $p['cost_price'],
                            'soldout_strategy' => $p['soldout_strategy'],
                            'is_active' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    DB::table('products')->insertOrIgnore($insertBatch);
                }
            }

            // Insert Inventories
            if (!empty($data['inventories'])) {
                $productsMap = DB::table('products')->pluck('id', 'sku');
                foreach (array_chunk($data['inventories'], 500) as $chunk) {
                    $insertInvBatch = [];
                    foreach ($chunk as $inv) {
                        if (isset($productsMap[$inv['sku']])) {
                            $insertInvBatch[] = [
                                'product_id' => $productsMap[$inv['sku']],
                                'outlet_id' => $outletsMap[$inv['outlet_code']] ?? $defaultOutletId,
                                'quantity' => $inv['quantity'],
                                'low_stock_threshold' => $inv['low_stock_threshold'],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                    if (!empty($insertInvBatch)) {
                        DB::table('inventories')->insertOrIgnore($insertInvBatch);
                    }
                }
            }
        } catch (\Throwable $e) {
            try { DB::statement('SET FOREIGN_KEY_CHECKS=1;'); } catch (\Throwable $e2) {}
            Log::error("Restore full products migration error: " . $e->getMessage());
        }
    }

    public function down(): void
    {
        //
    }
};
