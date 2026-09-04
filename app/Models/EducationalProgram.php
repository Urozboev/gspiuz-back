<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationalProgram extends Model
{
    use HasFactory;
    protected $fillable = [
        'first_name',
        'second_name',
        'photo',
        'third_name',
        'parent_id',
        'slug',
        'code',
        'level',
        'order',
        'file',
        'lang',
        'icon',
        'map',
        'name',
        'education_years',
        'yt_link',
        'active',
        'date',
        'sirtqi_date',
        'form_education',
        'daytime',
        'part_time',
        'first_description',
        'second_description',
        'third_description',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'first_name' => 'array',
        'second_name' => 'array',
        'third_name' => 'array',
        'lang' => 'array',
        'name' => 'array',
        'map' => 'array',
        'first_description' => 'array',
        'second_description' => 'array',
        'third_description' => 'array',
        'form_education' => 'array',
    ];

    /**
     * Get the parent program.
     */
    public function children()
    {
        return $this->hasMany(EducationalProgram::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(EducationalProgram::class, 'parent_id');
    }

    public function employs()
    {
        return $this->belongsToMany(Employ::class, 'educational_program_employ', 'educational_program_id', 'employ_id');
    }
    public function activity()
    {
        return $this->hasOne(Activity::class, 'educational_program_id');
    }
    public function faq()
    {
        return $this->hasMany(EducationFaq::class, 'educational_program_id');
    }

    public function EntranceRequirement()
    {
        return $this->hasOne(EntranceRequirement::class, 'educational_program_id');
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
