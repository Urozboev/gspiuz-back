<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // SoftDeletes xususiyatini import qilish


class FileMenu extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['name', 'body', 'type', 'size', 'm_type', 'text', 'form_menu_id'];

    // Form bilan bog‘lanish
    public function form()
    {
        return $this->belongsTo(Form::class, 'form_menu_id');
    }
}
