<x-filament-panels::page>
    @php
        $bulanIndoShort = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $isMasterOwner = is_null(auth()->user()?->outlet_id);
    @endphp

    {{-- PESAN BERHASIL --}}
    @if (session()->has('pesan_sukses'))
        <div style="padding: 14px 18px; border-radius: 12px; background: #ECFDF5; border: 1px solid #A7F3D0; color: #047857; font-weight: 800; font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <span>✓</span>
            <span>{{ session('pesan_sukses') }}</span>
        </div>
    @endif

    @if($isMasterOwner)
        {{-- ==================================================================================
             MODE MONITORING MASTER PEMILIK TOKO (TANPA FORM BUKA/TUTUP SHIFT)
             ================================================================================== --}}
        <div style="background: linear-gradient(135deg, #1E3A8A 0%, #1D4ED8 100%); border-radius: 18px; padding: 24px; color: #ffffff; box-shadow: 0 10px 25px rgba(30, 58, 138, 0.2); display: flex; flex-direction: column; gap: 16px;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                        <span style="background: rgba(255,255,255,0.2); color: #ffffff; font-size: 10px; font-weight: 800; padding: 3px 10px; border-radius: 20px; text-transform: uppercase;">
                            MONITORING LIVE CABANG
                        </span>
                        <span style="font-size: 12px; color: #BFDBFE; font-weight: 700;">
                            &bull; {{ now()->format('d M Y') }}
                        </span>
                    </div>
                    <h2 style="font-size: 22px; font-weight: 900; margin: 0; color: #ffffff;">
                        Monitoring Absen & Shift Kasir Cabang
                    </h2>
                    <p style="font-size: 13px; color: #DBEAFE; margin: 4px 0 0 0;">
                        Pantau jadwal absen masuk, kasir bertugas, modal awal laci, dan setoran akhir di seluruh cabang toko Anda secara real-time dari HP.
                    </p>
                </div>

                <div style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.25); padding: 12px 18px; border-radius: 14px; text-align: center;">
                    <span style="font-size: 11px; font-weight: 700; color: #DBEAFE; display: block; text-transform: uppercase;">Sesi Shift Terbuka Saat Ini</span>
                    <span style="font-size: 24px; font-weight: 900; color: #ffffff;">
                        {{ \App\Models\ShiftSession::where('status', 'open')->count() }} Cabang Aktif
                    </span>
                </div>
            </div>
        </div>

    @else
        {{-- ==================================================================================
             MODE OPERASIONAL KASIR CABANG (DENGAN TOMBOL BUKA/TUTUP SHIFT)
             ================================================================================== --}}
        @if($this->activeShift)
            @php
                $mIndex = $this->activeShift->opened_at ? $this->activeShift->opened_at->month - 1 : now()->month - 1;
                $tgl = $this->activeShift->opened_at ? $this->activeShift->opened_at->format('d') : now()->format('d');
                $jam = $this->activeShift->opened_at ? $this->activeShift->opened_at->format('H.i') : now()->format('H.i');
                $namaBulan = $bulanIndoShort[$mIndex] ?? 'Jul';
            @endphp

            <div style="background: #ffffff; border-radius: 16px; border: 2px solid #1E88E5; padding: 22px; box-shadow: 0 8px 24px rgba(30, 136, 229, 0.12); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div style="display: flex; align-items: center; gap: 18px;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <h2 style="font-size: 24px; font-weight: 900; color: #0F172A; margin: 0;">
                                {{ $namaBulan }} {{ $tgl }}
                            </h2>
                            <span style="background: #1E88E5; color: #ffffff; font-size: 12px; font-weight: 800; padding: 4px 12px; border-radius: 20px;">
                                Buka
                            </span>
                        </div>
                        <span style="font-size: 13px; color: #64748B; font-weight: 600; margin-top: 4px; display: block;">
                            Absen Masuk Pukul {{ $jam }} WIB &bull; Kasir: {{ $this->activeShift->cashier_name ?? auth()->user()->name }}
                        </span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                    <div style="text-align: right; background: #F1F5F9; padding: 10px 14px; border-radius: 12px;">
                        <span style="font-size: 10px; font-weight: 800; color: #64748B; text-transform: uppercase; display: block;">Modal Awal Laci</span>
                        <span style="font-size: 16px; font-weight: 900; color: #0F172A;">
                            Rp {{ number_format($this->activeShift->initial_cash, 0, ',', '.') }}
                        </span>
                    </div>

                    <div style="text-align: right; background: #ECFDF5; padding: 10px 14px; border-radius: 12px; border: 1px solid #A7F3D0;">
                        <span style="font-size: 10px; font-weight: 800; color: #047857; text-transform: uppercase; display: block;">+ Penjualan Sesi Ini</span>
                        <span style="font-size: 16px; font-weight: 900; color: #059669;">
                            Rp {{ number_format($this->activeShiftSales, 0, ',', '.') }}
                        </span>
                    </div>

                    <div style="text-align: right; background: #EFF6FF; padding: 10px 14px; border-radius: 12px; border: 1px solid #BFDBFE;">
                        <span style="font-size: 10px; font-weight: 800; color: #1D4ED8; text-transform: uppercase; display: block;">= Kas Seharusnya</span>
                        <span style="font-size: 18px; font-weight: 900; color: #1E40AF;">
                            Rp {{ number_format($this->expectedCash, 0, ',', '.') }}
                        </span>
                    </div>

                    {{-- FORM TUTUP SHIFT LANGSUNG DI SITU --}}
                    <form wire:submit.prevent="tutupShiftSekarang({{ $this->activeShift->id }})" style="display: flex; align-items: center; gap: 10px; background: #FFF1F2; padding: 10px 14px; border-radius: 12px; border: 1px solid #FECDD3;">
                        <div>
                            <span style="font-size: 10px; font-weight: 800; color: #9F1239; display: block;">Uang Akhir Tutup Laci (Otomatis/Cek Fisik):</span>
                            <input
                                type="number"
                                wire:model="closingCashInput"
                                placeholder="{{ $this->expectedCash }}"
                                required
                                style="width: 140px; padding: 6px 10px; border-radius: 8px; border: 1px solid #F43F5E; font-size: 14px; font-weight: 900; color: #9F1239; background: #ffffff;"
                            />
                        </div>
                        <button
                            type="submit"
                            style="padding: 10px 16px; border-radius: 10px; background: #E11D48; color: #ffffff; font-size: 12px; font-weight: 900; border: none; cursor: pointer; white-space: nowrap; box-shadow: 0 4px 10px rgba(225, 29, 72, 0.2);"
                        >
                            🔒 Tutup Shift
                        </button>
                    </form>
                </div>
            </div>
        @else
            {{-- KARTU JIKA BELUM ADA SHIFT TERBUKA HARI INI --}}
            <div style="background: #ffffff; border-radius: 16px; border: 1px dashed #CBD5E1; padding: 32px; text-align: center;">
                <div style="font-size: 42px; margin-bottom: 10px;">📋</div>
                <h3 style="font-size: 18px; font-weight: 900; color: #0F172A;">Belum Ada Shift Kasir Yang Terbuka</h3>
                <p style="font-size: 13px; color: #64748B; margin: 6px 0 20px 0;">
                    Tekan tombol di bawah untuk absen masuk dan memasukkan uang modal awal laci hari ini.
                </p>

                <form wire:submit.prevent="bukaShiftSekarang" style="display: inline-flex; align-items: center; gap: 10px; background: #F8FAFC; padding: 12px 18px; border-radius: 14px; border: 1px solid #CBD5E1;">
                    <div>
                        <label style="font-size: 11px; font-weight: 800; color: #475569; display: block; text-align: left; margin-bottom: 4px;">Modal Awal Laci (Rp)</label>
                        <input
                            type="number"
                            wire:model="initialCashInput"
                            required
                            style="width: 160px; padding: 8px 12px; border-radius: 8px; border: 2px solid #CBD5E1; font-size: 14px; font-weight: 800;"
                        />
                    </div>
                    <button
                        type="submit"
                        style="padding: 11px 20px; border-radius: 10px; background: linear-gradient(135deg, #1565C0 0%, #1E88E5 100%); color: #ffffff; font-weight: 900; font-size: 13px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;"
                    >
                        <span>🚀</span> Buka Shift Pagi
                    </button>
                </form>
            </div>
        @endif
    @endif

    {{-- TABEL SELURUH RIWAYAT SHIFT KASIR (100% BAHASA INDONESIA YANG RAPI DI HP) --}}
    <div style="background: #ffffff; border-radius: 16px; border: 1px solid #CBD5E1; overflow: hidden; box-shadow: 0 4px 14px rgba(0,0,0,0.02); margin-top: 20px;">
        <div style="padding: 16px 20px; background: #F8FAFC; border-bottom: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 style="font-size: 15px; font-weight: 900; color: #0F172A; margin: 0;">
                    📜 Laporan Riwayat Absen & Shift Kasir
                </h3>
                <span style="font-size: 12px; color: #64748B;">
                    Rekapitulasi jam kerja & kecocokan uang kasir cabang
                </span>
            </div>
            <span style="padding: 4px 12px; border-radius: 12px; background: #E0F2FE; color: #0284C7; font-size: 12px; font-weight: 800;">
                Total {{ $this->historyShifts->count() }} Sesi Tercatat
            </span>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #F1F5F9; border-bottom: 2px solid #E2E8F0; font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748B;">
                        <th style="padding: 14px 18px;">Tanggal & Jam Masuk</th>
                        <th style="padding: 14px 18px;">Staf Kasir</th>
                        <th style="padding: 14px 18px;">Cabang Toko</th>
                        <th style="padding: 14px 18px;">Status</th>
                        <th style="padding: 14px 18px;">Modal Awal</th>
                        <th style="padding: 14px 18px;">Penjualan Sesi</th>
                        <th style="padding: 14px 18px;">Uang Akhir Tutup</th>
                        <th style="padding: 14px 18px;">Selisih / Cocok</th>
                        <th style="padding: 14px 18px;">Jam Tutup</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->historyShifts as $shift)
                        @php
                            $mIdx = $shift->opened_at ? $shift->opened_at->month - 1 : 0;
                            $bulanStr = $bulanIndoShort[$mIdx] ?? 'Jul';
                            $shiftSales = \App\Models\Order::where('outlet_id', $shift->outlet_id)
                                ->where('created_at', '>=', $shift->opened_at)
                                ->where(function($q) use ($shift) {
                                    if ($shift->closed_at) {
                                        $q->where('created_at', '<=', $shift->closed_at);
                                    }
                                })
                                ->where('payment_status', 'paid')
                                ->sum('total_amount');
                            $expected = $shift->initial_cash + $shiftSales;
                            $selisih = $shift->closing_cash !== null ? ($shift->closing_cash - $expected) : null;
                        @endphp
                        <tr style="border-bottom: 1px solid #F1F5F9; font-size: 13px;">
                            <td style="padding: 14px 18px; font-weight: 800; color: #0F172A;">
                                {{ $bulanStr }} {{ $shift->opened_at ? $shift->opened_at->format('d, Y • H.i') : '-' }} WIB
                            </td>
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 800; color: #0F172A;">
                                    👤 {{ $shift->cashier_name ?? $shift->user?->name ?? 'Kasir' }}
                                </div>
                                @if($shift->cashier_name && $shift->cashier_name !== ($shift->user?->name ?? ''))
                                    <div style="font-size: 11px; color: #64748B; font-weight: 600; margin-top: 2px;">
                                        Akun: {{ $shift->user?->name }}
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 14px 18px; font-weight: 700; color: #0284C7;">
                                {{ $shift->outlet->name ?? 'Muliku Store' }}
                            </td>
                            <td style="padding: 14px 18px;">
                                @if($shift->status === 'open')
                                    <span style="padding: 4px 12px; border-radius: 20px; background: #DBEAFE; color: #1E88E5; font-size: 11px; font-weight: 800;">
                                        Buka
                                    </span>
                                @else
                                    <span style="padding: 4px 12px; border-radius: 20px; background: #F1F5F9; color: #64748B; font-size: 11px; font-weight: 800;">
                                        Tutup
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 14px 18px; font-weight: 800; color: #1976D2;">
                                Rp {{ number_format($shift->initial_cash, 0, ',', '.') }}
                            </td>
                            <td style="padding: 14px 18px; font-weight: 800; color: #059669;">
                                Rp {{ number_format($shiftSales, 0, ',', '.') }}
                            </td>
                            <td style="padding: 14px 18px; font-weight: 800; color: #0F172A;">
                                {{ $shift->closing_cash !== null ? 'Rp ' . number_format($shift->closing_cash, 0, ',', '.') : '-' }}
                            </td>
                            <td style="padding: 14px 18px; font-weight: 800;">
                                @if($shift->closing_cash === null)
                                    <span style="color: #94A3B8;">Belum Ditutup</span>
                                @elseif($selisih == 0)
                                    <span style="padding: 4px 10px; border-radius: 12px; background: #ECFDF5; color: #047857; font-size: 11px;">✓ Cocok (Rp 0)</span>
                                @elseif($selisih < 0)
                                    <span style="padding: 4px 10px; border-radius: 12px; background: #FFF1F2; color: #E11D48; font-size: 11px;">⚠️ Kurang Rp {{ number_format(abs($selisih), 0, ',', '.') }}</span>
                                @else
                                    <span style="padding: 4px 10px; border-radius: 12px; background: #EFF6FF; color: #1D4ED8; font-size: 11px;">+ Lebih Rp {{ number_format($selisih, 0, ',', '.') }}</span>
                                @endif
                            </td>
                            <td style="padding: 14px 18px; color: #64748B; font-weight: 600;">
                                {{ $shift->closed_at ? $shift->closed_at->format('d/m/Y H.i') . ' WIB' : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 36px; text-align: center; color: #94A3B8;">
                                Belum ada riwayat shift tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
