<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'GRAFIK TREN PENJUALAN TOKO';
    protected static ?string $description = 'Pergerakan omset penjualan berdasarkan periode waktu terpilih';

    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '210px';

    public ?string $filter = '7_days';

    protected $listeners = ['monitoring-outlet-changed' => '$refresh', 'monitoring-period-changed' => '$refresh'];

    protected function getData(): array
    {
        $activeFilter = session('monitoring_period', 'today');

        $query = Order::where('payment_status', 'paid');
        if (auth()->check() && auth()->user()->outlet_id) {
            $query->where('outlet_id', auth()->user()->outlet_id);
        } elseif (session('monitoring_outlet_id')) {
            $query->where('outlet_id', session('monitoring_outlet_id'));
        }

        $labels = [];
        $dataOmset = [];

        if ($activeFilter === 'today') {
            for ($h = 8; $h <= 22; $h += 2) {
                $labels[] = sprintf('%02d.00 WIB', $h);
                $startH = Carbon::today()->setTime($h, 0, 0);
                $endH = Carbon::today()->setTime($h + 1, 59, 59);
                $sum = (clone $query)->whereBetween('created_at', [$startH, $endH])->sum('total_amount');
                $dataOmset[] = (float) $sum;
            }
        } elseif ($activeFilter === 'yesterday') {
            for ($h = 8; $h <= 22; $h += 2) {
                $labels[] = sprintf('%02d.00 WIB', $h);
                $startH = Carbon::yesterday()->setTime($h, 0, 0);
                $endH = Carbon::yesterday()->setTime($h + 1, 59, 59);
                $sum = (clone $query)->whereBetween('created_at', [$startH, $endH])->sum('total_amount');
                $dataOmset[] = (float) $sum;
            }
        } elseif ($activeFilter === 'this_month') {
            $daysInMonth = Carbon::now()->daysInMonth;
            $salesByDate = (clone $query)
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dt = Carbon::now()->startOfMonth()->addDays($d - 1);
                $dateStr = $dt->toDateString();
                $labels[] = $dt->format('d M');
                $dataOmset[] = (float) ($salesByDate[$dateStr] ?? 0);
            }
        } elseif ($activeFilter === 'this_year' || $activeFilter === 'all_time') {
            $bulanIndo = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            for ($m = 1; $m <= 12; $m++) {
                $labels[] = $bulanIndo[$m - 1];
                $sum = (clone $query)->whereMonth('created_at', $m)->whereYear('created_at', Carbon::now()->year)->sum('total_amount');
                $dataOmset[] = (float) $sum;
            }
        } else {
            // 7_days default
            $salesByDate = (clone $query)
                ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
                ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            for ($i = 6; $i >= 0; $i--) {
                $dt = Carbon::now()->subDays($i);
                $dateStr = $dt->toDateString();
                $labels[] = $dt->format('D, d M');
                $dataOmset[] = (float) ($salesByDate[$dateStr] ?? 0);
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Omset Penjualan (Rp)',
                    'data' => $dataOmset,
                    'fill' => true,
                    'borderColor' => '#2563EB',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.12)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
