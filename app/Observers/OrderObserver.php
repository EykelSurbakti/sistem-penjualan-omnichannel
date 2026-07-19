<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\ActivityLog;

class OrderObserver
{
    public function created(Order $order): void
    {
        ActivityLog::record(
            'CREATE',
            'Pesanan & Transaksi',
            "Membuat transaksi pesanan baru {$order->order_number} sebesar Rp " . number_format($order->total_amount, 0, ',', '.'),
            $order,
            null,
            $order->toArray()
        );

        try {
            $admins = \App\Models\User::whereNull('outlet_id')->get();
            if ($admins->isNotEmpty()) {
                $outletName = $order->outlet?->name ?: 'Cabang Toko';
                $notif = \Filament\Notifications\Notification::make()
                    ->title('🛍️ Pesanan Baru Masuk!')
                    ->body("Pesanan {$order->order_number} dari {$outletName} senilai Rp " . number_format($order->total_amount, 0, ',', '.'))
                    ->success()
                    ->icon('heroicon-o-shopping-bag');

                foreach ($admins as $admin) {
                    $admin->notifyNow($notif->toDatabase());
                    try {
                        $admin->notifyNow(new \App\Notifications\NewOrderWebPushNotification($order));
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error("WebPush dispatch exception for admin {$admin->id}: " . $e->getMessage() . " | " . $e->getFile() . ":" . $e->getLine());
                    }
                }
            }
        } catch (\Throwable $e) {
            // Abaikan kesalahan notifikasi agar transaksi kasir tidak terhambat
        }
    }

    public function updated(Order $order): void
    {
        if ($order->isDirty('payment_status') || $order->isDirty('fulfillment_status') || $order->isDirty('total_amount')) {
            ActivityLog::record(
                'UPDATE',
                'Pesanan & Transaksi',
                "Mengedit/memperbarui status pesanan {$order->order_number}",
                $order,
                $order->getOriginal(),
                $order->getChanges()
            );
        }
    }

    public function deleted(Order $order): void
    {
        ActivityLog::record(
            'DELETE',
            'Pesanan & Transaksi',
            "Menghapus/membatalkan pesanan {$order->order_number} sebesar Rp " . number_format($order->total_amount, 0, ',', '.'),
            $order,
            $order->toArray(),
            null
        );
    }
}
