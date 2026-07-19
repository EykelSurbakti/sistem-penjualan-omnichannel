<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        try {
            $roleAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
            
            $admin = User::firstOrCreate([
                'email' => 'admin@gmail.com',
            ], [
                'name' => 'Master Admin Muliku',
                'password' => Hash::make('password'),
                'outlet_id' => null,
                'role_label' => 'Master Admin / Owner',
                'allowed_modules' => ['all'],
                'security_settings' => [],
            ]);

            // Pastikan password dan role selalu valid
            $admin->update([
                'password' => Hash::make('password'),
                'outlet_id' => null,
            ]);
            $admin->assignRole($roleAdmin);
        } catch (\Throwable $e) {
            // Log if needed
        }
    }

    public function down(): void
    {
        // No action needed
    }
};
