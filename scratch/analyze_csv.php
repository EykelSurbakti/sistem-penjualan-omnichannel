<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use League\Csv\Reader;

$csvPath = 'C:/Users/eykel/Documents/MALIKU/maliku plastik.csv';
$csv = Reader::createFromPath($csvPath, 'r');
$csv->setHeaderOffset(0);
$records = iterator_to_array($csv->getRecords());

$totalRows        = count($records);
$trackTrue        = 0;
$trackFalse       = 0;
$alertStockValues = [];
$zeroStock        = 0;
$lowStockOurWay   = 0; // qty <= 5
$lowStockIseller  = 0; // qty <= AlertAtStock (per product)

foreach ($records as $row) {
    $track      = strtolower(trim($row['TrackInventory'] ?? 'true'));
    $alertStock = (int) trim($row['AlertAtStock'] ?? '0');
    $qty        = (float) ($row['Inventory MULIKU STORE 03'] ?? 0);
    $qty        = (int) round($qty);

    if ($track === 'true') {
        $trackTrue++;
    } else {
        $trackFalse++;
    }

    $alertStockValues[] = $alertStock;

    if ($qty <= 0) $zeroStock++;
    if ($qty <= 5) $lowStockOurWay++;
    if ($alertStock > 0 && $qty <= $alertStock) $lowStockIseller++;
}

$alertCounts = array_count_values($alertStockValues);
arsort($alertCounts);

echo "═══════════════════════════════════════════════════\n";
echo "  ANALISIS CSV: maliku plastik.csv\n";
echo "═══════════════════════════════════════════════════\n";
echo "  Total baris CSV         : {$totalRows}\n";
echo "  TrackInventory = True   : {$trackTrue}  ← iSeller tampilkan di Inventaris\n";
echo "  TrackInventory = False  : {$trackFalse}  ← iSeller TIDAK tampilkan\n";
echo "\n";
echo "  Stok = 0 (habis)        : {$zeroStock}\n";
echo "  Stok <= 5 (threshold 5) : {$lowStockOurWay}  ← yg kita pakai (salah)\n";
echo "  Stok <= AlertAtStock    : {$lowStockIseller}  ← yg iSeller pakai (benar)\n";
echo "\n";
echo "  Top 10 nilai AlertAtStock di CSV:\n";
$i = 0;
foreach ($alertCounts as $val => $count) {
    echo "    AlertAtStock={$val} → {$count} produk\n";
    if (++$i >= 10) break;
}
echo "═══════════════════════════════════════════════════\n";
