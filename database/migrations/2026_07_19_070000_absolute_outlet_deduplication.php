<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
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

            // 2. Cari semua ID outlet selain 1, 2, dan 4 (seperti ID 3, 5, 6, dll)
            $extraIds = DB::table('outlets')->whereNotIn('id', [1, 2, 4])->pluck('id');
            if ($extraIds->isNotEmpty()) {
                $tablesWithOutletId = ['products', 'inventories', 'users', 'orders', 'shifts', 'cashier_sessions'];

                foreach ($extraIds as $exId) {
                    $name = DB::table('outlets')->where('id', $exId)->value('name') ?? '';
                    $code = DB::table('outlets')->where('id', $exId)->value('code') ?? '';
                    
                    $targetId = 4; // Default ke Muliku Prabotan
                    if (stripos($name, 'Plastik01') !== false || stripos($code, 'PLS-01') !== false || stripos($code, 'MLK-01') !== false) {
                        $targetId = 1;
                    } elseif (stripos($name, 'Plastik02') !== false || stripos($code, 'PLS-02') !== false || stripos($code, 'MLK-02') !== false) {
                        $targetId = 2;
                    }

                    // Pindahkan semua data di seluruh tabel ke target ID resmi
                    foreach ($tablesWithOutletId as $tbl) {
                        if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'outlet_id')) {
                            DB::table($tbl)->where('outlet_id', $exId)->update(['outlet_id' => $targetId]);
                        }
                    }
                }

                // Hapus paksa dengan SET FOREIGN_KEY_CHECKS=0
                DB::table('outlets')->whereNotIn('id', [1, 2, 4])->delete();
            }

            // 3. Pastikan juga jika ada duplikasi berdasarkan kode atau nama, hapus yang ID-nya bukan 1, 2, atau 4
            DB::table('outlets')->whereNotIn('id', [1, 2, 4])->delete();

            // 4. Standarisasi penunjukan akun user ke outlet resmi
            DB::table('users')->where('email', 'mulikuplastik01@gmail.com')->update(['outlet_id' => 1]);
            DB::table('users')->where('email', 'mulikuplastik02@gmail.com')->update(['outlet_id' => 2]);
            DB::table('users')->where('email', 'mulikuprabotan@gmail.com')->update(['outlet_id' => 4]);
            DB::table('users')->where('email', 'nike@maliku.com')->update(['outlet_id' => 4]);

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    public function down(): void
    {
        //
    }
};
