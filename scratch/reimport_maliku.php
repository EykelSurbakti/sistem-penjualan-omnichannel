<?php
/**
 * SCRIPT: Kosongkan data lalu import ulang dari maliku plastik.csv
 * Lengkap dengan track_inventory & alert_at_stock
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use League\Csv\Reader;

set_time_limit(0);

$csvPath = 'C:/Users/eykel/Documents/MALIKU/maliku plastik.csv';

// 1. Cari outlet
$outlet = DB::table('outlets')->where('name', 'like', '%Maliku%Plastik%')->first()
       ?? DB::table('outlets')->where('name', 'like', '%Maliku%')->first();

if (!$outlet) {
    echo "❌ Outlet tidak ditemukan!\n";
    DB::table('outlets')->get()->each(fn($o) => print("  - [{$o->id}] {$o->name}\n"));
    exit(1);
}
echo "✔ Outlet: [{$outlet->id}] {$outlet->name}\n";

// 2. Hapus data lama
echo "\n📦 Menghapus data lama...\n";
DB::statement('SET FOREIGN_KEY_CHECKS=0');
$di = DB::table('inventories')->delete();
$do = DB::table('order_items')->delete();
$dr = DB::table('orders')->delete();
$dp = DB::table('products')->delete();
$dc = DB::table('categories')->delete();
DB::statement('SET FOREIGN_KEY_CHECKS=1');
DB::statement('ALTER TABLE products AUTO_INCREMENT = 1');
DB::statement('ALTER TABLE inventories AUTO_INCREMENT = 1');
DB::statement('ALTER TABLE categories AUTO_INCREMENT = 1');
echo "  Inventories: {$di} | Order Items: {$do} | Orders: {$dr} | Products: {$dp} | Kategori: {$dc}\n";
echo "  ✔ Database kosong!\n\n";

// 3. Import CSV
if (!file_exists($csvPath)) { echo "❌ File tidak ada: {$csvPath}\n"; exit(1); }

$csv = Reader::createFromPath($csvPath, 'r');
$csv->setHeaderOffset(0);
$records = iterator_to_array($csv->getRecords());
$total   = count($records);
echo "📊 Total produk di CSV: {$total}\n";

$firstRow     = reset($records);
$inventoryCol = null;
foreach (array_keys($firstRow) as $key) {
    if (stripos($key, 'Inventory ') !== false && stripos($key, 'STORE') !== false) {
        $inventoryCol = $key;
        break;
    }
}
echo "  Kolom stok: " . ($inventoryCol ?? '⚠️ tidak ditemukan') . "\n\n";

$imported = 0; $skipped = 0;
$categoryCache = []; $skuSet = []; $slugSet = [];
$now = now()->toDateTimeString();

foreach (array_chunk($records, 100) as $chunkIndex => $chunk) {
    DB::beginTransaction();
    try {
        foreach ($chunk as $row) {
            $sku         = trim($row['SKU'] ?? '');
            $name        = trim($row['Name'] ?? '');
            $handle      = trim($row['Handle'] ?? '');
            $buyingPrice = (float) str_replace(',', '', trim($row['BuyingPrice'] ?? '0'));
            $price       = (float) str_replace(',', '', trim($row['Price'] ?? '0'));
            $isActive    = strtolower(trim($row['IsActive'] ?? 'true')) !== 'false';
            $trackInv    = strtolower(trim($row['TrackInventory'] ?? 'true')) !== 'false';
            $alertAt     = (int) trim($row['AlertAtStock'] ?? '0');
            $continueStr = strtolower(trim($row['ContinueSellingWhenSoldOut'] ?? 'false')) === 'true' ? 'continue' : 'stop';
            $createdAtRaw = trim($row['CreatedAt'] ?? '');
            $type        = trim($row['Type'] ?? '');
            $description = trim($row['Description'] ?? '');
            $qty         = $inventoryCol ? (int) round((float) str_replace(',', '', ($row[$inventoryCol] ?? '0'))) : 0;

            if ($name === '' && $sku === '') { $skipped++; continue; }

            $threshold = $alertAt > 0 ? $alertAt : 3; // Jika AlertAtStock = 0 di CSV, default threshold iSeller adalah 3

            // Slug unik (in-memory)
            $slugBase = $handle !== '' ? $handle : Str::slug($name ?: 'produk');
            $slug = $slugBase; $sfx = 1;
            while (isset($slugSet[$slug])) { $slug = $slugBase . '-' . $sfx++; }
            $slugSet[$slug] = true;

            // SKU unik (in-memory)
            $skuFinal = $sku !== '' ? $sku : 'AUTO-' . ($imported + $skipped + 1);
            $skufx = 1;
            while (isset($skuSet[$skuFinal])) { $skuFinal = $sku . '-' . $skufx++; }
            $skuSet[$skuFinal] = true;

            // Kategori
            $categoryId = null;
            if ($type !== '') {
                if (!isset($categoryCache[$type])) {
                    $ex = DB::table('categories')->where('name', $type)->first();
                    $categoryCache[$type] = $ex
                        ? $ex->id
                        : DB::table('categories')->insertGetId([
                            'name' => $type, 'slug' => Str::slug($type) ?: Str::random(8),
                            'created_at' => $now, 'updated_at' => $now,
                        ]);
                }
                $categoryId = $categoryCache[$type];
            }

            // Tanggal
            try { $createdAt = \Carbon\Carbon::parse($createdAtRaw)->toDateTimeString(); }
            catch (\Throwable $e) { $createdAt = $now; }

            // Insert
            $productId = DB::table('products')->insertGetId([
                'category_id'      => $categoryId,
                'name'             => $name,
                'slug'             => $slug,
                'sku'              => $skuFinal,
                'description'      => $description,
                'cost_price'       => $buyingPrice,
                'base_price'       => $price,
                'has_variants'     => false,
                'is_active'        => $isActive,
                'track_inventory'  => $trackInv,
                'alert_at_stock'   => $threshold,
                'soldout_strategy' => $continueStr,
                'created_at'       => $createdAt,
                'updated_at'       => $now,
            ]);

            DB::table('inventories')->insert([
                'product_id'          => $productId,
                'outlet_id'           => $outlet->id,
                'quantity'            => $qty,
                'low_stock_threshold' => $threshold,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);

            $imported++;
        }
        DB::commit();
        $pct = $total > 0 ? round(($imported / $total) * 100) : 100;
        echo "  [{$pct}%] {$imported}/{$total}\r";
    } catch (\Throwable $e) {
        DB::rollBack();
        echo "\n⚠️ Error chunk {$chunkIndex}: " . $e->getMessage() . "\n";
        $skipped += count($chunk);
    }
}

echo "\n\n";
echo "═══════════════════════════════════════════════════\n";
echo "  ✅ IMPORT SELESAI!\n";
echo "  📦 Produk diimport : {$imported}\n";
echo "  ⚠️  Baris dilewati  : {$skipped}\n";
echo "  🏪 Outlet           : [{$outlet->id}] {$outlet->name}\n";
echo "═══════════════════════════════════════════════════\n";
