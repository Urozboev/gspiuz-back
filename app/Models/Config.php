<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    use HasFactory;
    
    protected $table = 'config';

    protected $fillable = [
        'group_id',
        'title',
        'route',
        'is_active',
        'position'
    ];

    public function configGroup()
    {
        return $this->belongsTo(ConfigGroup::class);
    }
}
