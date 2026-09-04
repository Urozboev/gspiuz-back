<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'desc',
        'views_count',
        'price',
        'discount_price',
        'info',
        'brand_id',
        'meta_keywords',
        'meta_desc',
        'slug'
    ];

    protected $casts = [
        'title' => 'array',
        'desc' => 'array',
        'info' => 'array',
        'meta_keywords' => 'array',
        'meta_desc' => 'array'
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function productsCategories()
    {
        return $this->belongsToMany(ProductsCategory::class, 'products_category_product', 'product_id', 'products_category_id');
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }
}
