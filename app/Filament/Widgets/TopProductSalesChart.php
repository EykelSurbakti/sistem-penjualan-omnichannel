<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopProductSalesChart extends ChartWidget
{
    protected static ?string $heading = 'KONTRIBUSI PENJUALAN PRODUK (%)';
    protected static ?int $sort = 4;
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

        $topRevenueProducts = $query->select('products.name as product_name', DB::raw('SUM(order_items.total_price) as total_rev'))
            ->groupBy('products.name')
            ->orderByDesc('total_rev')
            ->limit(5)
            ->get();

        if ($topRevenueProducts->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Kontribusi (%)',
                        'data' => [1],
                        'backgroundColor' => ['#94A3B8'],
                    ],
                ],
                'labels' => ['Belum Ada Data'],
            ];
        }

        $labels = [];
        $data = [];
        $colors = ['#2563EB', '#F59E0B', '#10B981', '#EC4899', '#6366F1'];

        foreach ($topRevenueProducts as $item) {
            $labels[] = str($item->product_name)->limit(18);
            $data[] = (float) $item->total_rev;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Nominal (Rp)',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
