<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AnesthesiaType extends Model
{
    use HasFactory;

    public const ACTIVE_CACHE_KEY = 'anesthesia_types.active';

    protected $table = 'anesthesia_types';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::ACTIVE_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::ACTIVE_CACHE_KEY));
    }

    /**
     * Retrieve ordered active anaesthesia types from cache.
     */
    public static function cachedActive(): Collection
    {
        return Cache::remember(
            self::ACTIVE_CACHE_KEY,
            now()->addHours(12),
            fn () => static::active()->get()
        );
    }

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Scope for active anesthesia types.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name');
    }
}

