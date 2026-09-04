<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lang extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'code',
        'is_main',
        'icon'
    ];

    protected $appends = [
        'icon_url',
    ];

    public function getIconUrlAttribute() {
        return $this->icon ? url('').'/upload/images/'.$this->icon : null;
    }
}
