<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected $listeners = ['monitoring-outlet-changed' => '$refresh'];

    protected function getStats(): array
    {
        $ordersQuery = Order::query();

        if (auth()->check() && auth()->user()->outlet_id) {
            $ordersQuery->where('outlet_id', auth()->user()->outlet_id);
        } elseif (session('monitoring_outlet_id')) {
            $ordersQuery->where('outlet_id', session('monitoring_outlet_id'));
        }

        $paidQuery = (clone $ordersQuery)->where('payment_status', 'paid');
        $totalSales = $paidQuery->sum('total_amount');
        $totalOrders = (clone $ordersQuery)->count();
        $avgSales = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        $storeLabel = 'Semua Toko MALIKU (Konsolidasi)';
        if (auth()->check() && auth()->user()->outlet_id) {
            $storeLabel = 'Toko: ' . (auth()->user()->outlet?->name ?? 'Toko Saya');
        } elseif (session('monitoring_outlet_id')) {
            $outlet = \App\Models\Outlet::find(session('monitoring_outlet_id'));
            $storeLabel = 'Filter Toko: ' . ($outlet?->name ?? 'Toko Terpilih');
        }

        return [
            Stat::make('Penjualan Bersih', 'Rp ' . number_format($totalSales, 0, ',', '.'))
                ->description($storeLabel)
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart([15, 20, 28, 45, 60, 85, 120]),

            Stat::make('Penjualan Kotor', 'Rp ' . number_format($totalSales, 0, ',', '.'))
                ->description('Total omset bruto terproses')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary')
                ->chart([15, 20, 28, 45, 60, 85, 120]),

            Stat::make('Rata-rata per Transaksi', 'Rp ' . number_format($avgSales, 0, ',', '.'))
                ->description('Nilai belanja rata-rata pelanggan')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),

            Stat::make('Total Transaksi', number_format($totalOrders, 0, ',', '.') . ' Pesanan')
                ->description('Dari kasir toko & pesanan online')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),
        ];
    }
}
