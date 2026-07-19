<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewOrderWebPushNotification extends Notification
{
    use Queueable;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        $outletName = $this->order->outlet?->name ?: 'Cabang Toko';
        $amount = number_format($this->order->total_amount, 0, ',', '.');

        return (new WebPushMessage)
            ->title('🛍️ Pesanan Baru - ' . $outletName)
            ->icon('/favicon.ico')
            ->body("Omset: Rp {$amount} ({$this->order->order_number})")
            ->action('Lihat Pesanan', 'view_order')
            ->options(['TTL' => 3600]);
    }
}
