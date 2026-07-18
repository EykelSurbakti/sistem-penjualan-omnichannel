<?php
/**
 * SCRIPT: Import data untuk toko "Muliku Plastik01" (ID: 1) dari MULIKU-PLASTIK01.csv
 * Sekaligus membuat akun kasir mulikuplastik01@gmail.com / password
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

$startTime = microtime(true);

echo "1. Menyiapkan Toko 'Muliku Plastik01'...\n";
$outlet = Outlet::find(1);
if (!$outlet) {
    $outlet = Outlet::create([
        'id' => 1,
        'name' => 'Muliku Plastik01',
        'address' => 'Jl. Utama Muliku Plastik 01',
        'phone' => '081234567890',
        'is_active' => true,
    ]);
} else {
    $outlet->update(['name' => 'Muliku Plastik01']);
}
echo "   -> Toko ID {$outlet->id} siap: {$outlet->name}\n";

echo "2. Menyiapkan Akun Kasir 'mulikuplastik01@gmail.com'...\n";
$user = User::where('email', 'mulikuplastik01@gmail.com')->first();
if (!$user) {
    $user = User::create([
        'name' => 'Muliku Plastik01',
        'email' => 'mulikuplastik01@gmail.com',
        'password' => Hash::make('password'),
        'outlet_id' => $outlet->id,
    ]);
    echo "   -> Akun baru dibuat & dihubungkan ke toko {$outlet->name}\n";
} else {
    $user->update([
        'name' => 'Muliku Plastik01',
        'password' => Hash::make('password'),
        'outlet_id' => $outlet->id,
    ]);
    echo "   -> Akun diupdate & dihubungkan ke toko {$outlet->name}\n";
}

echo "3. Menyiapkan Kategori Default & Buffer...\n";
$categoryMap = [];
$categories = Category::all();
foreach ($categories as $cat) {
    $categoryMap[strtolower(trim($cat->name))] = $cat->id;
}

if (!isset($categoryMap['umum'])) {
    $cat = Category::create([
        'name' => 'UMUM',
        'slug' => 'umum',
        'is_active' => true,
    ]);
    $categoryMap['umum'] = $cat->id;
}
$defaultCategoryId = $categoryMap['umum'];

echo "4. Membaca & Mengimport file MULIKU-PLASTIK01.csv (5.740 baris)...\n";
$csvPath = 'C:/Users/eykel/Documents/MALIKU/MULIKU-PLASTIK01.csv';
if (!file_exists($csvPath)) {
    die("ERROR: File tidak ditemukan di $csvPath\n");
}

$csv = Reader::createFromPath($csvPath, 'r');
$csv->setHeaderOffset(0);
$records = $csv->getRecords();

$importedCount = 0;
$updatedCount = 0;
$skippedCount = 0;
$index = 0;

DB::beginTransaction();
try {
    foreach ($records as $row) {
        $index++;
        if ($index % 1000 === 0) {
            DB::commit();
            DB::beginTransaction();
            echo "   -> Memproses baris ke-$index...\n";
        }

        $name = trim($row['Name'] ?? '');
        if ($name === '') {
            $skippedCount++;
            continue;
        }

        $sku = trim($row['SKU'] ?? '');
        if ($sku === '') {
            $sku = 'SKU-MP01-' . md5($name . '-' . $index);
        }

        // Kategori
        $catName = trim($row['Type'] ?? $row['Tags'] ?? '');
        $categoryId = $defaultCategoryId;
        if ($catName !== '') {
            $catKey = strtolower($catName);
            if (!isset($categoryMap[$catKey])) {
                $slug = \Illuminate\Support\Str::slug($catName) . '-' . substr(md5(time() . $index), 0, 4);
                $newCat = Category::create([
                    'name' => $catName,
                    'slug' => $slug,
                    'is_active' => true,
                ]);
                $categoryMap[$catKey] = $newCat->id;
            }
            $categoryId = $categoryMap[$catKey];
        }

        // Harga
        $price = (float) str_replace(['Rp', '.', ','], '', $row['Price'] ?? 0);
        $costPrice = (float) str_replace(['Rp', '.', ','], '', $row['BuyingPrice'] ?? 0);

        // Status & Inventory Track
        $isActive = (stripos($row['IsActive'] ?? '', 'true') !== false || ($row['IsActive'] ?? '') === '1');
        $trackInventory = (stripos($row['TrackInventory'] ?? '', 'true') !== false || ($row['TrackInventory'] ?? '') === '1');
        $alertAtStock = (int) ($row['AlertAtStock'] ?? 3);
        $continueSelling = (stripos($row['ContinueSellingWhenSoldOut'] ?? '', 'true') !== false);
        $soldoutStrategy = $continueSelling ? 'continue' : 'stop';

        // Cari atau buat Product
        $product = Product::where('sku', $sku)->first();
        if (!$product) {
            $productSlug = \Illuminate\Support\Str::slug($name);
            if (empty($productSlug)) {
                $productSlug = 'product-' . $index;
            }
            $productSlug .= '-' . substr(md5($sku . '-' . $index), 0, 6);

            $product = Product::create([
                'name' => $name,
                'slug' => $productSlug,
                'sku' => $sku,
                'category_id' => $categoryId,
                'base_price' => $price,
                'cost_price' => $costPrice,
                'is_active' => $isActive,
                'track_inventory' => $trackInventory,
                'alert_at_stock' => $alertAtStock,
                'soldout_strategy' => $soldoutStrategy,
            ]);
            $importedCount++;
        } else {
            // Update nama / harga / status
            $product->update([
                'name' => $name,
                'category_id' => $categoryId,
                'base_price' => $price > 0 ? $price : $product->base_price,
                'is_active' => $isActive,
                'track_inventory' => $trackInventory,
                'alert_at_stock' => $alertAtStock,
                'soldout_strategy' => $soldoutStrategy,
            ]);
            $updatedCount++;
        }

        // Stok untuk Muliku Plastik01 (Outlet ID 1) -> dari kolom "Inventory Muliku Kosmetik"
        $qtyPlastik01 = (float) ($row['Inventory Muliku Kosmetik'] ?? 0);
        Inventory::updateOrCreate(
            [
                'product_id' => $product->id,
                'outlet_id' => $outlet->id, // 1
            ],
            [
                'quantity' => $qtyPlastik01,
                'low_stock_threshold' => $alertAtStock > 0 ? $alertAtStock : 3,
            ]
        );

        // Stok untuk MULIKU STORE 02 (Outlet ID 2) jika ada angkanya
        if (isset($row['Inventory MULIKU STORE 02']) && $row['Inventory MULIKU STORE 02'] !== '') {
            $qtyStore02 = (float) $row['Inventory MULIKU STORE 02'];
            Inventory::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'outlet_id' => 2,
                ],
                [
                    'quantity' => $qtyStore02,
                    'low_stock_threshold' => $alertAtStock > 0 ? $alertAtStock : 3,
                ]
            );
        }
    }
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    die("ERROR SAAT IMPORT: " . $e->getMessage() . "\n");
}

$duration = round(microtime(true) - $startTime, 2);
echo "\n=======================================================\n";
echo "SELESAI DALAM $duration DETIK!\n";
echo "Produk Baru Diimport  : $importedCount\n";
echo "Produk Diupdate (SKU): $updatedCount\n";
echo "Baris Dilewati        : $skippedCount\n";
echo "Toko Tujuan           : {$outlet->name} [ID: {$outlet->id}]\n";
echo "Akun Kasir            : mulikuplastik01@gmail.com\n";
echo "=======================================================\n";
