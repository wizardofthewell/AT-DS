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
        'lavender' => [
            'label' => 'Lavender & Linen',
            'colors' => ['primary' => '#6e6785', 'accent' => '#c7bcd9', 'background' => '#f6f4f8', 'text' => '#2e2a38', 'dark' => '#3a3350'],
        ],
        'terracotta' => [
            'label' => 'Terracotta & Oat',
            'colors' => ['primary' => '#a2573f', 'accent' => '#d8c3a0', 'background' => '#f8f3ea', 'text' => '#3a2a22', 'dark' => '#4a2a20'],
        ],
        'coastal' => [
            'label' => 'Coastal Blue & Sand',
            'colors' => ['primary' => '#3f6b7a', 'accent' => '#e2cf9f', 'background' => '#f4f7f6', 'text' => '#23323a', 'dark' => '#1f3a44'],
        ],
        'nightfall_moss' => [
            'label' => 'Nightfall Moss & Brass (dark)',
            'colors' => ['primary' => '#9fb08a', 'accent' => '#c4a77d', 'background' => '#1e2420', 'text' => '#ece8df', 'dark' => '#151a16'],
        ],
        'midnight_sea' => [
            'label' => 'Midnight Sea & Seafoam (dark)',
            'colors' => ['primary' => '#86bcae', 'accent' => '#c9a57e', 'background' => '#141d22', 'text' => '#e6eef0', 'dark' => '#0c1417'],
        ],
        'espresso' => [
            'label' => 'Espresso & Rose Gold (dark)',
            'colors' => ['primary' => '#d9a58f', 'accent' => '#b08a5e', 'background' => '#231b17', 'text' => '#f3e9e1', 'dark' => '#17110e'],
        ],
        'sunlit' => [
            'label' => 'Sunlit Honey & Sage',
            'colors' => ['primary' => '#8a6d2f', 'accent' => '#e8d3a2', 'background' => '#fbf8f0', 'text' => '#33302a', 'dark' => '#4b5a45'],
        ],
        'blush' => [
            'label' => 'Blush & Eucalyptus',
            'colors' => ['primary' => '#7d8f84', 'accent' => '#e8c4bb', 'background' => '#fbf5f3', 'text' => '#3a2f2d', 'dark' => '#4e5c53'],
        ],
        'ink_and_ivory' => [
            'label' => 'Ink & Ivory (dark)',
            'colors' => ['primary' => '#d8d0bd', 'accent' => '#8fa7b8', 'background' => '#16191d', 'text' => '#ebe9e4', 'dark' => '#0d0f12'],
        ],
        'forest_night' => [
            'label' => 'Forest Night & Gold (dark)',
            'colors' => ['primary' => '#e0b96b', 'accent' => '#5f8a6e', 'background' => '#17221c', 'text' => '#e9efe6', 'dark' => '#0f1812'],
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

        $background = static::get('background');

        $vars = [
            '--site-primary' => $primary,
            '--site-on-primary' => static::readableOn($primary),
            '--site-accent' => static::get('accent'),
            '--site-background' => $background,
            '--site-surface' => static::isDark($background) ? "color-mix(in oklab, $background 88%, white)" : '#ffffff',
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
     * Whether the given background colour needs light text (a dark theme).
     */
    public static function isDark(string $hex): bool
    {
        return static::readableOn($hex) === '#ffffff';
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
