<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    protected $fillable = [
      'key',
      'val',
      'translation_group_id'
    ];

    protected $casts = [
      'val' => 'array',
    ];

    public function translationGroup()
    {
      return $this->belongsTo(TranslationGroup::class);
    }
}
