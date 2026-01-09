<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonShift extends Model
{
    protected $fillable = [
        'user_id',
        'person_name',
        'role_group',
        'date',
        'shift_type',
        'shift_time',
        'shift_slot',
        'msg',
        'status',
        'overtime_minutes',
        'overtime_hours'	
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}

