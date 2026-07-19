<div style="font-family: inherit; font-size: 13.5px; color: #334155;">
    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px; margin-bottom: 16px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <div>
                <span style="font-size: 11px; color: #64748B; display: block;">Pelaku Aktivitas</span>
                <strong style="color: #0F172A; font-size: 14px;">{{ $record->user_name }}</strong>
                <span style="font-size: 11px; background: #E2E8F0; padding: 1px 6px; border-radius: 4px; color: #334155; margin-left: 4px;">{{ $record->user_role }}</span>
            </div>
            <div>
                <span style="font-size: 11px; color: #64748B; display: block;">Cabang Toko / Lokasi</span>
                <strong style="color: #0F172A; font-size: 14px;">{{ $record->outlet_name }}</strong>
            </div>
            <div>
                <span style="font-size: 11px; color: #64748B; display: block;">Modul / Kategori</span>
                <strong style="color: #1E40AF;">{{ $record->module }}</strong>
            </div>
            <div>
                <span style="font-size: 11px; color: #64748B; display: block;">Waktu Eksekusi</span>
                <strong style="color: #0F172A;">{{ $record->created_at?->format('d M Y, H:i:s') }} WIB</strong>
            </div>
        </div>
        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #CBD5E1;">
            <span style="font-size: 11px; color: #64748B; display: block;">Alamat IP Pengguna</span>
            <code style="background: #E2E8F0; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{ $record->ip_address ?: 'Sistem Internal' }}</code>
        </div>
    </div>

    <div style="margin-bottom: 16px;">
        <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">
            Keterangan Singkat
        </span>
        <div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 12px 14px; border-radius: 6px; font-weight: 700; color: #1E3A8A;">
            {{ $record->description }}
        </div>
    </div>

    @if($record->old_values || $record->new_values)
        <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 8px;">
            Rincian Perubahan Data (Audit Trail)
        </span>
        <div style="overflow-x: auto; border: 1px solid #E2E8F0; border-radius: 8px;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 12.5px;">
                <thead>
                    <tr style="background: #F1F5F9; border-bottom: 1px solid #E2E8F0; color: #475569;">
                        <th style="padding: 10px 12px; width: 30%;">Atribut / Kolom</th>
                        <th style="padding: 10px 12px; width: 35%; color: #DC2626;">Sebelum Diubah (Old Value)</th>
                        <th style="padding: 10px 12px; width: 35%; color: #16A34A;">Sesudah Diubah (New Value)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $keys = array_unique(array_merge(
                            is_array($record->old_values) ? array_keys($record->old_values) : [],
                            is_array($record->new_values) ? array_keys($record->new_values) : []
                        ));
                    @endphp
                    @foreach($keys as $key)
                        @if(in_array($key, ['updated_at', 'created_at', 'id'])) @continue @endif
                        <tr style="border-bottom: 1px solid #F1F5F9;">
                            <td style="padding: 10px 12px; font-weight: 700; color: #334155; background: #FAFAFA;">
                                {{ ucwords(str_replace('_', ' ', $key)) }}
                            </td>
                            <td style="padding: 10px 12px; background: #FEF2F2; color: #991B1B; font-family: monospace;">
                                @php
                                    $oldVal = $record->old_values[$key] ?? null;
                                    if (is_array($oldVal)) $oldVal = json_encode($oldVal);
                                    elseif (is_bool($oldVal)) $oldVal = $oldVal ? 'Ya (True)' : 'Tidak (False)';
                                @endphp
                                {{ $oldVal !== null && $oldVal !== '' ? $oldVal : '-' }}
                            </td>
                            <td style="padding: 10px 12px; background: #F0FDF4; color: #166534; font-family: monospace; font-weight: bold;">
                                @php
                                    $newVal = $record->new_values[$key] ?? null;
                                    if (is_array($newVal)) $newVal = json_encode($newVal);
                                    elseif (is_bool($newVal)) $newVal = $newVal ? 'Ya (True)' : 'Tidak (False)';
                                @endphp
                                {{ $newVal !== null && $newVal !== '' ? $newVal : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="background: #F8FAFC; border: 1px dashed #CBD5E1; padding: 14px; text-align: center; border-radius: 8px; color: #64748B;">
            Aktivitas ini merupakan aksi sistem atau pencatatan langsung tanpa mutasi kolom spesifik.
        </div>
    @endif
</div>
