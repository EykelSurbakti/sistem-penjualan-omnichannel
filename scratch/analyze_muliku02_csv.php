<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use League\Csv\Reader;

$csvPath = 'C:/Users/eykel/Documents/MALIKU/MULIKU-PLASTIK02.csv';
if (!file_exists($csvPath)) {
    die("File not found: $csvPath\n");
}

$csv = Reader::createFromPath($csvPath, 'r');
$csv->setHeaderOffset(0);
$headers = $csv->getHeader();
$records = iterator_to_array($csv->getRecords());

echo "Total Rows in MULIKU-PLASTIK02.csv: " . count($records) . "\n";
echo "Headers: " . implode(', ', $headers) . "\n\n";

// Cek kolom inventory yang ada
$invCols = [];
foreach ($headers as $h) {
    if (stripos($h, 'Inventory') !== false || stripos($h, 'Stok') !== false) {
        $invCols[] = $h;
    }
}
echo "Inventory Columns found: " . implode(', ', $invCols) . "\n\n";

// Cek sample 5 baris pertama
$sample = array_slice($records, 0, 5);
foreach ($sample as $i => $row) {
    echo "Row " . ($i+1) . ": " . ($row['Name'] ?? 'N/A') . " (SKU: " . ($row['SKU'] ?? 'N/A') . ")\n";
    foreach ($invCols as $col) {
        echo "  - $col : " . ($row[$col] ?? '0') . "\n";
    }
}
