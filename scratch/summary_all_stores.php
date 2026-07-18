<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Outlet;
use App\Models\User;
use App\Models\Product;

echo "=== RINGKASAN DATA SELURUH TOKO & KASIR DI SISTEM ===\n\n";

$outlets = [1, 2, 4];
foreach ($outlets as $oid) {
    $outlet = Outlet::find($oid);
    $user = User::where('outlet_id', $oid)->first();
    $productCount = Product::where('outlet_id', $oid)->count();
    $activeCount = Product::where('outlet_id', $oid)->where('is_active', true)->count();
    
    echo sprintf("Toko ID %d : %-18s | Email Kasir: %-26s | Total Produk: %d (Aktif: %d)\n",
        $oid,
        $outlet ? $outlet->name : 'N/A',
        $user ? $user->email : 'N/A',
        $productCount,
        $activeCount
    );
}
