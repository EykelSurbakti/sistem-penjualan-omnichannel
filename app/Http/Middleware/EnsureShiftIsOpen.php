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
