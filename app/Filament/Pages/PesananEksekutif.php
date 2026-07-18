<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\Outlet;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class PesananEksekutif extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Pesanan';
    protected static ?string $title = 'Monitoring Pesanan';
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.pesanan-eksekutif';

    public ?int $selectedOutletId = null;
    public string $search = '';
    public ?int $selectedOrderId = null;

    public static function shouldRegisterNavigation(): bool
    {
        // Hanya muncul untuk Master Admin / Pemilik (tanpa outlet_id)
        return is_null(auth()->user()?->outlet_id);
    }

    public function mount()
    {
        if (auth()->check() && auth()->user()->outlet_id) {
            $this->redirect('/portal-kasir');
            return;
        }
    }

    public function setOutlet(?int $outletId)
    {
        $this->selectedOutletId = $outletId;
        $this->selectedOrderId = null;
    }

    public function selectOrder(int $orderId)
    {
        $this->selectedOrderId = $orderId;
    }

    public function closeDetail()
    {
        $this->selectedOrderId = null;
    }

    public function getOutletsProperty()
    {
        return Outlet::where('is_active', true)->get();
    }

    public function getOrdersProperty()
    {
        $query = Order::with(['outlet', 'cashier', 'customer', 'items.product', 'channel'])
            ->latest();

        if ($this->selectedOutletId) {
            $query->where('outlet_id', $this->selectedOutletId);
        }

        if (trim($this->search) !== '') {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                    ->orWhereHas('cashier', function ($uq) use ($search) {
                        $uq->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        return $query->limit(50)->get();
    }

    public function getSelectedOrderProperty()
    {
        if (!$this->selectedOrderId) {
            return null;
        }

        return Order::with(['outlet', 'cashier', 'customer', 'items.product', 'channel'])->find($this->selectedOrderId);
    }
}
