<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'file',
        'link',
        'date',
        'document_category_id',
        'slug',
    ];

    protected $casts = [
        'title' => 'array'
    ];

    public function document_category()
    {
        return $this->belongsTo(DocumentCategory::class);
    }
}
