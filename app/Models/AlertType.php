<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertType extends Model
{
    protected $fillable = ['type'];

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'alert_type_id');
    }
}

