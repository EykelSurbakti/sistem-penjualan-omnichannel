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

        $newShift = ShiftSession::create([
            'shift_id' => $shiftId,
            'user_id' => auth()->id(),
            'outlet_id' => $outletId,
            'cashier_name' => $cashierName,
            'initial_cash' => $request->input('initial_cash', 0),
            'status' => 'open',
            'opened_at' => now(),
        ]);

        \App\Models\ActivityLog::record(
            'SHIFT',
            'Shift Kasir',
            "Membuka shift kasir baru atas nama '{$cashierName}' dengan modal awal Rp " . number_format($request->input('initial_cash', 0), 0, ',', '.'),
            $newShift
        );

        return redirect('/admin/pos-kasir');
    })->name('buka-shift');

    Route::post('/portal-kasir/tutup-shift', function (Request $request) {
        $shift = ShiftSession::where('user_id', auth()->id())
            ->where('status', 'open')
            ->latest()
            ->first();

        if ($shift) {
            $closingCash = (int) $request->input('closing_cash', 0);
            $shift->update([
                'status' => 'closed',
                'closed_at' => now(),
                'closing_cash' => $closingCash,
            ]);

            \App\Models\ActivityLog::record(
                'SHIFT',
                'Shift Kasir',
                "Menutup dan mengakhiri shift kasir atas nama '{$shift->cashier_name}' dengan uang akhir laci Rp " . number_format($closingCash, 0, ',', '.'),
                $shift
            );
        }

        return redirect('/portal-kasir');
    })->name('tutup-shift');

    Route::get('/katalog-toko', \App\Livewire\KatalogToko::class)->name('katalog-toko');

    Route::get('/admin/api/check-new-orders', function (\Illuminate\Http\Request $request) {
        if (!auth()->check() || auth()->user()->outlet_id !== null) {
            return response()->json(['has_new' => false], 403);
        }

        $lastCheck = $request->query('last_check');
        if (!$lastCheck) {
            return response()->json([
                'has_new' => false,
                'server_time' => now()->format('Y-m-d H:i:s')
            ]);
        }

        try {
            // Gunakan perbandingan langsung string format Y-m-d H:i:s agar sinkron sempurna dengan zona waktu server database
            $orders = \App\Models\Order::with('outlet')
                ->where('created_at', '>', $lastCheck)
                ->latest()
                ->get();

            return response()->json([
                'has_new' => $orders->isNotEmpty(),
                'server_time' => now()->format('Y-m-d H:i:s'),
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'outlet_name' => $order->outlet?->name ?: 'Cabang Toko',
                        'total_amount_formatted' => 'Rp ' . number_format($order->total_amount, 0, ',', '.'),
                        'created_at' => $order->created_at->format('H:i:s'),
                    ];
                })
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'has_new' => false,
                'server_time' => now()->format('Y-m-d H:i:s')
            ]);
        }
    })->name('admin.api.check-new-orders');

    Route::post('/admin/api/push-subscribe', function (\Illuminate\Http\Request $request) {
        if (!auth()->check() || auth()->user()->outlet_id !== null) {
            return response()->json(['success' => false], 403);
        }
        $endpoint = $request->input('endpoint');
        $key = $request->input('keys.p256dh');
        $token = $request->input('keys.auth');
        if ($endpoint && $key && $token) {
            auth()->user()->updatePushSubscription($endpoint, $key, $token);
        }
        return response()->json(['success' => true]);
    })->name('admin.api.push-subscribe');
});
