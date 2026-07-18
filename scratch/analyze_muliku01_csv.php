<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use League\Csv\Reader;

$csvPath = 'C:/Users/eykel/Documents/MALIKU/MULIKU-PLASTIK01.csv';
$csv = Reader::createFromPath($csvPath, 'r');
$csv->setHeaderOffset(0);

$headers = $csv->getHeader();
echo "Headers matching 'Inventory':\n";
foreach ($headers as $h) {
    if (stripos($h, 'Inventory') !== false) {
        echo " - $h\n";
    }
}

$kosmetikTotal = 0;
$kosmetikNonZero = 0;
$store02Total = 0;
$store02NonZero = 0;
$rowCount = 0;

foreach ($csv->getRecords() as $row) {
    $rowCount++;
    $k = (float)($row['Inventory Muliku Kosmetik'] ?? 0);
    $s = (float)($row['Inventory MULIKU STORE 02'] ?? 0);

    $kosmetikTotal += $k;
    if ($k != 0) $kosmetikNonZero++;

    $store02Total += $s;
    if ($s != 0) $store02NonZero++;
}

echo "\nTotal Rows: $rowCount\n";
echo "Inventory Muliku Kosmetik: Total Stock = $kosmetikTotal, Non-Zero Rows = $kosmetikNonZero\n";
echo "Inventory MULIKU STORE 02: Total Stock = $store02Total, Non-Zero Rows = $store02NonZero\n";
