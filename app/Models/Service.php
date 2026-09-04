<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'desc',
        'views_count',
        'img',
        'slug'
    ];

    protected $casts = [
        'title' => 'array',
        'desc' => 'array',
    ];

    public function parent()
    {
        return $this->hasOne(self::class, 'id', 'parent_id');
    }
    
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public function works()
    {
        return $this->belongsToMany(Work::class);
    }

    protected $appends = [
        'lg_img',
        'md_img',
        'sm_img'
    ];

    public function getLgImgAttribute() {
        return $this->img ? url('').'/upload/images/'.$this->img : null;
    }

    public function getMdImgAttribute() {
        return $this->img ? url('').'/upload/images/600/'.$this->img : null;
    }

    public function getSmImgAttribute() {
        return $this->img ? url('').'/upload/images/200/'.$this->img : null;
    }
}
