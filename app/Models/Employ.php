<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employ extends Model
{
    use HasFactory, softDeletes;
    protected $fillable = [
        'first_name',
        'last_name',
        'surname',
        'email',
        'address',
        'status',
        'birthday',
        'gender',
        'special',
        'photo',
        'phone',
        'dec',
        'slug',
        'position',
        'work_time',
        'leader',
        'professor',
    ];
    protected $casts = [
        'first_name' => 'array',
        'last_name' => 'array',
        'surname' => 'array',
        'position' => 'array',
        'address' => 'array',
        'work_time' => 'array',
        'dec' => 'array',

    ];

    /**
     * Xodimning bitta tayinlovi.
     *
     * Ko'p joyda "xodimning lavozimi" bitta deb qaraladi, shuning uchun
     * bu bog'lanish saqlanib qolgan. Bo'lim sahifasida u aynan o'sha
     * bo'limga cheklab yuklanadi.
     */
    public function employMeta()
    {
        return $this->hasOne(EmployMeta::class, 'employ_id');
    }

    /**
     * Xodimning barcha tayinlovlari.
     *
     * Bir xodim bir nechta bo'lim yoki markazda bo'lishi mumkin —
     * masalan prorektor, ayni paytda kengash a'zosi.
     */
    public function employMetas()
    {
        return $this->hasMany(EmployMeta::class, 'employ_id');
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
    public function educational_programs()
    {
        return $this->belongsToMany(EducationalProgram::class, 'educational_program_employ', 'educational_program_id', 'employ_id');
    }


}
