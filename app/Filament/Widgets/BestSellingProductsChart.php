<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BestSellingProductsChart extends ChartWidget
{
    protected static ?string $heading = 'PRODUK TERLARIS (UNIT TERJUAL)';
    protected static ?int $sort = 3;
    protected static ?string $maxHeight = '200px';
    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    protected $listeners = ['monitoring-outlet-changed' => '$refresh', 'monitoring-period-changed' => '$refresh'];

    protected function getData(): array
    {
        $query = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid');

        if (session('monitoring_outlet_id')) {
            $query->where('orders.outlet_id', session('monitoring_outlet_id'));
        }

        $period = session('monitoring_period', 'today');
        $now = \Illuminate\Support\Carbon::now();
        if ($period === 'today') {
            $query->whereDate('orders.created_at', $now->toDateString());
        } elseif ($period === 'yesterday') {
            $query->whereDate('orders.created_at', $now->copy()->subDay()->toDateString());
        } elseif ($period === '7_days') {
            $query->where('orders.created_at', '>=', $now->copy()->subDays(7)->startOfDay());
        } elseif ($period === 'this_month') {
            $query->whereMonth('orders.created_at', $now->month)->whereYear('orders.created_at', $now->year);
        } elseif ($period === 'this_year') {
            $query->whereYear('orders.created_at', $now->year);
        }

        $topProducts = $query->select('products.name as product_name', DB::raw('SUM(order_items.quantity) as total_qty'))
            ->groupBy('products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        if ($topProducts->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Terjual (Unit)',
                        'data' => [0],
                        'backgroundColor' => ['#94A3B8'],
                        'borderRadius' => 6,
                    ],
                ],
                'labels' => ['Belum Ada Data'],
            ];
        }

        $labels = [];
        $data = [];
        $colors = ['#2563EB', '#10B981', '#F59E0B', '#6366F1', '#EC4899'];

        foreach ($topProducts as $item) {
            $labels[] = str($item->product_name)->limit(18);
            $data[] = (int) $item->total_qty;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Terjual (Unit)',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
