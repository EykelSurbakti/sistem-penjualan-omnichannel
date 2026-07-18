<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use League\Csv\Reader;
use App\Models\Product;

$csvPath = 'C:/Users/eykel/Documents/MALIKU/MULIKU-PLASTIK01.csv';
$csv = Reader::createFromPath($csvPath, 'r');
$csv->setHeaderOffset(0);

$existingCount = Product::count();
$overlapCount = 0;
$newCount = 0;
$emptySkuCount = 0;

foreach ($csv->getRecords() as $row) {
    $sku = trim($row['SKU'] ?? '');
    if ($sku === '') {
        $emptySkuCount++;
        continue;
    }
    if (Product::where('sku', $sku)->exists()) {
        $overlapCount++;
    } else {
        $newCount++;
    }
}

echo "Existing products in DB: $existingCount\n";
echo "MULIKU-PLASTIK01.csv rows: overlap with existing SKU = $overlapCount | new SKU = $newCount | empty SKU = $emptySkuCount\n";
