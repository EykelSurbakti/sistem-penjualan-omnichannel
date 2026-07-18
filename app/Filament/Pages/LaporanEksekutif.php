<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\ShiftSession;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanEksekutif extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Laporan';
    protected static ?string $title = 'Pusat Laporan Eksekutif';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.laporan-eksekutif';

    public string $activeReport = 'menu'; // menu, penjualan, produk, kasir, laba_rugi, pembayaran
    public ?int $selectedOutletId = null;
    public string $period = 'this_month';

    public static function shouldRegisterNavigation(): bool
    {
        // Hanya untuk Master Admin / Pemilik (tanpa outlet_id)
        return is_null(auth()->user()?->outlet_id);
    }

    public function mount()
    {
        if (auth()->check() && auth()->user()->outlet_id) {
            $this->redirect('/portal-kasir');
            return;
        }
    }

    public function setActiveReport(string $report)
    {
        $this->activeReport = $report;
    }

    public function setOutlet(?int $outletId)
    {
        $this->selectedOutletId = $outletId;
    }

    public function setPeriod(string $period)
    {
        $this->period = $period;
    }

    public function getOutletsProperty()
    {
        return Outlet::where('is_active', true)->get();
    }

    protected function getFilteredOrderQuery()
    {
        $query = Order::where('payment_status', 'paid');

        if ($this->selectedOutletId) {
            $query->where('outlet_id', $this->selectedOutletId);
        }

        $now = Carbon::now();
        if ($this->period === 'today') {
            $query->whereDate('created_at', $now->toDateString());
        } elseif ($this->period === '7_days') {
            $query->where('created_at', '>=', $now->copy()->subDays(7)->startOfDay());
        } elseif ($this->period === 'this_month') {
            $query->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
        } elseif ($this->period === 'this_year') {
            $query->whereYear('created_at', $now->year);
        }

        return $query;
    }

    public function getPenjualanReportProperty()
    {
        $orders = $this->getFilteredOrderQuery()
            ->selectRaw('DATE(created_at) as tanggal, COUNT(*) as total_transaksi, SUM(total_amount) as total_omset')
            ->groupBy('tanggal')
            ->orderByDesc('tanggal')
            ->get();

        $summary = [
            'total_omset' => $orders->sum('total_omset'),
            'total_transaksi' => $orders->sum('total_transaksi'),
            'rata_rata' => $orders->sum('total_transaksi') > 0 ? $orders->sum('total_omset') / $orders->sum('total_transaksi') : 0,
        ];

        return [
            'rows' => $orders,
            'summary' => $summary,
        ];
    }

    public function getProdukReportProperty()
    {
        $query = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid');

        if ($this->selectedOutletId) {
            $query->where('orders.outlet_id', $this->selectedOutletId);
        }

        $now = Carbon::now();
        if ($this->period === 'today') {
            $query->whereDate('orders.created_at', $now->toDateString());
        } elseif ($this->period === '7_days') {
            $query->where('orders.created_at', '>=', $now->copy()->subDays(7)->startOfDay());
        } elseif ($this->period === 'this_month') {
            $query->whereMonth('orders.created_at', $now->month)->whereYear('orders.created_at', $now->year);
        } elseif ($this->period === 'this_year') {
            $query->whereYear('orders.created_at', $now->year);
        }

        $items = $query->select(
            'products.name as product_name',
            'products.sku as product_sku',
            DB::raw('SUM(order_items.quantity) as total_qty'),
            DB::raw('SUM(order_items.total_price) as total_omset')
        )
            ->groupBy('products.name', 'products.sku')
            ->orderByDesc('total_omset')
            ->limit(30)
            ->get();

        return [
            'rows' => $items,
            'total_qty' => $items->sum('total_qty'),
            'total_omset' => $items->sum('total_omset'),
        ];
    }

    public function getPembayaranReportProperty()
    {
        $orders = $this->getFilteredOrderQuery()
            ->selectRaw('payment_method, COUNT(*) as total_transaksi, SUM(total_amount) as total_omset')
            ->groupBy('payment_method')
            ->get();

        return [
            'rows' => $orders,
            'total_omset' => $orders->sum('total_omset'),
            'total_transaksi' => $orders->sum('total_transaksi'),
        ];
    }

    public function getLabaRugiReportProperty()
    {
        $query = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid');

        if ($this->selectedOutletId) {
            $query->where('orders.outlet_id', $this->selectedOutletId);
        }

        $now = Carbon::now();
        if ($this->period === 'today') {
            $query->whereDate('orders.created_at', $now->toDateString());
        } elseif ($this->period === '7_days') {
            $query->where('orders.created_at', '>=', $now->copy()->subDays(7)->startOfDay());
        } elseif ($this->period === 'this_month') {
            $query->whereMonth('orders.created_at', $now->month)->whereYear('orders.created_at', $now->year);
        } elseif ($this->period === 'this_year') {
            $query->whereYear('orders.created_at', $now->year);
        }

        $items = $query->select(
            DB::raw('SUM(order_items.total_price) as gross_revenue'),
            DB::raw('SUM(order_items.quantity * COALESCE(products.cost_price, products.base_price * 0.7)) as total_hpp')
        )->first();

        $grossRev = (float) ($items?->gross_revenue ?? 0);
        $totalHpp = (float) ($items?->total_hpp ?? 0);
        $netProfit = $grossRev - $totalHpp;
        $marginPct = $grossRev > 0 ? ($netProfit / $grossRev) * 100 : 0;

        return [
            'gross_revenue' => $grossRev,
            'total_hpp' => $totalHpp,
            'net_profit' => $netProfit,
            'margin_pct' => round($marginPct, 2),
        ];
    }

    public function getKasirReportProperty()
    {
        $query = ShiftSession::with(['user', 'outlet']);

        if ($this->selectedOutletId) {
            $query->where('outlet_id', $this->selectedOutletId);
        }

        $now = Carbon::now();
        if ($this->period === 'today') {
            $query->where(function ($q) use ($now) {
                $q->whereDate('opened_at', $now->toDateString())
                  ->orWhere('status', 'open');
            });
        } elseif ($this->period === '7_days') {
            $query->where('opened_at', '>=', $now->copy()->subDays(7)->startOfDay());
        } elseif ($this->period === 'this_month') {
            $query->whereMonth('opened_at', $now->month)->whereYear('opened_at', $now->year);
        } elseif ($this->period === 'this_year') {
            $query->whereYear('opened_at', $now->year);
        }

        return $query->latest()->limit(50)->get();
    }
}
