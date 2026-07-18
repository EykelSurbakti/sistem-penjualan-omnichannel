<?php
/**
 * SCRIPT: Import data untuk Toko Muliku Plastik02 (ID: 2) dari MULIKU-PLASTIK02.csv
 * Memastikan katalog produk 100% terisolasi (products.outlet_id = 2).
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use League\Csv\Reader;
use App\Models\Category;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

$startTime = microtime(true);

echo "=== 0. PERSIAPAN TOKO & AKUN KASIR MULIKU PLASTIK02 ===\n";

$outlet2 = Outlet::find(2);
if (!$outlet2) {
    $outlet2 = Outlet::create(['id' => 2, 'name' => 'Muliku Plastik02', 'is_active' => true]);
} else {
    $outlet2->update(['name' => 'Muliku Plastik02', 'is_active' => true]);
}
echo " -> Outlet ID 2: Muliku Plastik02 berhasil disiapkan.\n";

$user2 = User::where('email', 'mulikuplastik02@gmail.com')->first();
if (!$user2) {
    User::create([
        'name' => 'Muliku Plastik02',
        'email' => 'mulikuplastik02@gmail.com',
        'password' => Hash::make('password'),
        'outlet_id' => 2
    ]);
} else {
    $user2->update([
        'name' => 'Muliku Plastik02',
        'password' => Hash::make('password'),
        'outlet_id' => 2
    ]);
}
echo " -> Akun Kasir: mulikuplastik02@gmail.com (outlet_id: 2) berhasil disiapkan.\n";

echo "\n=== 1. BERSIHKAN KATALOG LAMA UNTUK OUTLET ID 2 ===\n";
DB::statement('SET FOREIGN_KEY_CHECKS=0;');
Inventory::where('outlet_id', 2)->delete();
Product::where('outlet_id', 2)->delete();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');
echo " -> Katalog lawas untuk Toko ID 2 berhasil dibersihkan.\n";

// Siapkan kategori default map
$categoryMap = [];
foreach (Category::all() as $cat) {
    $categoryMap[strtolower(trim($cat->name))] = $cat->id;
}
if (!isset($categoryMap['umum'])) {
    $cat = Category::create(['name' => 'UMUM', 'slug' => 'umum', 'is_active' => true]);
    $categoryMap['umum'] = $cat->id;
}
$defaultCategoryId = $categoryMap['umum'];

$csvPath = 'C:/Users/eykel/Documents/MALIKU/MULIKU-PLASTIK02.csv';
$csv = Reader::createFromPath($csvPath, 'r');
$csv->setHeaderOffset(0);
$records = $csv->getRecords();

echo "\n=== 2. MEMULAI PROSES IMPORT MULIKU-PLASTIK02.csv ===\n";
$count = 0;
$index = 0;
DB::beginTransaction();
try {
    foreach ($records as $row) {
        $index++;
        if ($index % 500 === 0) {
            DB::commit();
            DB::beginTransaction();
            echo "   -> Memproses baris ke-$index...\n";
        }

        $name = trim($row['Name'] ?? '');
        if ($name === '') continue;

        $sku = trim($row['SKU'] ?? '');
        if ($sku === '') $sku = 'SKU-PLASTIK02-' . md5($name . '-' . $index);

        $catName = trim($row['Category'] ?? $row['Type'] ?? '');
        $catId = $defaultCategoryId;
        if ($catName !== '') {
            $catKey = strtolower($catName);
            if (!isset($categoryMap[$catKey])) {
                $newCat = Category::create(['name' => $catName, 'slug' => Str::slug($catName) . '-' . substr(md5($index . '-2'), 0, 4), 'is_active' => true]);
                $categoryMap[$catKey] = $newCat->id;
            }
            $catId = $categoryMap[$catKey];
        }

        $price = (float) str_replace(['Rp', '.', ','], '', $row['Price'] ?? 0);
        $costPrice = (float) str_replace(['Rp', '.', ','], '', $row['BuyingPrice'] ?? 0);
        $isActive = (stripos($row['IsActive'] ?? '', 'true') !== false || ($row['IsActive'] ?? '') === '1');
        $trackInventory = (stripos($row['TrackInventory'] ?? '', 'true') !== false || ($row['TrackInventory'] ?? '') === '1');
        $alertAtStock = (int) ($row['AlertAtStock'] ?? 3);
        $soldoutStrategy = (stripos($row['ContinueSellingWhenSoldOut'] ?? '', 'true') !== false) ? 'continue' : 'stop';

        $product = Product::where('outlet_id', 2)->where('sku', $sku)->first();
        if (!$product) {
            $productSlug = Str::slug($name);
            if (empty($productSlug)) $productSlug = 'product-plastik02-' . $index;
            $productSlug .= '-' . substr(md5($sku . '-2-' . $index), 0, 6);

            $product = Product::create([
                'outlet_id' => 2, // KHUSUS MULIKU PLASTIK02
                'name' => $name,
                'slug' => $productSlug,
                'sku' => $sku,
                'category_id' => $catId,
                'base_price' => $price,
                'cost_price' => $costPrice,
                'is_active' => $isActive,
                'track_inventory' => $trackInventory,
                'alert_at_stock' => $alertAtStock,
                'soldout_strategy' => $soldoutStrategy,
            ]);
            $count++;
        } else {
            $product->update([
                'name' => $name,
                'category_id' => $catId,
                'base_price' => $price > 0 ? $price : $product->base_price,
                'is_active' => $isActive,
            ]);
        }

        // Jumlahkan semua kolom inventaris jika ada
        $qty = 0;
        foreach ($row as $colName => $colVal) {
            if (stripos($colName, 'Inventory') !== false || stripos($colName, 'Stok') !== false) {
                if (is_numeric($colVal)) {
                    $qty += (float) $colVal;
                }
            }
        }

        Inventory::updateOrCreate(
            ['product_id' => $product->id, 'outlet_id' => 2],
            ['quantity' => $qty, 'low_stock_threshold' => $alertAtStock > 0 ? $alertAtStock : 3]
        );
    }
    DB::commit();
    echo " -> Muliku Plastik02 berhasil diimport: $count produk tersimpan khusus di Toko ID 2.\n";
} catch (\Exception $e) {
    DB::rollBack();
    die("ERROR SAAT IMPORT PLASTIK02: " . $e->getMessage() . "\n");
}

$duration = round(microtime(true) - $startTime, 2);
echo "\n=======================================================\n";
echo "SELESAI DALAM $duration DETIK!\n";
echo "Total Produk di Muliku Plastik02 (ID: 2): " . Product::where('outlet_id', 2)->count() . "\n";
echo "=======================================================\n";
