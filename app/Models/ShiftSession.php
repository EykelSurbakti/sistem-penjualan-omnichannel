<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftSession extends Model
{
    protected $table = 'shift_user';

    protected $guarded = [];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'initial_cash' => 'float',
        'closing_cash' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
}
