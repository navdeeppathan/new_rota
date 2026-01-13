<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskManagement extends Model
{
    protected $table = 'tasks_management';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'description',
        'section',
        'status',
        'progress',
        'progress_desc'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
