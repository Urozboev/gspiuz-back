<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployMeta extends Model
{
    use HasFactory, softDeletes;
    protected $fillable = [
        'employ_id',
        'department_id',
        'position_id',
        'employ_staff_id',
        'employ_form_id',
        'contrakt_date',
        'contrakt_number',
        'employ_type_id',
        'active',
        'slug',
        'order'
    ];



    public function employ()
    {
        return $this->belongsTo(Employ::class, 'employ_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }
    public function employ_form()
    {
        return $this->belongsTo(EmployFor::class, 'employ_form_id');
    }
    public function employ_staff()
    {
        return $this->belongsTo(EmployStaff::class, 'employ_staff_id');
    }
    public function employ_type()
    {
        return $this->belongsTo(EmployType::class, 'employ_type_id');
    }


}
