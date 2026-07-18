<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// 1. Rename Outlet ID 4 ke "Muliku Prabotan"
$outlet = Outlet::find(4);
if (!$outlet) {
    $outlet = Outlet::first();
}
if ($outlet) {
    $outlet->update([
        'name' => 'Muliku Prabotan',
    ]);
    echo "Outlet ID {$outlet->id} berhasil diubah namanya menjadi '{$outlet->name}'\n";
}

// 2. Buat atau update User "mulikuprabotan@gmail.com"
$user = User::where('email', 'mulikuprabotan@gmail.com')->first();
if (!$user) {
    $user = User::create([
        'name' => 'Muliku Prabotan',
        'email' => 'mulikuprabotan@gmail.com',
        'password' => Hash::make('password'),
        'outlet_id' => $outlet ? $outlet->id : 4,
    ]);
    echo "User baru mulikuprabotan@gmail.com berhasil dibuat dan dihubungkan ke toko {$outlet->name}\n";
} else {
    $user->update([
        'name' => 'Muliku Prabotan',
        'password' => Hash::make('password'),
        'outlet_id' => $outlet ? $outlet->id : 4,
    ]);
    echo "User mulikuprabotan@gmail.com berhasil diupdate dan dihubungkan ke toko {$outlet->name}\n";
}
