<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use League\Csv\Reader;

$csvPath = 'C:/Users/eykel/Documents/MALIKU/maliku plastik.csv';
$csv = Reader::createFromPath($csvPath, 'r');
$csv->setHeaderOffset(0);
$records = iterator_to_array($csv->getRecords());

$c1 = 0; $c2 = 0; $c3 = 0; $c4 = 0; $c5 = 0;
foreach ($records as $row) {
    $track = strtolower(trim($row['TrackInventory'] ?? 'true'));
    $active = strtolower(trim($row['IsActive'] ?? 'true'));
    if ($track !== 'true') continue;

    $qty = (float) ($row['Inventory MULIKU STORE 03'] ?? 0);
    $qty = (int) round($qty);

    if ($qty > 0 && $qty <= 3) $c1++;
    if ($active === 'true' && $qty > 0 && $qty <= 3) $c2++;
    if ($qty > 0 && $qty < 3) $c3++;
    if ($active === 'true' && $qty > 0 && $qty < 3) $c4++;
    if ($qty <= 3) $c5++;
}

echo "TrackInventory=true && 0 < Qty <= 3: {$c1}\n";
echo "TrackInventory=true && IsActive=true && 0 < Qty <= 3: {$c2}\n";
echo "TrackInventory=true && 0 < Qty < 3: {$c3}\n";
echo "TrackInventory=true && IsActive=true && 0 < Qty < 3: {$c4}\n";
