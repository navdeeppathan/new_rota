<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskPerform extends Model
{
    protected $table = 'task_perform';

    protected $fillable = [
        'task_id',
        'user_id',
        'title',
        'date',
        'start_time',
        'end_time',
        'duration_hours',
        'icon_path',
    ];

    // Task relationship (optional, if linked to task)
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // User relationship (performer)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function images()
    {
    return $this->hasMany(TaskImage::class, 'task_id', 'id');
    }
    
    public function image()
    {
        return $this->hasMany(TaskImage::class, 'task_id'); // task_id here means schedule_task.id
    }


}

