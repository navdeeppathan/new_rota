<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = ['folder_id','title','file_path','uploaded_by'];

    public function history() {
        return $this->hasMany(DocumentHistory::class);
    }
}

