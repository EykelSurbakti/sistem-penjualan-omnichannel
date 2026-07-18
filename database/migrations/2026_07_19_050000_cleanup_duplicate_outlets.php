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

            // 1. Pastikan outlet resmi ID 1, 2, 4 memiliki nama dan kode persis seperti lokal
            DB::table('outlets')->updateOrInsert(['id' => 1], ['code' => 'OUT-MLK-01', 'name' => 'Muliku Plastik01', 'address' => 'Jl. Plastik No. 1, Jakarta', 'phone' => '08222222222', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('outlets')->updateOrInsert(['id' => 2], ['code' => 'OUT-MLK-02', 'name' => 'Muliku Plastik02', 'address' => 'Jl. Plastik No. 2, Jakarta', 'phone' => '08333333333', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('outlets')->updateOrInsert(['id' => 4], ['code' => 'MALIKU-PLASTIK', 'name' => 'Muliku Prabotan', 'address' => 'Jl. Prabotan Utama No. 1, Jakarta', 'phone' => '08111111111', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);

            // 2. Gabungkan/pindahkan data dari outlet duplikat ke outlet resmi ID 1, 2, 4
            $dupPls1 = DB::table('outlets')->where('code', 'OUT-PLS-01')->orWhere(function($q) {
                $q->where('name', 'Muliku Plastik01')->where('id', '!=', 1);
            })->pluck('id');
            if ($dupPls1->isNotEmpty()) {
                DB::table('products')->whereIn('outlet_id', $dupPls1)->update(['outlet_id' => 1]);
                DB::table('inventories')->whereIn('outlet_id', $dupPls1)->update(['outlet_id' => 1]);
                DB::table('users')->whereIn('outlet_id', $dupPls1)->update(['outlet_id' => 1]);
            }

            $dupPls2 = DB::table('outlets')->where('code', 'OUT-PLS-02')->orWhere(function($q) {
                $q->where('name', 'Muliku Plastik02')->where('id', '!=', 2);
            })->pluck('id');
            if ($dupPls2->isNotEmpty()) {
                DB::table('products')->whereIn('outlet_id', $dupPls2)->update(['outlet_id' => 2]);
                DB::table('inventories')->whereIn('outlet_id', $dupPls2)->update(['outlet_id' => 2]);
                DB::table('users')->whereIn('outlet_id', $dupPls2)->update(['outlet_id' => 2]);
            }

            $dupPrb = DB::table('outlets')->where('code', 'OUT-PRB-01')->orWhere(function($q) {
                $q->where('name', 'Muliku Prabotan')->where('id', '!=', 4);
            })->pluck('id');
            if ($dupPrb->isNotEmpty()) {
                DB::table('products')->whereIn('outlet_id', $dupPrb)->update(['outlet_id' => 4]);
                DB::table('inventories')->whereIn('outlet_id', $dupPrb)->update(['outlet_id' => 4]);
                DB::table('users')->whereIn('outlet_id', $dupPrb)->update(['outlet_id' => 4]);
            }

            // 3. Pastikan user akun menunjuk ke outlet resmi
            DB::table('users')->where('email', 'mulikuplastik01@gmail.com')->update(['outlet_id' => 1]);
            DB::table('users')->where('email', 'mulikuplastik02@gmail.com')->update(['outlet_id' => 2]);
            DB::table('users')->where('email', 'mulikuprabotan@gmail.com')->update(['outlet_id' => 4]);
            DB::table('users')->where('email', 'nike@maliku.com')->update(['outlet_id' => 4]);

            // 4. Hapus seluruh outlet selain ID 1, 2, dan 4
            DB::table('outlets')->whereNotIn('id', [1, 2, 4])->delete();

            Schema::enableForeignKeyConstraints();
        } catch (\Throwable $e) {
            Schema::enableForeignKeyConstraints();
            // Amankan agar tidak crash
        }
    }

    public function down(): void
    {
        //
    }
};
