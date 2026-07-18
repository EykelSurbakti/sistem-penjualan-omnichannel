<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

$skus = ['BATANGDUNIA78', 'Pva11', 'PP61418181', 'ASSOY8199', 'Sp00', 'BAK8299'];
foreach ($skus as $s) {
    $p = Product::where('sku', $s)->first();
    if ($p) {
        $qty = $p->inventories->first()->quantity ?? 0;
        echo sprintf("%-35s | SKU: %-13s | Stok di Web Kita: %s pcs\n", substr($p->name, 0, 35), $p->sku, $qty);
    } else {
        echo "SKU $s not found\n";
    }
}
