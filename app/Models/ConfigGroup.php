<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigGroup extends Model
{
    use HasFactory;

    protected $table = 'config_groups';

    protected $fillable = [
        'title',
        'is_active',
        'position'
    ];

    public function configs()
    {
        return $this->hasMany(Config::class);
    }
}
