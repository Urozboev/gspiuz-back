<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TranslationGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'sub_text'
    ];

    public function translations()
    {
        return $this->hasMany(Translation::class);
    }
}
