<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class MergeDuplicateInventories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:merge-duplicate-inventories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merapikan database dengan menggabungkan baris inventaris yang ganda pada toko yang sama.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Memulai proses penggabungan inventaris ganda...");

        DB::beginTransaction();
        try {
            $duplicates = Inventory::select('product_id', 'outlet_id', DB::raw('COUNT(*) as count'))
                ->groupBy('product_id', 'outlet_id')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            $mergedCount = 0;
            $deletedCount = 0;

            foreach ($duplicates as $dup) {
                // Ambil semua inventory untuk product_id dan outlet_id ini
                $inventories = Inventory::where('product_id', $dup->product_id)
                    ->where('outlet_id', $dup->outlet_id)
                    ->orderBy('id', 'asc') // Yang pertama akan dipertahankan
                    ->get();

                if ($inventories->count() > 1) {
                    $primaryInv = $inventories->first();
                    $totalQty = 0;
                    
                    // Jumlahkan semua qty, dan hapus sisanya
                    foreach ($inventories as $index => $inv) {
                        $totalQty += $inv->quantity;
                        if ($index > 0) {
                            $inv->delete();
                            $deletedCount++;
                        }
                    }
                    
                    // Update qty di inventory utama
                    $primaryInv->quantity = $totalQty;
                    $primaryInv->save();
                    $mergedCount++;
                }
            }

            DB::commit();
            $this->info("=== PROSES SELESAI SUKSES 100% ===");
            $this->info("Berhasil menggabungkan {$mergedCount} produk yang memiliki stok ganda.");
            $this->info("Berhasil menghapus {$deletedCount} baris stok duplikat.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Gagal melakukan proses: " . $e->getMessage());
        }
    }
}
