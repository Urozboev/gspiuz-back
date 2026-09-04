<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'position',
        'img',
        'phone_number',
        'instagram_link',
        'telegram_link',
        'linkedin_link',
        'facebook_link',
        'work_time',
        'view_count',
        'gender',
        'dec',
        'slug',
        'yers'
    ];

    protected $casts = [
        'name' => 'array',
        'position' => 'array',
        'dec' => 'array',
        'work_time' => 'array'
    ];

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
