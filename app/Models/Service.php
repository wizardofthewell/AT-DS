<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use Auditable;

    protected $fillable = [
        'title',
        'slug',
        'tagline',
        'summary',
        'description',
        'image',
        'icon',
        'price_label',
        'features',
        'button_label',
        'button_url',
        'is_featured',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    public function getUrlAttribute(): string
    {
        return route('services.show', $this);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/'.ltrim($this->image, '/')) : null;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
