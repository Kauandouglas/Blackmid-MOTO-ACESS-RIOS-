<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'observations',
        'price',
        'image',
        'sizes',
        'colors',
        'gallery',
        'featured',
        'highlight_best_sellers',
        'highlight_launches',
        'stock',
        'track_stock',
        'active',
        'weight_grams',
        'gross_weight_grams',
        'width_cm',
        'height_cm',
        'depth_cm',
        'bling_id',
        'bling_code',
        'bling_last_sync_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sizes' => 'array',
            'colors' => 'array',
            'gallery' => 'array',
            'featured' => 'boolean',
            'highlight_best_sellers' => 'boolean',
            'highlight_launches' => 'boolean',
            'track_stock' => 'boolean',
            'active' => 'boolean',
            'width_cm' => 'decimal:2',
            'height_cm' => 'decimal:2',
            'depth_cm' => 'decimal:2',
            'bling_last_sync_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
