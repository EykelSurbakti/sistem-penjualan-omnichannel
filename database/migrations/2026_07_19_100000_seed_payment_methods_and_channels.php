<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Pastikan Channels ada (ID 1 = POS, ID 2 = Web Online)
        if (Schema::hasTable('channels')) {
            DB::table('channels')->updateOrInsert(['id' => 1], [
                'code' => 'CHN-POS',
                'name' => 'Point of Sale (POS)',
                'type' => 'pos',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('channels')->updateOrInsert(['id' => 2], [
                'code' => 'CHN-WEB',
                'name' => 'Toko Online MALIKU',
                'type' => 'online_store',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Pastikan Payment Methods ada (ID 1 = Tunai/Cash, ID 2 = QRIS, ID 3 = Transfer, ID 4 = Debit/Kredit)
        if (Schema::hasTable('payment_methods')) {
            DB::table('payment_methods')->updateOrInsert(['id' => 1], [
                'code' => 'PAY-CASH',
                'name' => 'Tunai / Cash',
                'type' => 'cash',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('payment_methods')->updateOrInsert(['id' => 2], [
                'code' => 'PAY-QRIS',
                'name' => 'QRIS (GoPay / OVO / Dana)',
                'type' => 'qris',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('payment_methods')->updateOrInsert(['id' => 3], [
                'code' => 'PAY-TRF',
                'name' => 'Transfer Bank (BCA / Mandiri / BRI)',
                'type' => 'transfer',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('payment_methods')->updateOrInsert(['id' => 4], [
                'code' => 'PAY-CARD',
                'name' => 'Kartu Debit / Kredit (EDC)',
                'type' => 'card',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        //
    }
};
