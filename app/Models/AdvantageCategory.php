<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvantageCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'title'
    ];

    protected $casts = [
        'title' => 'array'
    ];

    public function advantages()
    {
        return $this->hasMany(Advantage::class);
    }
}
