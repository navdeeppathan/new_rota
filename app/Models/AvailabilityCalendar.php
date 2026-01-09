<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;


class AvailabilityCalendar extends Model
{
    use HasFactory;

    protected $table = 'availability_calendar';

    protected $fillable = [
        'user_id',
        'date',
        'month',
        'start_time',
        'end_time',
        'is_day_off',
        'days',
        'shift'
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'is_day_off' => 'boolean',
    ];

    public $timestamps = true;

    
   public function user()
    {
        return $this->belongsTo(User::class, 'user_id'); 
    }


}
