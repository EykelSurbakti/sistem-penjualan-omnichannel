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
        set_time_limit(300);

        try {
            Schema::disableForeignKeyConstraints();

            // 1. Bersihkan toko, pesanan seeder, dan inventaris lama yang tidak terkait Muliku
            DB::table('orders')->delete();
            DB::table('order_items')->delete();
            DB::table('inventories')->delete();
            DB::table('outlets')->whereNotIn('code', ['OUT-MLK-01', 'OUT-MLK-02', 'MALIKU-PLASTIK', 'OUT-PRB-01', 'OUT-PLS-01', 'OUT-PLS-02'])->delete();

            // 2. Sinkronkan nama dan kode Outlet persis dengan LOKAL
            DB::table('outlets')->updateOrInsert(['code' => 'OUT-MLK-01'], ['name' => 'Muliku Plastik01', 'address' => 'Jl. Plastik No. 1, Jakarta', 'phone' => '08222222222', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('outlets')->updateOrInsert(['code' => 'OUT-MLK-02'], ['name' => 'Muliku Plastik02', 'address' => 'Jl. Plastik No. 2, Jakarta', 'phone' => '08333333333', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('outlets')->updateOrInsert(['code' => 'MALIKU-PLASTIK'], ['name' => 'Muliku Prabotan', 'address' => 'Jl. Prabotan Utama No. 1, Jakarta', 'phone' => '08111111111', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);

            // 3. Sinkronkan Kategori
            DB::table('categories')->updateOrInsert(['slug' => 'plastik-wadah-serbaguna'], ['name' => 'Plastik & Wadah Serbaguna', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('categories')->updateOrInsert(['slug' => 'alat-tulis-kantor-atk'], ['name' => 'Alat Tulis & Kantor (ATK)', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('categories')->updateOrInsert(['slug' => 'peta-dunia'], ['name' => 'Peta Dunia', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);

            Schema::enableForeignKeyConstraints();

            // 4. Baca file JSON berukuran kecil dan masukkan secara streaming
            $jsonPath = __DIR__ . '/../data/cloud_products.json';
            if (!File::exists($jsonPath)) {
                return;
            }

            $jsonContent = File::get($jsonPath);
            $data = json_decode($jsonContent, true);
            unset($jsonContent); // Bebaskan memori string JSON

            if (empty($data)) {
                return;
            }

            $categoriesMap = DB::table('categories')->pluck('id', 'slug');
            $outletsMap = DB::table('outlets')->pluck('id', 'code');
            $defaultCatId = DB::table('categories')->first()->id ?? 1;
            $defaultOutletId = DB::table('outlets')->first()->id ?? 1;

            // Insert Produk dalam chunk 500
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

            // Insert Inventories dalam chunk 500
            if (!empty($data['inventories'])) {
                $productsMap = DB::table('products')->pluck('id', 'sku');
                foreach (array_chunk($data['inventories'], 500) as $chunk) {
                    $insertInvBatch = [];
                    foreach ($chunk as $inv) {
                        if (isset($productsMap[$inv['sku']], $outletsMap[$inv['outlet_code']])) {
                            $insertInvBatch[] = [
                                'product_id' => $productsMap[$inv['sku']],
                                'outlet_id' => $outletsMap[$inv['outlet_code']],
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
            Schema::enableForeignKeyConstraints();
            Log::error("Migration upload products error: " . $e->getMessage());
            // Jangan throw exception agar kontainer tidak crash saat deploy
        }
    }

    public function down(): void
    {
        //
    }
};