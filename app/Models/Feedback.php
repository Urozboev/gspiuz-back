<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'position',
        'feedback',
        'img',
        'video',
        'youtube_link',
        'link'
    ];

    protected $casts = [
        'name' => 'array',
        'position' => 'array',
        'feedback' => 'array'
    ];

    protected $appends = [
        'lg_img',
        'md_img',
        'sm_img',
        'lg_logo',
        'md_logo',
        'sm_logo'
    ];

    public function getLgImgAttribute() {
        return $this->img ? url('').'/upload/feedbacks/'.$this->img : null;
    }

    public function getMdImgAttribute() {
        return $this->img ? url('').'/upload/feedbacks/600/'.$this->img : null;
    }

    public function getSmImgAttribute() {
        return $this->img ? url('').'/upload/feedbacks/200/'.$this->img : null;
    }

    public function getLgLogoAttribute() {
        return $this->img ? url('').'/upload/feedbacks/'.$this->img : null;
    }

    public function getMdLogoAttribute() {
        return $this->img ? url('').'/upload/feedbacks/600/'.$this->img : null;
    }

    public function getSmLogoAttribute() {
        return $this->img ? url('').'/upload/feedbacks/200/'.$this->img : null;
    }
}
