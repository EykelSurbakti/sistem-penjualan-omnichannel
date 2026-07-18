<?php

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $roleAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $roleKasir = Role::firstOrCreate(['name' => 'Kasir POS']);

        // Outlets
        $storePrabotan = Outlet::firstOrCreate(['code' => 'OUT-PRB-01'], [
            'name' => 'Muliku Prabotan',
            'address' => 'Jl. Prabotan Utama No. 1, Jakarta',
            'phone' => '08111111111',
            'tax_rate' => 0.00,
            'is_active' => true,
        ]);

        $storePlastik01 = Outlet::firstOrCreate(['code' => 'OUT-PLS-01'], [
            'name' => 'Muliku Plastik01',
            'address' => 'Jl. Plastik No. 1, Jakarta',
            'phone' => '08222222222',
            'tax_rate' => 0.00,
            'is_active' => true,
        ]);

        $storePlastik02 = Outlet::firstOrCreate(['code' => 'OUT-PLS-02'], [
            'name' => 'Muliku Plastik02',
            'address' => 'Jl. Plastik No. 2, Jakarta',
            'phone' => '08333333333',
            'tax_rate' => 0.00,
            'is_active' => true,
        ]);

        // Users
        $u1 = User::firstOrCreate(['email' => 'mulikuprabotan@gmail.com'], [
            'name' => 'Muliku Prabotan',
            'password' => Hash::make('password'),
            'outlet_id' => $storePrabotan->id,
            'role_label' => 'Manager Toko Prabotan',
            'allowed_modules' => ['all'],
            'security_settings' => [],
        ]);
        $u1->assignRole($roleKasir);

        $u2 = User::firstOrCreate(['email' => 'mulikuplastik01@gmail.com'], [
            'name' => 'Muliku Plastik01',
            'password' => Hash::make('password'),
            'outlet_id' => $storePlastik01->id,
            'role_label' => 'Manager Toko Plastik 01',
            'allowed_modules' => ['all'],
            'security_settings' => [],
        ]);
        $u2->assignRole($roleKasir);

        $u3 = User::firstOrCreate(['email' => 'mulikuplastik02@gmail.com'], [
            'name' => 'Muliku Plastik02',
            'password' => Hash::make('password'),
            'outlet_id' => $storePlastik02->id,
            'role_label' => 'Manager Toko Plastik 02',
            'allowed_modules' => ['all'],
            'security_settings' => [],
        ]);
        $u3->assignRole($roleKasir);

        $u4 = User::firstOrCreate(['email' => 'nike@maliku.com'], [
            'name' => 'Nike Kasir Prabotan',
            'password' => Hash::make('password'),
            'outlet_id' => $storePrabotan->id,
            'role_label' => 'Kasir Prabotan',
            'allowed_modules' => ['pos', 'dashboard', 'orders', 'products', 'inventory', 'reports_sales'],
            'security_settings' => ['cannot_delete_data' => true],
        ]);
        $u4->assignRole($roleKasir);
    }

    public function down(): void
    {
        //
    }
};
