<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ShiftSession;
use App\Models\User;

$users = User::all();
foreach ($users as $u) {
    echo "User ID: {$u->id} | Name: {$u->name} | Email: {$u->email} | Outlet: {$u->outlet_id}\n";
    $shifts = ShiftSession::where('user_id', $u->id)->get();
    foreach ($shifts as $s) {
        echo "  -> Shift ID: {$s->id} | Status: {$s->status} | Cashier: {$s->cashier_name} | Opened At: {$s->opened_at}\n";
    }
}
