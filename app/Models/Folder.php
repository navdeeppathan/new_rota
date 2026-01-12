<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $fillable = ['name','parent_id','year' ,'status'];

    public function children() {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function documents() {
        return $this->hasMany(Document::class);
    }

}

