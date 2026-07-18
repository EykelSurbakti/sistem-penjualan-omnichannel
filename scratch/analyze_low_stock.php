<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use League\Csv\Reader;

$csvPath = 'C:/Users/eykel/Documents/MALIKU/maliku plastik.csv';
$csv = Reader::createFromPath($csvPath, 'r');
$csv->setHeaderOffset(0);
$records = iterator_to_array($csv->getRecords());

$counts = [];
for ($t = 0; $t <= 100; $t++) {
    $counts[$t] = 0;
}
$countsZero = 0;
$countsGreaterThanZero = [];
for ($t = 1; $t <= 100; $t++) {
    $countsGreaterThanZero[$t] = 0;
}

foreach ($records as $row) {
    $track = strtolower(trim($row['TrackInventory'] ?? 'true'));
    if ($track !== 'true') continue; // only check TrackInventory = true (4404 products)

    $qty = (float) ($row['Inventory MULIKU STORE 03'] ?? 0);
    $qty = (int) round($qty);

    if ($qty === 0) $countsZero++;

    for ($t = 0; $t <= 50; $t++) {
        if ($qty <= $t) {
            $counts[$t]++;
        }
    }
    for ($t = 1; $t <= 50; $t++) {
        if ($qty > 0 && $qty <= $t) {
            $countsGreaterThanZero[$t]++;
        }
    }
}

echo "Total TrackInventory = True: 4404\n";
echo "Qty = 0: {$countsZero}\n";
echo "\nChecking Qty <= T (including 0):\n";
for ($t = 0; $t <= 15; $t++) {
    echo "  Qty <= {$t}: {$counts[$t]}\n";
}
echo "\nChecking 0 < Qty <= T (excluding 0):\n";
for ($t = 1; $t <= 15; $t++) {
    echo "  0 < Qty <= {$t}: {$countsGreaterThanZero[$t]}\n";
}
