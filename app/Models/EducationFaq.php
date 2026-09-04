<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationFaq extends Model
{
    use HasFactory;
    protected $table = 'education_faqs';
    protected $fillable = ['educational_program_id','action','skill_id','question', 'answer','parent_id'];

    protected $casts = [
        'question' => 'array',
        'answer' => 'array',
    ];

    public function children()
    {
        return $this->hasMany(EducationFaq::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(EducationFaq::class, 'parent_id');
    }
    public function educationalProgram()
    {
        return $this->belongsTo(EducationalProgram::class, 'educational_program_id');
    }
    public function skill()
    {
        return $this->belongsTo(ERskill::class, 'skill_id');
    }


}
