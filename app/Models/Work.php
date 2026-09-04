<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Work extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'desc',
        'youtube_link',
        'main_img',
        'video',
        'views_count'
    ];

    protected $casts = [
        'title' => 'array',
        'desc' => 'array'
    ];

    public function services() {
        return $this->belongsToMany(Service::class);
    }

    public function workImages()
    {
        return $this->hasMany(WorkImage::class);
    }

    protected $appends = [
        'lg_main_img',
        'md_main_img',
        'sm_main_img'
    ];

    public function getLgMainImgAttribute() {
        return $this->main_img ? url('').'/upload/images/'.$this->main_img : null;
    }

    public function getMdMainImgAttribute() {
        return $this->main_img ? url('').'/upload/images/600/'.$this->main_img : null;
    }

    public function getSmMainImgAttribute() {
        return $this->main_img ? url('').'/upload/images/200/'.$this->main_img : null;
    }
}
