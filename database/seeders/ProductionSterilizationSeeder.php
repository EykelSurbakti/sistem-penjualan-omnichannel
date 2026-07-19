<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\ShiftSession;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class ProductionSterilizationSeeder extends Seeder
{
    /**
     * Run the production sterilization and reset process.
     * Command: php artisan db:seed --class=ProductionSterilizationSeeder
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        $this->command->info('🧹 [1/5] Menghapus seluruh riwayat pesanan (Orders & Order Items)...');
        DB::table('order_items')->truncate();
        DB::table('orders')->truncate();

        $this->command->info('🔒 [2/5] Menghapus seluruh riwayat absen & shift kasir (Shift Sessions)...');
        DB::table('shift_sessions')->truncate();

        $this->command->info('📋 [3/5] Membersihkan log aktivitas (Activity Logs)...');
        if (Schema::hasTable('activity_logs')) {
            DB::table('activity_logs')->truncate();
        }
        if (Schema::hasTable('notifications')) {
            DB::table('notifications')->truncate();
        }

        $this->command->info('🏪 [4/5] Mengatur ulang 3 Cabang Toko Resmi (Muliku Prabotan & Plastik)...');
        DB::table('outlets')->truncate();

        $prabotanId = DB::table('outlets')->insertGetId([
            'code' => 'OUT-PRB',
            'name' => 'Muliku Prabotan',
            'address' => 'Jl. Prabotan Utama No. 1, Jakarta',
            'phone' => '08222222222',
            'tax_rate' => 11.00,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $plastik01Id = DB::table('outlets')->insertGetId([
            'code' => 'OUT-PL1',
            'name' => 'Muliku Plastik 01',
            'address' => 'Jl. Raya Plastik No. 10, Jakarta',
            'phone' => '08111111111',
            'tax_rate' => 11.00,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $plastik02Id = DB::table('outlets')->insertGetId([
            'code' => 'OUT-PL2',
            'name' => 'Muliku Plastik 02',
            'address' => 'Jl. Niaga Plastik No. 20, Jakarta',
            'phone' => '08333333333',
            'tax_rate' => 11.00,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('👥 [5/5] Memastikan hanya 4 Akun Pengguna Resmi di sistem...');
        // Hapus semua user terlebih dahulu agar bersih steril
        DB::table('users')->truncate();

        $roleAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $roleKasir = Role::firstOrCreate(['name' => 'Kasir POS']);

        // 1. Super Admin / Owner
        $admin = User::create([
            'name' => 'Master Admin Muliku',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'outlet_id' => null, // null = Master Monitoring (Semua Outlet)
            'role_label' => 'Master Admin / Owner',
            'allowed_modules' => ['all'],
            'security_settings' => [],
        ]);
        $admin->assignRole($roleAdmin);

        // 2. Kasir Prabotan
        $kasirPrabotan = User::create([
            'name' => 'Kasir Prabotan',
            'email' => 'mulikuprabotan@gmail.com',
            'password' => Hash::make('password'),
            'outlet_id' => $prabotanId,
            'role_label' => 'Kasir Cabang Prabotan',
            'allowed_modules' => ['pos', 'dashboard', 'orders', 'products', 'inventory'],
            'security_settings' => ['cannot_delete_data' => true, 'cannot_cancel_order' => true],
        ]);
        $kasirPrabotan->assignRole($roleKasir);

        // 3. Kasir Plastik 01
        $kasirPlastik01 = User::create([
            'name' => 'Kasir Plastik 01',
            'email' => 'mulikuplastik01@gmail.com',
            'password' => Hash::make('password'),
            'outlet_id' => $plastik01Id,
            'role_label' => 'Kasir Cabang Plastik 01',
            'allowed_modules' => ['pos', 'dashboard', 'orders', 'products', 'inventory'],
            'security_settings' => ['cannot_delete_data' => true, 'cannot_cancel_order' => true],
        ]);
        $kasirPlastik01->assignRole($roleKasir);

        // 4. Kasir Plastik 02
        $kasirPlastik02 = User::create([
            'name' => 'Kasir Plastik 02',
            'email' => 'mulikuplastik02@gmail.com',
            'password' => Hash::make('password'),
            'outlet_id' => $plastik02Id,
            'role_label' => 'Kasir Cabang Plastik 02',
            'allowed_modules' => ['pos', 'dashboard', 'orders', 'products', 'inventory'],
            'security_settings' => ['cannot_delete_data' => true, 'cannot_cancel_order' => true],
        ]);
        $kasirPlastik02->assignRole($roleKasir);

        // Reset dan sesuaikan stok inventori untuk 3 cabang baru ini
        $this->command->info('📦 Menyesuaikan stok awal katalog untuk 3 cabang toko...');
        DB::table('inventories')->truncate();
        $products = Product::all();
        $outlets = [$prabotanId, $plastik01Id, $plastik02Id];

        foreach ($products as $product) {
            foreach ($outlets as $oid) {
                DB::table('inventories')->insert([
                    'product_id' => $product->id,
                    'outlet_id' => $oid,
                    'quantity' => 100, // Stok default 100 siap jual
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::enableForeignKeyConstraints();

        $this->command->info('✅ STERILISASI SIAP! Database kini 100% steril dengan 3 Toko dan 4 Akun Resmi.');
    }
}
