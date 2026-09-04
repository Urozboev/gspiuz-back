<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = ['name', 'structure_type_id',  'parent_id', 'active', 'code','slug'];

    protected $casts = [
        'name' => 'array',
    ];
    public function structureType()
    {
        return $this->belongsTo(StructureType::class, 'structure_type_id');
    }

    public function type()
    {
        return $this->belongsTo(EmployType::class, 'type_id');
    }

    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Department::class, 'parent_id');
    }
}
