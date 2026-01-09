<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleTask extends Model
{
    use HasFactory;

    protected $table = 'schedule_task'; // explicitly define the table

    protected $fillable = [
        'user_id',
        'task_id',
        'schedule_id',
         'scheduled_date',
    ];

    // Relationships (optional if you have models for users, tasks, and schedules)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function schedule()
    {
        return $this->belongsTo(TaskPerform::class);
    }
    public function images()
{
    return $this->hasMany(TaskImage::class, 'task_id', 'id'); 
    // 'task_id' in task_images refers to this model's id (taskperformance_id)
}

   
}
