<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use League\Csv\Reader;

$csvPath = 'C:/Users/eykel/Documents/MALIKU/MULIKU-PLASTIK01.csv';
$csv = Reader::createFromPath($csvPath, 'r');
$csv->setHeaderOffset(0);

$skus = ['Pva11', 'PP61418181', 'BATANGDUNIA78', 'ASSOY8199', 'Sp00', 'BAK8299'];

foreach ($csv->getRecords() as $row) {
    if (in_array(trim($row['SKU'] ?? ''), $skus)) {
        echo sprintf(
            "%-30s | SKU: %-13s | Kosmetik: %-8s | STORE 02: %-8s | SUM: %-8s\n",
            substr($row['Name'], 0, 30),
            $row['SKU'],
            $row['Inventory Muliku Kosmetik'],
            $row['Inventory MULIKU STORE 02'],
            ((float)$row['Inventory Muliku Kosmetik'] + (float)$row['Inventory MULIKU STORE 02'])
        );
    }
}
