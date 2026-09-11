<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'google_books_id',
        'category_id',
        'title',
        'slug',
        'description',
        'isbn',
        'publisher',
        'published_at',
        'pages',
        'cover_url',
        'price',
        'stock',
        'active',
        'featured',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'date',
            'price' => 'integer',
            'stock' => 'integer',
            'active' => 'boolean',
            'featured' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'product_author');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    public function scopeFeatured(Builder $query): void
    {
        $query->where('featured', true);
    }

    public function isPurchasable(): bool
    {
        return $this->active && $this->stock > 0;
    }
}
