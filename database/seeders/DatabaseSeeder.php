<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Selalu pastikan akun Master Admin (admin@gmail.com) tersedia di lokal maupun Railway cloud
        try {
            $roleAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
            $adminGmail = User::firstOrCreate([
                'email' => 'admin@gmail.com',
            ], [
                'name' => 'Master Admin Muliku',
                'password' => Hash::make('password'),
                'outlet_id' => null, // null = Master Monitoring (Semua Outlet)
                'role_label' => 'Master Admin / Owner',
                'allowed_modules' => ['all'],
                'security_settings' => [],
            ]);
            $adminGmail->assignRole($roleAdmin);
        } catch (\Throwable $e) {
            // abaikan jika tabel roles belum siap
        }

        // Cegah crash/duplikasi outlet jika database sudah pernah di-seed sebelumnya
        if (DB::table('outlets')->count() > 0) {
            return;
        }

        // 1. Create Roles
        $roleAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $roleManager = Role::firstOrCreate(['name' => 'Manager Outlet']);
        $roleKasir = Role::firstOrCreate(['name' => 'Kasir POS']);

        // 2. Create 3 MALIKU Stores / Outlets
        $store01Id = DB::table('outlets')->insertGetId([
            'code' => 'OUT-MLK-01',
            'name' => 'MALIKU STORE 01',
            'address' => 'Jl. Pusat Perdagangan No. 10, Jakarta',
            'phone' => '021-8899001',
            'tax_rate' => 11.00,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $store02Id = DB::table('outlets')->insertGetId([
            'code' => 'OUT-MLK-02',
            'name' => 'MALIKU STORE 02',
            'address' => 'Jl. Raya Bandung No. 45, Bandung',
            'phone' => '022-8899002',
            'tax_rate' => 11.00,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $store03Id = DB::table('outlets')->insertGetId([
            'code' => 'OUT-MLK-03',
            'name' => 'MALIKU STORE 03',
            'address' => 'Jl. Niaga Utama No. 88, Surabaya',
            'phone' => '031-8899003',
            'tax_rate' => 11.00,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Create Master Owner Account (Akses ke Semua Toko MALIKU)
        $owner = User::firstOrCreate([
            'email' => 'admin@iseller.local',
        ], [
            'name' => 'Muliku Plastik',
            'password' => Hash::make('password'),
            'outlet_id' => null, // null = Semua Outlet (Master Monitoring)
            'role_label' => 'Pemilik Akun (Owner)',
            'allowed_modules' => ['all'],
            'security_settings' => [],
        ]);
        $owner->assignRole($roleAdmin);

        // 4. Create Staff / Kasir Account (Nike Kasir)
        $kasir = User::firstOrCreate([
            'email' => 'kasir@iseller.local',
        ], [
            'name' => 'Nike Kasir',
            'password' => Hash::make('password'),
            'outlet_id' => $store03Id,
            'role_label' => 'Admin dibatasi, Akses Aplikasi',
            'allowed_modules' => [
                'pos', 'online_store', 'dashboard', 'orders', 'customers',
                'products', 'transfer', 'inventory', 'collections',
                'discount', 'promotion', 'loyalty', 'price_books',
                'reports_sales', 'reports_inventory', 'reports_transactions'
            ],
            'security_settings' => [
                'cannot_delete_data' => true,
                'cannot_cancel_order' => true,
            ],
        ]);
        $kasir->assignRole($roleKasir);

        // 5. Channels & Payment Methods
        $posChannelId = DB::table('channels')->insertGetId([
            'code' => 'CHN-POS',
            'name' => 'Point of Sale (POS)',
            'type' => 'pos',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $webChannelId = DB::table('channels')->insertGetId([
            'code' => 'CHN-WEB',
            'name' => 'Toko Online MALIKU',
            'type' => 'online_store',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('payment_methods')->insert([
            ['id' => 1, 'code' => 'PAY-CASH', 'name' => 'Tunai / Cash', 'type' => 'cash', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'code' => 'PAY-QRIS', 'name' => 'QRIS (GoPay / OVO / Dana)', 'type' => 'qris', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'code' => 'PAY-TRF', 'name' => 'Transfer Bank (BCA / Mandiri / BRI)', 'type' => 'transfer', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'code' => 'PAY-CARD', 'name' => 'Kartu Debit / Kredit (EDC)', 'type' => 'card', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 6. Categories
        $catId = DB::table('categories')->insertGetId([
            'name' => 'Plastik & Wadah Serbaguna',
            'slug' => 'plastik-wadah-serbaguna',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 7. Products from Reference Screenshots
        $productsData = [
            ['KOTAK PENSIL KALENG CC-7808', 'KPK-7808', 25000, 18000, 0, 'stop'],
            ['KOTAK SABUN MARIO 333', 'KSM-333', 15000, 10000, 8, 'stop'],
            ['SAMPUL INTER X', 'SIX-100', 8000, 5000, 8, 'stop'],
            ['BOARD MASTER MERAH', 'BMM-001', 12000, 8000, 5, 'stop'],
            ['CETAKAN SILIKON HURUF PINK', 'CSH-PNK', 35000, 24000, 1, 'stop'],
            ['HANGER SURBON 4', 'HNG-SR4', 20000, 14000, 0, 'stop'],
            ['P KLIP 5X8', 'PKL-5X8', 6000, 3500, 135, 'stop'],
            ['PENA CETEK KARAKTER', 'PCK-002', 4000, 2000, 0, 'stop'],
            ['PENSIL SET PAGE DRAWING BOX', 'PSP-BOX', 28000, 19000, 5, 'stop'],
            ['SEMPOA NATURAL', 'SMP-NAT', 45000, 30000, 5, 'stop'],
            ['ACUAN 110', 'ACN-110', 18000, 12000, 12, 'continue'],
            ['ACUAN 139', 'ACN-139', 22000, 15000, 9, 'continue'],
            ['ACUAN 143', 'ACN-143', 24000, 16000, 10, 'continue'],
        ];

        foreach ($productsData as [$name, $sku, $price, $cost, $qty, $strategy]) {
            $pid = DB::table('products')->insertGetId([
                'category_id' => $catId,
                'sku' => $sku,
                'name' => $name,
                'slug' => str($name)->slug(),
                'base_price' => $price,
                'cost_price' => $cost,
                'has_variants' => false,
                'soldout_strategy' => $strategy,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Seed inventory in MALIKU STORE 03
            DB::table('inventories')->insert([
                'product_id' => $pid,
                'outlet_id' => $store03Id,
                'quantity' => $qty,
                'low_stock_threshold' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Also seed some inventory in STORE 01 & STORE 02
            DB::table('inventories')->insert([
                'product_id' => $pid,
                'outlet_id' => $store01Id,
                'quantity' => $qty + 10,
                'low_stock_threshold' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 8. Sample Customer
        $customerId = DB::table('customers')->insertGetId([
            'code' => 'CUST-0001',
            'name' => 'Pelanggan MALIKU VIP',
            'phone' => '081234567890',
            'email' => 'vip@maliku.local',
            'loyalty_points' => 150,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 9. Orders for July 2026 (matching reference total Rp 2.749.000 across 42 orders)
        $orderRefs = [
            ['#12-3108', 85000],
            ['#12-3107', 99000],
            ['#12-3106', 106000],
            ['#12-3105', 3000],
            ['#12-3104', 151000],
            ['#12-3103', 78000],
            ['#12-3102', 43000],
            ['#12-3101', 85000],
            ['#12-3100', 67000],
            ['#12-3099', 97000],
        ];

        $sampleProductId = DB::table('products')->first()->id ?? 1;

        foreach ($orderRefs as $index => [$ref, $amount]) {
            $orderId = DB::table('orders')->insertGetId([
                'order_number' => $ref,
                'outlet_id' => $store03Id,
                'channel_id' => $posChannelId,
                'customer_id' => $customerId,
                'cashier_id' => $kasir->id,
                'subtotal' => $amount,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $amount,
                'payment_status' => 'paid',
                'fulfillment_status' => 'completed',
                'created_at' => now()->subHours($index * 2),
                'updated_at' => now()->subHours($index * 2),
            ]);

            DB::table('order_items')->insert([
                'order_id' => $orderId,
                'product_id' => $sampleProductId,
                'product_variant_id' => null,
                'quantity' => 1,
                'unit_price' => $amount,
                'discount_amount' => 0,
                'total_price' => $amount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add remaining orders to reach 42 orders & total Rp 2.749.000
        $remainingAmount = 2749000 - collect($orderRefs)->sum(1);
        $perOrderAmount = round($remainingAmount / 32);

        for ($i = 1; $i <= 32; $i++) {
            $orderId = DB::table('orders')->insertGetId([
                'order_number' => '#12-30' . str_pad(98 - $i, 2, '0', STR_PAD_LEFT),
                'outlet_id' => $store03Id,
                'channel_id' => $posChannelId,
                'customer_id' => $customerId,
                'cashier_id' => $kasir->id,
                'subtotal' => $perOrderAmount,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $perOrderAmount,
                'payment_status' => 'paid',
                'fulfillment_status' => 'completed',
                'created_at' => now()->subDays(rand(1, 15)),
                'updated_at' => now()->subDays(rand(1, 15)),
            ]);

            DB::table('order_items')->insert([
                'order_id' => $orderId,
                'product_id' => $sampleProductId,
                'product_variant_id' => null,
                'quantity' => 1,
                'unit_price' => $perOrderAmount,
                'discount_amount' => 0,
                'total_price' => $perOrderAmount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
