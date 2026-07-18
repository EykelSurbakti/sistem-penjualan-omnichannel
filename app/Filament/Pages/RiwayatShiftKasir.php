<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\ShiftSession;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class RiwayatShiftKasir extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Pesanan & Transaksi';
    protected static ?string $navigationLabel = 'Riwayat Shift & Absen';
    protected static ?string $title = 'Register Shifts (Riwayat Shift Kasir)';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.riwayat-shift-kasir';

    public $initialCashInput = 500000;
    public $closingCashInput = null;

    public function mount()
    {
        if ($this->activeShift) {
            $this->closingCashInput = $this->expectedCash;
        } else {
            $this->closingCashInput = 500000;
        }
    }

    public function bukaShiftSekarang()
    {
        $this->validate([
            'initialCashInput' => 'required|numeric|min:0',
        ]);

        $outletId = auth()->user()->outlet_id ?? 1;

        $defaultShift = DB::table('shifts')->first();
        if (!$defaultShift) {
            $shiftId = DB::table('shifts')->insertGetId([
                'outlet_id' => $outletId,
                'name' => 'Shift Reguler Toko',
                'start_time' => '07:00:00',
                'end_time' => '22:00:00',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $shiftId = $defaultShift->id;
        }

        ShiftSession::create([
            'shift_id' => $shiftId,
            'user_id' => auth()->id(),
            'outlet_id' => $outletId,
            'initial_cash' => $this->initialCashInput,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        session()->flash('pesan_sukses', 'Mesin Kasir berhasil dibuka dan shift dimulai!');
    }

    public function tutupShiftSekarang($id)
    {
        $shift = ShiftSession::where('user_id', auth()->id())
            ->where('id', $id)
            ->where('status', 'open')
            ->first();

        if ($shift) {
            $finalClosing = $this->closingCashInput ?? $this->expectedCash;
            $shift->update([
                'status' => 'closed',
                'closed_at' => now(),
                'closing_cash' => $finalClosing,
            ]);

            session()->flash('pesan_sukses', 'Shift kasir berhasil ditutup dengan kecocokan saldo kas!');
        }
    }

    public function getActiveShiftProperty()
    {
        return ShiftSession::where('user_id', auth()->id())
            ->where('status', 'open')
            ->latest()
            ->first();
    }

    public function getActiveShiftSalesProperty()
    {
        $shift = $this->activeShift;
        if (!$shift) return 0;

        return Order::where('outlet_id', $shift->outlet_id)
            ->where('created_at', '>=', $shift->opened_at)
            ->where('payment_status', 'paid')
            ->sum('total_amount');
    }

    public function getExpectedCashProperty()
    {
        $shift = $this->activeShift;
        if (!$shift) return 0;

        return $shift->initial_cash + $this->activeShiftSales;
    }

    public function getHistoryShiftsProperty()
    {
        $query = ShiftSession::with(['user', 'outlet'])->latest();
        if (auth()->check() && !is_null(auth()->user()->outlet_id)) {
            $query->where('user_id', auth()->id());
        }
        return $query->get();
    }
}
