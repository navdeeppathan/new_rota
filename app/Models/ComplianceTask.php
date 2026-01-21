<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ComplianceTask extends Model
{
    protected $fillable = ['name', 'is_active'];

    public function check()
    {
        return $this->hasOne(ComplianceCheck::class, 'c_tasks_id');
    }
}
