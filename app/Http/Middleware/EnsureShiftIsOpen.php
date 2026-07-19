<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureShiftIsOpen
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !is_null(auth()->user()->outlet_id)) {
            // Check if there is an active open shift session for this user
            $activeShift = \App\Models\ShiftSession::where('user_id', auth()->id())
                ->where('status', 'open')
                ->latest()
                ->first();

            // 1. CEK KEAMANAN: Jika kasir masih memiliki Shift yang TERBUKA, BLOKIR total upaya logout!
            if ($activeShift && ($request->is('*logout*') || $request->routeIs('*logout*'))) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['message' => 'Anda tidak dapat keluar akun karena Shift Kasir masih TERBUKA. Silakan tutup shift terlebih dahulu.'], 403);
                }
                return redirect()->to('/portal-kasir?blocked_logout=1')->with('error', '⚠️ Anda tidak dapat keluar akun karena Shift Kasir Anda masih TERBUKA! Silakan rekap dan tutup shift terlebih dahulu.');
            }

            // 2. Jika tidak ada shift terbuka, wajibkan buka shift
            if (!$activeShift) {
                // Allow logout or portal-kasir requests so they aren't trapped in a redirect loop
                if (
                    $request->is('admin/logout*') ||
                    $request->is('logout*') ||
                    $request->is('portal-kasir*') ||
                    $request->is('livewire*')
                ) {
                    return $next($request);
                }

                return redirect()->to('/portal-kasir?auto_open_shift=1');
            }
        }

        return $next($request);
    }
}
