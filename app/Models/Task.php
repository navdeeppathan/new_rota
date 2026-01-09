<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $table = 'tasks';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'location',
        'date',
        'start_time',
        'end_time',
        'duration_hours',
    ];

    public function images()
    {
        return $this->hasMany(TaskImage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }
    
     public function performances()
    {
        return $this->hasMany(TaskPerform::class);
    }
   
   public function task_data()
    {
        return $this->belongsTo(ScheduleTask::class);
    }
   
}

