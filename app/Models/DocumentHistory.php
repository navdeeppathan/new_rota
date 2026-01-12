<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentHistory extends Model
{
    public $timestamps = false;
    protected $fillable = ['document_id','action','file_path','created_at'];
}

