<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusPlaystore extends Model
{
    protected $table = 'statusPlaystore';

    protected $fillable = ['status'];

    public $timestamps = true; 
}
