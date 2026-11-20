<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Perbaikan dari Factories ke Relations

class Product extends Model
{
    public const LOW_STOCK_THRESHOLD = 5;

    protected $fillable = [
        'name',
        'category_id',
        'product_code', // Ditambahkan
        'brand',        // Ditambahkan
        'price',
        'stock',
        'description', 
        'image',        // Ditambahkan
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}