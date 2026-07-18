<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use League\Csv\Reader;

$csvPath = 'C:/Users/eykel/Documents/MALIKU/MULIKU-PLASTIK01.csv';
$csv = Reader::createFromPath($csvPath, 'r');
$csv->setHeaderOffset(0);

foreach ($csv->getRecords() as $row) {
    if (stripos(trim($row['Name'] ?? ''), 'COKLAT BATANG DUNIA') !== false) {
        echo "FOUND: " . $row['Name'] . " | SKU: " . $row['SKU'] . "\n";
        echo " - Inventory Muliku Kosmetik : [" . ($row['Inventory Muliku Kosmetik'] ?? 'NULL') . "]\n";
        echo " - Inventory MULIKU STORE 02 : [" . ($row['Inventory MULIKU STORE 02'] ?? 'NULL') . "]\n";
    }
}
