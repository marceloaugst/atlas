<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasFactory;

    private const ACTIVE_CACHE_KEY = 'categories:active';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Active categories rarely change, but every catalog page load reads
     * them (the filter chips), so cache the list instead of re-querying.
     */
    public static function activeCached(): \Illuminate\Support\Collection
    {
        return Cache::remember(
            self::ACTIVE_CACHE_KEY,
            1800,
            fn () => self::where('active', true)->orderBy('name')->get(),
        );
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::ACTIVE_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::ACTIVE_CACHE_KEY));
    }
}
