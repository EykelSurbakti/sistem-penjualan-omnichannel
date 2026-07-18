<?php

namespace App\Filament\Responses;

use App\Models\ShiftSession;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class CustomLoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        $user = auth()->user();

        // Jika user terikat dengan outlet/toko (contoh: mulikuprabotan@gmail.com / mulikuplastik01@gmail.com)
        if ($user && !is_null($user->outlet_id)) {
            // Tutup otomatis shift lama yang menggantung agar awal login WAJIB mengisi shift baru untuk hari/sesi ini
            ShiftSession::where('user_id', $user->id)
                ->where('status', 'open')
                ->update([
                    'status' => 'closed',
                    'closed_at' => now(),
                ]);

            // Awal login selalu arahkan ke portal kasir dengan pop up otomatis untuk mengisi Nama Kasir & Modal Awal!
            return redirect()->to('/portal-kasir?auto_open_shift=1');
        }

        // Jika Master Owner (tanpa outlet_id) -> ke dasbor eksekutif
        return redirect()->to('/admin');
    }
}
