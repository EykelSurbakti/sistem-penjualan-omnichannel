<?php

use App\Models\ShiftSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/portal-kasir', function () {
        $activeShift = ShiftSession::where('user_id', auth()->id())
            ->where('status', 'open')
            ->latest()
            ->first();

        return view('portal-kasir', [
            'activeShift' => $activeShift
        ]);
    })->name('portal-kasir');

    Route::post('/portal-kasir/buka-shift', function (Request $request) {
        $request->validate([
            'cashier_name' => 'required|string|max:100',
            'initial_cash' => 'required|numeric|min:0'
        ]);

        $outletId = auth()->user()->outlet_id ?? 1;
        $cashierName = $request->input('cashier_name', auth()->user()->name);

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

        // Tutup shift terbuka sebelumnya untuk user ini agar mulai sesi shift baru yang bersih
        ShiftSession::where('user_id', auth()->id())
            ->where('status', 'open')
            ->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);

        ShiftSession::create([
            'shift_id' => $shiftId,
            'user_id' => auth()->id(),
            'outlet_id' => $outletId,
            'cashier_name' => $cashierName,
            'initial_cash' => $request->input('initial_cash', 0),
            'status' => 'open',
            'opened_at' => now(),
        ]);

        return redirect('/admin/pos-kasir');
    })->name('buka-shift');

    Route::post('/portal-kasir/tutup-shift', function (Request $request) {
        $shift = ShiftSession::where('user_id', auth()->id())
            ->where('status', 'open')
            ->latest()
            ->first();

        if ($shift) {
            $shift->update([
                'status' => 'closed',
                'closed_at' => now(),
                'closing_cash' => $request->input('closing_cash', 0),
            ]);
        }

        return redirect('/portal-kasir');
    })->name('tutup-shift');

    Route::get('/katalog-toko', \App\Livewire\KatalogToko::class)->name('katalog-toko');
});
