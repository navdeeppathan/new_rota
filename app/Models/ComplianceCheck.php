<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceCheck extends Model
{
    protected $fillable = [
        'c_tasks_id',
        'is_checked',
        'percent',
        'frequency',
        'checked_at',
        'checked_by'
    ];

    public function task()
    {
        return $this->belongsTo(ComplianceTask::class, 'c_tasks_id');
    }
}

