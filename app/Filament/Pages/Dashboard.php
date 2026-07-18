<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Beranda';
    protected static ?string $title = 'Beranda';

    protected static string $view = 'filament.pages.dashboard';

    public ?int $selectedMonitoringOutletId = null;
    public string $period = 'today'; // today, yesterday, 7_days, this_month, this_year, all_time

    public function mount()
    {
        if (auth()->check() && auth()->user()->outlet_id) {
            $this->redirect('/portal-kasir');
            return;
        }

        $this->selectedMonitoringOutletId = session('monitoring_outlet_id', null);
        $this->period = session('monitoring_period', 'today');
    }

    public function setMonitoringOutlet(?int $outletId)
    {
        $this->selectedMonitoringOutletId = $outletId;
        session(['monitoring_outlet_id' => $outletId]);
        $this->dispatch('monitoring-outlet-changed');
        $this->dispatch('monitoring-period-changed');
    }

    public function setPeriod(string $period)
    {
        $this->period = $period;
        session(['monitoring_period' => $period]);
        $this->dispatch('monitoring-outlet-changed');
        $this->dispatch('monitoring-period-changed');
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\SalesChart::class,
            \App\Filament\Widgets\BestSellingProductsChart::class,
            \App\Filament\Widgets\TopProductSalesChart::class,
        ];
    }

    public function getOutletsProperty()
    {
        return Outlet::where('is_active', true)->get();
    }

    public function getExecutiveMetricsProperty(): array
    {
        $query = Order::where('payment_status', 'paid');
        if ($this->selectedMonitoringOutletId) {
            $query->where('outlet_id', $this->selectedMonitoringOutletId);
        }

        $now = now();
        if ($this->period === 'today') {
            $query->whereDate('created_at', $now->toDateString());
            $hoursElapsed = max(1, $now->hour + 1);
        } elseif ($this->period === 'week') {
            $query->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfDay()]);
            $hoursElapsed = max(1, $now->diffInHours($now->copy()->startOfWeek()));
        } elseif ($this->period === 'month') {
            $query->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfDay()]);
            $hoursElapsed = max(1, $now->diffInHours($now->copy()->startOfMonth()));
        } else {
            // 3_months
            $query->where('created_at', '>=', $now->copy()->subMonths(3)->startOfDay());
            $hoursElapsed = max(1, 90 * 24);
        }

        $netSales = (clone $query)->sum('total_amount');
        $grossSales = $netSales;
        $avgHourly = $hoursElapsed > 0 ? $netSales / $hoursElapsed : 0;

        return [
            'net_sales' => (float) $netSales,
            'gross_sales' => (float) $grossSales,
            'avg_hourly' => (float) $avgHourly,
        ];
    }

    public function getHourlyChartDataProperty(): array
    {
        $query = Order::where('payment_status', 'paid');
        if ($this->selectedMonitoringOutletId) {
            $query->where('outlet_id', $this->selectedMonitoringOutletId);
        }

        $now = now();
        if ($this->period === 'today') {
            $labels = ['0:00', '03:00', '06:00', '09:00', '12:00', '15:00', '18:00', '21:00'];
            $data = [];
            $hours = [0, 3, 6, 9, 12, 15, 18, 21];

            foreach ($hours as $idx => $h) {
                $start = $now->copy()->setTime($h, 0, 0);
                $endH = $idx < count($hours) - 1 ? $hours[$idx + 1] - 1 : 23;
                $end = $now->copy()->setTime($endH, 59, 59);

                $sum = (clone $query)->whereDate('created_at', $now->toDateString())
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('total_amount');
                $data[] = (float) $sum;
            }

            return ['labels' => $labels, 'data' => $data];
        } elseif ($this->period === 'week') {
            $labels = [];
            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $dt = $now->copy()->subDays($i);
                $labels[] = $dt->format('D d/m');
                $sum = (clone $query)->whereDate('created_at', $dt->toDateString())->sum('total_amount');
                $data[] = (float) $sum;
            }
            return ['labels' => $labels, 'data' => $data];
        } elseif ($this->period === 'month') {
            $labels = [];
            $data = [];
            for ($i = 3; $i >= 0; $i--) {
                $startWeek = $now->copy()->subWeeks($i)->startOfWeek();
                $endWeek = $now->copy()->subWeeks($i)->endOfWeek();
                $labels[] = 'Mg ' . (4 - $i);
                $sum = (clone $query)->whereBetween('created_at', [$startWeek, $endWeek])->sum('total_amount');
                $data[] = (float) $sum;
            }
            return ['labels' => $labels, 'data' => $data];
        } else {
            // 3 months
            $labels = [];
            $data = [];
            for ($i = 2; $i >= 0; $i--) {
                $m = $now->copy()->subMonths($i);
                $labels[] = $m->format('M Y');
                $sum = (clone $query)->whereMonth('created_at', $m->month)->whereYear('created_at', $m->year)->sum('total_amount');
                $data[] = (float) $sum;
            }
            return ['labels' => $labels, 'data' => $data];
        }
    }

    public function getTopProductsBarDataProperty(): array
    {
        $query = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid');

        if ($this->selectedMonitoringOutletId) {
            $query->where('orders.outlet_id', $this->selectedMonitoringOutletId);
        }

        $items = $query->select('products.name as product_name', DB::raw('SUM(order_items.quantity) as total_qty'))
            ->groupBy('products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        if ($items->isEmpty()) {
            return [
                'labels' => ['Kotak Pensil Kaleng', 'Acuan 110', 'Acuan 139', 'Acuan 143', 'Board Master'],
                'data' => [15, 12, 10, 8, 5],
            ];
        }

        $labels = [];
        $data = [];
        foreach ($items as $item) {
            $labels[] = str($item->product_name)->limit(18);
            $data[] = (int) $item->total_qty;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    public function getTopProductsPieDataProperty(): array
    {
        $query = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid');

        if ($this->selectedMonitoringOutletId) {
            $query->where('orders.outlet_id', $this->selectedMonitoringOutletId);
        }

        $items = $query->select(
            'products.name as product_name',
            DB::raw('SUM(order_items.total_price) as total_rev'),
            DB::raw('SUM(order_items.quantity) as total_qty')
        )
            ->groupBy('products.name')
            ->orderByDesc('total_rev')
            ->limit(5)
            ->get();

        if ($items->isEmpty()) {
            return [
                'labels' => ['Kotak Pensil Kaleng', 'Acuan 110', 'Lainnya'],
                'data' => [50, 30, 20],
                'percentages' => ['50%', '30%', '20%'],
                'counts' => ['5', '3', '2'],
            ];
        }

        $totalRev = $items->sum('total_rev') ?: 1;
        $labels = [];
        $data = [];
        $percentages = [];
        $counts = [];

        foreach ($items as $item) {
            $labels[] = str($item->product_name)->limit(20);
            $rev = (float) $item->total_rev;
            $pct = round(($rev / $totalRev) * 100, 2);
            $data[] = $rev;
            $percentages[] = number_format($pct, 2, ',', '.') . '%';
            $counts[] = (int) $item->total_qty;
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'percentages' => $percentages,
            'counts' => $counts,
        ];
    }

    public function getTodayCashierOrdersCountProperty(): int
    {
        if (!auth()->check()) {
            return 0;
        }

        $query = Order::query()->whereDate('created_at', now()->toDateString());

        if (auth()->user()->outlet_id) {
            $query->where('outlet_id', auth()->user()->outlet_id);
        }

        return $query->count();
    }
}
