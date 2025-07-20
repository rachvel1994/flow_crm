<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Tags\HasTags;

class Product extends Model
{
    use HasTags;

    protected $fillable = [
        'name',
        'sku',
        'model',
        'category_id',
        'price',
        'images',
        'description',
        'b2b_price',
        'sale_price',
        'color_id',
        'is_active',
        'team_id',
    ];

    protected $casts = [
        'images' => 'array'
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
