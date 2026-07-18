<?php
/**
 * SCRIPT: Re-import data terpisah untuk Toko Muliku Prabotan (ID: 4) & Toko Muliku Plastik01 (ID: 1)
 * Pastikan tiap toko memiliki katalog produk (products.outlet_id) yang 100% terpisah dan mandiri!
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

echo "=== 0. BERSIHKAN DATA PRODUK & INVENTARIS SEBELUMNYA AGAR TERPISAH SEMPURNA ===\n";
DB::statement('SET FOREIGN_KEY_CHECKS=0;');
DB::table('inventories')->truncate();
DB::table('products')->truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');
echo " -> Tabel products & inventories berhasil dibersihkan.\n";

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

// =========================================================================================
// 1. IMPORT TOKO "Muliku Prabotan" (ID: 4) dari C:/Users/eykel/Documents/MALIKU/maliku plastik.csv
// =========================================================================================
echo "\n=== 1. IMPORT TOKO 'Muliku Prabotan' (ID: 4) ===\n";
$outlet4 = Outlet::find(4);
if (!$outlet4) {
    $outlet4 = Outlet::create(['id' => 4, 'name' => 'Muliku Prabotan', 'is_active' => true]);
} else {
    $outlet4->update(['name' => 'Muliku Prabotan']);
}

$user4 = User::where('email', 'mulikuprabotan@gmail.com')->first();
if (!$user4) {
    User::create(['name' => 'Muliku Prabotan', 'email' => 'mulikuprabotan@gmail.com', 'password' => Hash::make('password'), 'outlet_id' => 4]);
} else {
    $user4->update(['name' => 'Muliku Prabotan', 'password' => Hash::make('password'), 'outlet_id' => 4]);
}

$csvPath4 = 'C:/Users/eykel/Documents/MALIKU/maliku plastik.csv';
$csv4 = Reader::createFromPath($csvPath4, 'r');
$csv4->setHeaderOffset(0);
$records4 = $csv4->getRecords();

$count4 = 0;
$index4 = 0;
DB::beginTransaction();
try {
    foreach ($records4 as $row) {
        $index4++;
        $name = trim($row['Name'] ?? '');
        if ($name === '') continue;

        $sku = trim($row['SKU'] ?? '');
        if ($sku === '') $sku = 'SKU-PRABOT-' . md5($name . '-' . $index4);

        $catName = trim($row['Category'] ?? $row['Type'] ?? '');
        $catId = $defaultCategoryId;
        if ($catName !== '') {
            $catKey = strtolower($catName);
            if (!isset($categoryMap[$catKey])) {
                $newCat = Category::create(['name' => $catName, 'slug' => Str::slug($catName) . '-' . substr(md5($index4), 0, 4), 'is_active' => true]);
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

        $product = Product::where('outlet_id', 4)->where('sku', $sku)->first();
        if (!$product) {
            $productSlug = Str::slug($name);
            if (empty($productSlug)) $productSlug = 'product-prabot-' . $index4;
            $productSlug .= '-' . substr(md5($sku . '-4-' . $index4), 0, 6);

            $product = Product::create([
                'outlet_id' => 4, // KHUSUS MULIKU PRABOTAN
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
            $count4++;
        } else {
            $product->update([
                'name' => $name,
                'category_id' => $catId,
                'base_price' => $price > 0 ? $price : $product->base_price,
                'is_active' => $isActive,
            ]);
        }

        $qtyStore03 = (float) ($row['Inventory MULIKU STORE 03'] ?? 0);
        $qtyPlastik = (float) ($row['Inventory Muliku Plastik'] ?? 0);
        $qty = $qtyStore03 + $qtyPlastik;
        Inventory::updateOrCreate(
            ['product_id' => $product->id, 'outlet_id' => 4],
            ['quantity' => $qty, 'low_stock_threshold' => $alertAtStock > 0 ? $alertAtStock : 3]
        );
    }
    DB::commit();
    echo " -> Muliku Prabotan berhasil diimport: $count4 produk tersimpan khusus di Toko ID 4.\n";
} catch (\Exception $e) {
    DB::rollBack();
    die("ERROR SAAT IMPORT PRABOTAN: " . $e->getMessage() . "\n");
}

// =========================================================================================
// 2. IMPORT TOKO "Muliku Plastik01" (ID: 1) dari C:/Users/eykel/Documents/MALIKU/MULIKU-PLASTIK01.csv
// =========================================================================================
echo "\n=== 2. IMPORT TOKO 'Muliku Plastik01' (ID: 1) ===\n";
$outlet1 = Outlet::find(1);
if (!$outlet1) {
    $outlet1 = Outlet::create(['id' => 1, 'name' => 'Muliku Plastik01', 'is_active' => true]);
} else {
    $outlet1->update(['name' => 'Muliku Plastik01']);
}

$user1 = User::where('email', 'mulikuplastik01@gmail.com')->first();
if (!$user1) {
    User::create(['name' => 'Muliku Plastik01', 'email' => 'mulikuplastik01@gmail.com', 'password' => Hash::make('password'), 'outlet_id' => 1]);
} else {
    $user1->update(['name' => 'Muliku Plastik01', 'password' => Hash::make('password'), 'outlet_id' => 1]);
}

$csvPath1 = 'C:/Users/eykel/Documents/MALIKU/MULIKU-PLASTIK01.csv';
$csv1 = Reader::createFromPath($csvPath1, 'r');
$csv1->setHeaderOffset(0);
$records1 = $csv1->getRecords();

$count1 = 0;
$index1 = 0;
DB::beginTransaction();
try {
    foreach ($records1 as $row) {
        $index1++;
        if ($index1 % 1000 === 0) {
            DB::commit();
            DB::beginTransaction();
            echo "   -> Memproses baris ke-$index1...\n";
        }

        $name = trim($row['Name'] ?? '');
        if ($name === '') continue;

        $sku = trim($row['SKU'] ?? '');
        if ($sku === '') $sku = 'SKU-PLASTIK01-' . md5($name . '-' . $index1);

        $catName = trim($row['Category'] ?? $row['Type'] ?? '');
        $catId = $defaultCategoryId;
        if ($catName !== '') {
            $catKey = strtolower($catName);
            if (!isset($categoryMap[$catKey])) {
                $newCat = Category::create(['name' => $catName, 'slug' => Str::slug($catName) . '-' . substr(md5($index1 . '-1'), 0, 4), 'is_active' => true]);
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

        $product = Product::where('outlet_id', 1)->where('sku', $sku)->first();
        if (!$product) {
            $productSlug = Str::slug($name);
            if (empty($productSlug)) $productSlug = 'product-plastik01-' . $index1;
            $productSlug .= '-' . substr(md5($sku . '-1-' . $index1), 0, 6);

            $product = Product::create([
                'outlet_id' => 1, // KHUSUS MULIKU PLASTIK01
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
            $count1++;
        } else {
            $product->update([
                'name' => $name,
                'category_id' => $catId,
                'base_price' => $price > 0 ? $price : $product->base_price,
                'is_active' => $isActive,
            ]);
        }

        $qtyStore02 = (float) ($row['Inventory MULIKU STORE 02'] ?? 0);
        $qtyKosmetik = (float) ($row['Inventory Muliku Kosmetik'] ?? 0);
        $qty = $qtyStore02 + $qtyKosmetik;
        Inventory::updateOrCreate(
            ['product_id' => $product->id, 'outlet_id' => 1],
            ['quantity' => $qty, 'low_stock_threshold' => $alertAtStock > 0 ? $alertAtStock : 3]
        );
    }
    DB::commit();
    echo " -> Muliku Plastik01 berhasil diimport: $count1 produk tersimpan khusus di Toko ID 1.\n";
} catch (\Exception $e) {
    DB::rollBack();
    die("ERROR SAAT IMPORT PLASTIK01: " . $e->getMessage() . "\n");
}

$duration = round(microtime(true) - $startTime, 2);
echo "\n=======================================================\n";
echo "SELESAI DALAM $duration DETIK!\n";
echo "Total Produk di Muliku Prabotan (ID: 4) : " . Product::where('outlet_id', 4)->count() . "\n";
echo "Total Produk di Muliku Plastik01 (ID: 1): " . Product::where('outlet_id', 1)->count() . "\n";
echo "=======================================================\n";
