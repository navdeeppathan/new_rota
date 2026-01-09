<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftDefinition extends Model
{
    protected $table = 'shift_definitions';

    protected $fillable = [
        'leave_code',
        'leave_name',
        'shift_slot',
        'day_start',
        'day_end',
        'night_start',
        'night_end',
        'break_start',
        'break_end',
        'break_duration',
        'total_working_time',
        'total_break_time',
    ];

    
}
