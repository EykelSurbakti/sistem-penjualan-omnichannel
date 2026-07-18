<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- OUTLETS ---\n";
foreach (DB::table('outlets')->get() as $o) {
    echo "ID: {$o->id} | Name: {$o->name}\n";
}

echo "\n--- USERS ---\n";
foreach (DB::table('users')->get() as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Email: {$u->email} | OutletID: " . ($u->outlet_id ?? 'null') . "\n";
}
