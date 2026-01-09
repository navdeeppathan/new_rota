<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'alert_type_id',
        'message',
        'show_to_user',
        'show_to_superadmin',
        'show_to_admin',
        'show_to_all',
        'user_id',
        'superadmin_id',
        'admin_id',
        'read_at',
    ];



    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function superadmin()
    {
        return $this->belongsTo(User::class, 'superadmin_id', 'id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id', 'id');
    }

    public function alertType()
    {
        return $this->belongsTo(AlertType::class, 'alert_type_id');
    }
}

