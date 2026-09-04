<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'educational_program_id','dec','photo'];

    protected $casts = [
        'dec' => 'array',
        'title' => 'array',

    ];

    protected $appends = [
        'lg_img',
        'md_img',
        'sm_img'
    ];
    public function educationalProgram()
    {
        return $this->belongsTo(EducationalProgram::class, 'educational_program_id');
    }

    public function getLgImgAttribute() {
        return $this->photo ? url('').'/upload/images/'.$this->photo : null;
    }

    public function getMdImgAttribute() {
        return $this->photo ? url('').'/upload/images/600/'.$this->photo : null;
    }

    public function getSmImgAttribute() {
        return $this->photo ? url('').'/upload/images/200/'.$this->photo : null;
    }
}
