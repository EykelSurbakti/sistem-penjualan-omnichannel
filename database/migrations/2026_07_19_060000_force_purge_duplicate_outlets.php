<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::disableForeignKeyConstraints();

            // 1. Kunci ID resmi 1, 2, dan 4 persis seperti di database lokal
            DB::table('outlets')->updateOrInsert(['id' => 1], ['code' => 'OUT-MLK-01', 'name' => 'Muliku Plastik01', 'address' => 'Jl. Plastik No. 1, Jakarta', 'phone' => '08222222222', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('outlets')->updateOrInsert(['id' => 2], ['code' => 'OUT-MLK-02', 'name' => 'Muliku Plastik02', 'address' => 'Jl. Plastik No. 2, Jakarta', 'phone' => '08333333333', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('outlets')->updateOrInsert(['id' => 4], ['code' => 'MALIKU-PLASTIK', 'name' => 'Muliku Prabotan', 'address' => 'Jl. Prabotan Utama No. 1, Jakarta', 'phone' => '08111111111', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);

            // 2. Ambil seluruh ID outlet yang bukan 1, 2, atau 4
            $extraIds = DB::table('outlets')->whereNotIn('id', [1, 2, 4])->pluck('id');
            if ($extraIds->isNotEmpty()) {
                foreach ($extraIds as $exId) {
                    $name = DB::table('outlets')->where('id', $exId)->value('name') ?? '';
                    $targetId = 4; // Default ke Muliku Prabotan
                    if (stripos($name, 'Plastik01') !== false || stripos($name, 'PLS-01') !== false) {
                        $targetId = 1;
                    } elseif (stripos($name, 'Plastik02') !== false || stripos($name, 'PLS-02') !== false) {
                        $targetId = 2;
                    }

                    // Pindahkan semua relasi dari ID duplikat ke ID resmi
                    DB::table('products')->where('outlet_id', $exId)->update(['outlet_id' => $targetId]);
                    DB::table('inventories')->where('outlet_id', $exId)->update(['outlet_id' => $targetId]);
                    DB::table('users')->where('outlet_id', $exId)->update(['outlet_id' => $targetId]);
                    DB::table('orders')->where('outlet_id', $exId)->update(['outlet_id' => $targetId]);
                }
                // Hapus permanen semua outlet selain ID 1, 2, dan 4
                DB::table('outlets')->whereNotIn('id', [1, 2, 4])->delete();
            }

            // 3. Pastikan user lokal menunjuk ke outlet resmi
            DB::table('users')->where('email', 'mulikuplastik01@gmail.com')->update(['outlet_id' => 1]);
            DB::table('users')->where('email', 'mulikuplastik02@gmail.com')->update(['outlet_id' => 2]);
            DB::table('users')->where('email', 'mulikuprabotan@gmail.com')->update(['outlet_id' => 4]);
            DB::table('users')->where('email', 'nike@maliku.com')->update(['outlet_id' => 4]);

            Schema::enableForeignKeyConstraints();
        } catch (\Throwable $e) {
            Schema::enableForeignKeyConstraints();
        }
    }

    public function down(): void
    {
        //
    }
};
