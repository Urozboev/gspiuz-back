<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advantage extends Model
{
    use HasFactory;

    protected $fillable = [
        'advantage_category_id',
        'title',
        'desc',
        'img'
    ];

    protected $casts = [
        'title' => 'array',
        'desc' => 'array',
    ];

    public function advantage_category()
    {
        return $this->belongsTo(AdvantageCategory::class);
    }

    protected $appends = [
        'lg_img',
        'md_img',
        'sm_img'
    ];

    public function getLgImgAttribute()
    {
        return $this->img ? url('') . '/upload/images/' . $this->img : null;
    }

    public function getMdImgAttribute()
    {
        return $this->img ? url('') . '/upload/images/600/' . $this->img : null;
    }

    public function getSmImgAttribute()
    {
        return $this->img ? url('') . '/upload/images/200/' . $this->img : null;
    }
}
