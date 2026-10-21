<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SiteSetting extends Model
{
    use Auditable;

    protected $fillable = ['key', 'value'];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    /**
     * Retrieve a setting. Supports dot notation where the first segment is
     * the setting group key, e.g. get('contact.email'). Blank values fall
     * back to the default so empty admin fields never blank out the site.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::allCached();

        if (array_key_exists($key, $all)) {
            return blank($all[$key]) ? $default : $all[$key];
        }

        [$group, $rest] = array_pad(explode('.', $key, 2), 2, null);

        if ($rest !== null && array_key_exists($group, $all)) {
            $value = data_get($all[$group], $rest);

            return blank($value) ? $default : $value;
        }

        return $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);

        Cache::forget('site_settings');
    }

    /**
     * @return array<string, mixed>
     */
    public static function allCached(): array
    {
        return Cache::rememberForever('site_settings', function (): array {
            if (! Schema::hasTable('site_settings')) {
                return [];
            }

            return static::query()->pluck('value', 'key')->toArray();
        });
    }

    public function auditLabel(): string
    {
        return 'Site settings: '.str($this->key)->headline();
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('site_settings'));
        static::deleted(fn () => Cache::forget('site_settings'));
    }
}
