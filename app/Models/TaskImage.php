<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskImage extends Model
{
    use HasFactory;

    protected $table = 'task_images';

    protected $fillable = [
        'task_id',
        'user_id',
        'file_path',
    ];

    /**
     * Get the task associated with the image.
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the user who uploaded the image (if needed).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

