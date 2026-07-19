<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    /**
     * Helper to easily record audit activity logs
     */
    public static function record(
        string $actionType,
        string $module,
        string $description,
        ?Model $subject = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        $user = auth()->user();
        $userId = $user?->id;
        $userName = $user?->name ?: 'Sistem Otomatis';
        $userRole = $user?->role_label ?: ($user?->outlet_id ? 'Kasir/Staf' : 'Master Admin');
        
        $outletId = $user?->outlet_id;
        $outletName = $user?->outlet?->name ?: 'Semua Toko (Konsolidasi)';

        if ($user && $user->outlet_id) {
            $activeShift = \App\Models\ShiftSession::where('user_id', $user->id)
                ->where('status', 'open')
                ->latest()
                ->first();

            if ($subject instanceof \App\Models\ShiftSession && !empty($subject->cashier_name)) {
                $userName = $subject->cashier_name;
                $userRole = 'Kasir di ' . $user->name;
            } elseif ($activeShift && !empty($activeShift->cashier_name)) {
                $userName = $activeShift->cashier_name;
                $userRole = 'Kasir di ' . $user->name;
            }
        }

        // Jika subject memiliki relasi outlet
        if ($subject && isset($subject->outlet_id) && $subject->outlet_id) {
            $outletId = $subject->outlet_id;
            $outletName = Outlet::find($outletId)?->name ?: $outletName;
        }

        return self::create([
            'user_id' => $userId,
            'user_name' => $userName,
            'user_role' => $userRole,
            'outlet_id' => $outletId,
            'outlet_name' => $outletName,
            'action_type' => $actionType,
            'module' => $module,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
        ]);
    }
}
