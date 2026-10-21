<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory,SoftDeletes;

    /** Tuzilma turlari — `structure_types` jadvalidagi raqamlar. */
    public const TYPE_LEADERSHIP = 1;
    public const TYPE_UNIT = 2;      // Boshqarma va boʻlimlar
    public const TYPE_FACULTY = 3;
    public const TYPE_KAFEDRA = 4;
    public const TYPE_CENTER = 5;

    protected $fillable = [
        'name', 'structure_type_id', 'parent_id', 'active', 'code', 'slug',
        'description', 'image', 'phone', 'email', 'address', 'work_time', 'order',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'address' => 'array',
        'work_time' => 'array',
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

    /** Boʻlimdagi tayinlovlar (xodim + lavozim). */
    public function assignments()
    {
        return $this->hasMany(EmployMeta::class, 'department_id');
    }

    /**
     * Saytga chiqadigan koʻrinish: tanlangan tilda, boʻsh tarjima
     * asosiy tilga tushadi.
     */
    public function toPublicArray(string $locale, string $fallback = 'uz'): array
    {
        $pick = fn (?array $value) => $value
            ? (($value[$locale] ?? '') !== '' ? $value[$locale] : ($value[$fallback] ?? null))
            : null;

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $pick($this->name),
            'description' => $pick($this->description),
            'image' => $this->image ? url('/upload/images/' . ltrim($this->image, '/')) : null,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $pick($this->address),
            'work_time' => $pick($this->work_time),
            'type' => $this->structure_type_id,
            'parent' => $this->parent ? ['slug' => $this->parent->slug, 'name' => $pick($this->parent->name)] : null,
        ];
    }
}
