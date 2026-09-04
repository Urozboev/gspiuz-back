<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployStaff extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = ['name','order', 'active'];
    protected $casts = ['name'=>'array'];

}
