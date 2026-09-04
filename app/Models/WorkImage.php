<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_id',
        'img'
    ];

    public function work()
    {
        return $this->belongsTo(Work::class);
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
