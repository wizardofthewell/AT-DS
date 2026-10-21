<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Page extends Model
{
    use Auditable;

    /**
     * Slugs that must never be used by CMS pages because they collide
     * with application routes.
     */
    public const RESERVED_SLUGS = [
        'admin', 'login', 'logout', 'storage', 'blog', 'sitemap',
        'contact-submit', 'livewire', 'filament', 'up',
    ];

    protected $fillable = [
        'title',
        'slug',
        'blocks',
        'is_published',
        'show_in_nav',
        'nav_label',
        'nav_order',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'is_published' => 'boolean',
            'show_in_nav' => 'boolean',
            'nav_order' => 'integer',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Published pages that should appear in the site navigation.
     *
     * @return Collection<int, Page>
     */
    public static function navPages(): Collection
    {
        return static::published()
            ->where('show_in_nav', true)
            ->orderBy('nav_order')
            ->get();
    }

    public function getNavLabelAttribute($value): string
    {
        return $value ?: $this->title;
    }

    public function getUrlAttribute(): string
    {
        return $this->slug === 'home' ? url('/') : url('/'.$this->slug);
    }
}
