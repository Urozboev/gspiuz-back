<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntranceRequirement extends Model
{
    use HasFactory;

    protected $fillable = ['name','dec','photo','educational_program_id'];

    protected $casts = [
        'name'=>'array',
        'dec'=>'array'
    ];
    public function educationalProgram()
    {
        return $this->belongsTo(EducationalProgram::class, 'educational_program_id');
    }

    public function skills()
    {
        return $this->belongsToMany(ERskill::class, 'entrance_requirement_e_rskill', 'entrance_requirement_id', 'skill_id');
    }


    protected $appends = [
        'lg_img',
        'md_img',
        'sm_img'
    ];

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
