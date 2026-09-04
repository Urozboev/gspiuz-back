<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormImage extends Model
{
    use HasFactory;
    protected $fillable = [
        'img',
        'form_id'
    ];

    public function form()
    {
        return $this->belongsTo(FormMenu::class);
    }
    public function formImages()
    {
        return $this->hasMany(FormImage::class, 'form_id');
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
