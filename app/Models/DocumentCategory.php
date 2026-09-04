<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'img',
        'parent_id'
    ];

    protected $casts = [
        'title' => 'array'
    ];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function parent()
    {
        return $this->hasOne(self::class, 'id', 'parent_id');
    }
    
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
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
