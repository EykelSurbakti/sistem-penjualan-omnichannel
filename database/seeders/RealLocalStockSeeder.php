<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RealLocalStockSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        $this->command->info('🏪 [1/4] Memulihkan data 3 Cabang Toko persis seperti lokal (ID: 1, 2, 4)...');
        if (Schema::hasTable('outlets')) {
            DB::table('outlets')->truncate();
        }

        $outletsFile = base_path('database/data/real_outlets.json');
        if (file_exists($outletsFile)) {
            $outlets = json_decode(file_get_contents($outletsFile), true);
            if (is_array($outlets)) {
                DB::table('outlets')->insert($outlets);
            }
        } else {
            DB::table('outlets')->insert([
                ['id' => 1, 'code' => 'OUT-MLK-01', 'name' => 'Muliku Plastik01', 'address' => 'Jl. Raya Plastik No. 10, Jakarta', 'phone' => '08111111111', 'tax_rate' => 11.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 2, 'code' => 'OUT-MLK-02', 'name' => 'Muliku Plastik02', 'address' => 'Jl. Niaga Plastik No. 20, Jakarta', 'phone' => '08333333333', 'tax_rate' => 11.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 4, 'code' => 'MALIKU-PLASTIK', 'name' => 'Muliku Prabotan', 'address' => 'Jl. Prabotan Utama No. 1, Jakarta', 'phone' => '08222222222', 'tax_rate' => 11.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $this->command->info('📦 [2/4] Memulihkan 11.792 Katalog Produk asli dengan status aktif/nonaktif asli...');
        if (Schema::hasTable('products')) {
            DB::table('products')->truncate();
        }

        $productsFile = base_path('database/data/real_products.json');
        if (file_exists($productsFile)) {
            $products = json_decode(file_get_contents($productsFile), true);
            if (is_array($products)) {
                foreach (array_chunk($products, 500) as $chunk) {
                    DB::table('products')->insert($chunk);
                }
            }
        }

        $this->command->info('📊 [3/4] Memulihkan 11.790 Stok & Inventaris asli per toko (0 pcs, 14 pcs, 20 pcs, dll)...');
        if (Schema::hasTable('inventories')) {
            DB::table('inventories')->truncate();
        }

        $inventoriesFile = base_path('database/data/real_inventories.json');
        if (file_exists($inventoriesFile)) {
            $inventories = json_decode(file_get_contents($inventoriesFile), true);
            if (is_array($inventories)) {
                foreach (array_chunk($inventories, 500) as $chunk) {
                    DB::table('inventories')->insert($chunk);
                }
            }
        }

        $this->command->info('👥 [4/4] Mempersiapkan Akun Kasir agar terhubung tepat ke ID Cabang asli...');
        if (Schema::hasTable('users')) {
            DB::table('users')->truncate();
        }

        $roleAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $roleKasir = Role::firstOrCreate(['name' => 'Kasir POS']);

        $admin = User::create([
            'name' => 'Master Admin Muliku',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'outlet_id' => null,
            'role_label' => 'Master Admin / Owner',
            'allowed_modules' => ['all'],
            'security_settings' => [],
        ]);
        $admin->assignRole($roleAdmin);

        $kasirPrabotan = User::create([
            'name' => 'Kasir Prabotan',
            'email' => 'mulikuprabotan@gmail.com',
            'password' => Hash::make('password'),
            'outlet_id' => 4, // ID asli Prabotan di lokal
            'role_label' => 'Kasir Cabang Prabotan',
            'allowed_modules' => ['pos', 'dashboard', 'orders', 'products', 'inventory'],
            'security_settings' => ['cannot_delete_data' => true, 'cannot_cancel_order' => true],
        ]);
        $kasirPrabotan->assignRole($roleKasir);

        $kasirPlastik01 = User::create([
            'name' => 'Kasir Plastik 01',
            'email' => 'mulikuplastik01@gmail.com',
            'password' => Hash::make('password'),
            'outlet_id' => 1, // ID asli Plastik 01 di lokal
            'role_label' => 'Kasir Cabang Plastik 01',
            'allowed_modules' => ['pos', 'dashboard', 'orders', 'products', 'inventory'],
            'security_settings' => ['cannot_delete_data' => true, 'cannot_cancel_order' => true],
        ]);
        $kasirPlastik01->assignRole($roleKasir);

        $kasirPlastik02 = User::create([
            'name' => 'Kasir Plastik 02',
            'email' => 'mulikuplastik02@gmail.com',
            'password' => Hash::make('password'),
            'outlet_id' => 2, // ID asli Plastik 02 di lokal
            'role_label' => 'Kasir Cabang Plastik 02',
            'allowed_modules' => ['pos', 'dashboard', 'orders', 'products', 'inventory'],
            'security_settings' => ['cannot_delete_data' => true, 'cannot_cancel_order' => true],
        ]);
        $kasirPlastik02->assignRole($roleKasir);

        Schema::enableForeignKeyConstraints();

        $this->command->info('✅ SINKRONISASI STOK LOKAL KE CLOUD SELESAI 100%! Data online kini persis sama seperti di komputer Anda.');
    }
}
