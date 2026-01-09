<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'user_id', 'shift_date', 'start_time', 'end_time',
        'shift_type_id', 'is_active'
    ];

    public function shiftType()
    {
        return $this->belongsTo(ShiftType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

