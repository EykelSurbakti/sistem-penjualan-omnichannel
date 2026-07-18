<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // 1. Kunci dan standarisasi 3 outlet resmi: ID 1, ID 2, ID 4
            DB::table('outlets')->updateOrInsert(['id' => 1], ['code' => 'OUT-MLK-01', 'name' => 'Muliku Plastik01', 'address' => 'Jl. Plastik No. 1, Jakarta', 'phone' => '08222222222', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('outlets')->updateOrInsert(['id' => 2], ['code' => 'OUT-MLK-02', 'name' => 'Muliku Plastik02', 'address' => 'Jl. Plastik No. 2, Jakarta', 'phone' => '08333333333', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('outlets')->updateOrInsert(['id' => 4], ['code' => 'MALIKU-PLASTIK', 'name' => 'Muliku Prabotan', 'address' => 'Jl. Prabotan Utama No. 1, Jakarta', 'phone' => '08111111111', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);

            // 2. Hapus langsung data di tabel anak yang terkait ID duplikat (seperti ID 3, 5, 6) agar tidak melempar error 1062 saat di-update
            $childTables = ['inventories', 'products', 'orders', 'order_items', 'shifts', 'cashier_sessions'];
            foreach ($childTables as $tbl) {
                if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'outlet_id')) {
                    DB::table($tbl)->whereNotIn('outlet_id', [1, 2, 4])->delete();
                }
            }

            // 3. Pastikan user menunjuk ke ID 1, 2, atau 4
            DB::table('users')->whereNotIn('outlet_id', [1, 2, 4])->whereNotNull('outlet_id')->update(['outlet_id' => 4]);
            DB::table('users')->where('email', 'mulikuplastik01@gmail.com')->update(['outlet_id' => 1]);
            DB::table('users')->where('email', 'mulikuplastik02@gmail.com')->update(['outlet_id' => 2]);
            DB::table('users')->where('email', 'mulikuprabotan@gmail.com')->update(['outlet_id' => 4]);
            DB::table('users')->where('email', 'nike@maliku.com')->update(['outlet_id' => 4]);

            // 4. Hapus permanen seluruh baris di tabel outlets yang ID-nya bukan 1, 2, atau 4
            DB::table('outlets')->whereNotIn('id', [1, 2, 4])->delete();

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            Log::error("Purge leftover outlets error: " . $e->getMessage());
        }
    }

    public function down(): void
    {
        //
    }
};
