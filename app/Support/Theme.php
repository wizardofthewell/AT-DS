<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Str;

class Theme
{
    public const DEFAULTS = [
        'primary' => '#66705a',
        'accent' => '#b39b7c',
        'background' => '#f5f2ec',
        'text' => '#322f2a',
        'dark' => '#2c312a',
        'heading_font' => 'Cormorant Garamond',
        'body_font' => 'Nunito Sans',
        'button_shape' => 'pill',
    ];

    public const PRESETS = [
        'harbourstone' => [
            'label' => 'Harbourstone (moss, driftwood & linen)',
            'colors' => ['primary' => '#66705a', 'accent' => '#b39b7c', 'background' => '#f5f2ec', 'text' => '#322f2a', 'dark' => '#2c312a'],
        ],
        'seaglass' => [
            'label' => 'Sea Glass & Stone',
            'colors' => ['primary' => '#5b716c', 'accent' => '#bfae93', 'background' => '#f3f2ee', 'text' => '#2f3332', 'dark' => '#28322f'],
        ],
        'cedar' => [
            'label' => 'Cedar & Clay',
            'colors' => ['primary' => '#7a6350', 'accent' => '#b98e6c', 'background' => '#f7f2ec', 'text' => '#352d27', 'dark' => '#33291f'],
        ],
        'sage' => [
            'label' => 'Sage & Blush',
            'colors' => ['primary' => '#6f7f63', 'accent' => '#c9937e', 'background' => '#faf6f1', 'text' => '#3b3430', 'dark' => '#2f3a2c'],
        ],
        'neutral' => [
            'label' => 'Warm Neutral',
            'colors' => ['primary' => '#6b625c', 'accent' => '#cdbfb4', 'background' => '#f7f4f1', 'text' => '#2f2b28', 'dark' => '#2f2b28'],
        ],
    ];

    public const HEADING_FONTS = [
        'Cormorant Garamond', 'Playfair Display', 'Lora', 'Libre Baskerville',
        'DM Serif Display', 'Marcellus', 'Fraunces', 'Josefin Sans',
    ];

    public const BODY_FONTS = [
        'Nunito Sans', 'Lato', 'Work Sans', 'Source Sans 3', 'Karla', 'Jost', 'Raleway', 'Open Sans',
    ];

    public static function get(string $key): string
    {
        $value = SiteSetting::get('theme.'.$key, static::DEFAULTS[$key]);

        return is_string($value) && $value !== '' ? $value : static::DEFAULTS[$key];
    }

    /**
     * CSS custom properties consumed by resources/css/app.css.
     */
    public static function cssVariables(): string
    {
        $primary = static::get('primary');
        $radius = match (static::get('button_shape')) {
            'square' => '0.25rem',
            'soft' => '0.75rem',
            default => '999px',
        };

        $vars = [
            '--site-primary' => $primary,
            '--site-on-primary' => static::readableOn($primary),
            '--site-accent' => static::get('accent'),
            '--site-background' => static::get('background'),
            '--site-text' => static::get('text'),
            '--site-dark' => static::get('dark'),
            '--site-heading-font' => '"'.static::get('heading_font').'", Georgia, serif',
            '--site-body-font' => '"'.static::get('body_font').'", ui-sans-serif, system-ui, sans-serif',
            '--site-button-radius' => $radius,
        ];

        return collect($vars)->map(fn ($v, $k) => "$k: $v;")->implode(' ');
    }

    public static function fontsUrl(): string
    {
        $families = collect([static::get('heading_font'), static::get('body_font')])
            ->unique()
            ->map(fn (string $font) => Str::slug($font).':400,400i,500,600,700')
            ->implode('|');

        return 'https://fonts.bunny.net/css?family='.$families.'&display=swap';
    }

    /**
     * Pick black or white text for legibility on the given background colour.
     */
    public static function readableOn(string $hex): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = preg_replace('/(.)/', '$1$1', $hex);
        }

        if (! preg_match('/^[0-9a-f]{6}$/i', $hex)) {
            return '#ffffff';
        }

        $channel = function (string $c): float {
            $c = hexdec($c) / 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        };

        $luminance = 0.2126 * $channel(substr($hex, 0, 2))
            + 0.7152 * $channel(substr($hex, 2, 2))
            + 0.0722 * $channel(substr($hex, 4, 2));

        return $luminance > 0.4 ? '#1f1b18' : '#ffffff';
    }
}
