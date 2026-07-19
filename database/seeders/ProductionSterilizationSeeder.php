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
        if (Schema::hasTable('order_items')) {
            DB::table('order_items')->truncate();
        }
        if (Schema::hasTable('orders')) {
            DB::table('orders')->truncate();
        }

        $this->command->info('🔒 [2/5] Menghapus seluruh riwayat absen & shift kasir (Shift Sessions)...');
        if (Schema::hasTable('shift_user')) {
            DB::table('shift_user')->truncate();
        }
        if (Schema::hasTable('shift_sessions')) {
            DB::table('shift_sessions')->truncate();
        }

        $this->command->info('📋 [3/5] Membersihkan log aktivitas (Activity Logs)...');
        if (Schema::hasTable('activity_logs')) {
            DB::table('activity_logs')->truncate();
        }
        if (Schema::hasTable('notifications')) {
            DB::table('notifications')->truncate();
        }

        $this->command->info('🏪 [4/5] Mengatur ulang 3 Cabang Toko Resmi & Memulihkan Stok Asli Lokal...');
        $this->call(RealLocalStockSeeder::class);
    }
}
