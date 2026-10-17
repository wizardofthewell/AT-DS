<?php

namespace App\Support;

use App\Models\Page;
use App\Models\SiteSetting;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * View helper shared with every public view as $site.
 */
class Site
{
    public function get(string $key, mixed $default = null): mixed
    {
        return SiteSetting::get($key, $default);
    }

    public function name(): string
    {
        return $this->get('branding.site_name', config('app.name'));
    }

    /**
     * The (usually shorter) name shown in the header when no logo image is set.
     */
    public function logoText(): string
    {
        return $this->get('branding.logo_text', $this->name());
    }

    public function motto(): string
    {
        return (string) $this->get('branding.motto', '');
    }

    /**
     * Render rich editor content (HTML string or TipTap JSON) and fill placeholders.
     */
    public function html(mixed $content): string
    {
        if (blank($content)) {
            return '';
        }

        $html = is_array($content)
            ? RichContentRenderer::make($content)->toHtml()
            : (string) $content;

        return $this->placeholders($html);
    }

    public function placeholders(?string $text): string
    {
        return str_replace(
            ['{year}', '{name}', '{motto}'],
            [now()->year, $this->name(), $this->motto()],
            (string) $text,
        );
    }

    public function image(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Main menu items: the custom menu from settings, or published pages flagged
     * for the menu when no custom menu has been set up.
     *
     * @return array<int, array{label: string, url: string, external: bool, children: array}>
     */
    public function navItems(): array
    {
        $items = $this->get('navigation.items', []);

        if (filled($items)) {
            return collect($items)->map(fn (array $item) => [
                'label' => $item['label'] ?? '',
                'url' => $this->url($item['url'] ?? '#'),
                'external' => (bool) ($item['external'] ?? false),
                'children' => collect($item['children'] ?? [])->map(fn (array $child) => [
                    'label' => $child['label'] ?? '',
                    'url' => $this->url($child['url'] ?? '#'),
                ])->all(),
            ])->all();
        }

        return Page::navPages()
            ->map(fn (Page $page) => [
                'label' => $page->nav_label,
                'url' => $page->url,
                'external' => false,
                'children' => [],
            ])
            ->all();
    }

    public function url(?string $url): string
    {
        if (blank($url)) {
            return '#';
        }

        return Str::startsWith($url, ['http://', 'https://', 'mailto:', 'tel:', '#']) ? $url : url($url);
    }

    public function isActive(string $url): bool
    {
        $path = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');

        return $path === '' ? request()->is('/') : request()->is($path, $path.'/*');
    }

    /**
     * @return array<string, string> network => url
     */
    public function socialLinks(): array
    {
        return collect($this->get('social', []))
            ->filter(fn ($url) => filled($url))
            ->all();
    }

    public function tel(?string $phone): string
    {
        return 'tel:'.preg_replace('/[^0-9+]/', '', (string) $phone);
    }
}
