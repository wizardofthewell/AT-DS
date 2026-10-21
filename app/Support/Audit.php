<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use BackedEnum;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

/**
 * Writes entries to the admin activity log.
 */
class Audit
{
    /**
     * Longest value stored per field, so huge text blocks do not bloat the log.
     */
    public const MAX_VALUE_LENGTH = 2000;

    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public static function record(
        string $event,
        ?Model $subject = null,
        array $oldValues = [],
        array $newValues = [],
        ?string $description = null,
        ?User $user = null,
    ): ?AuditLog {
        try {
            $request = app()->bound('request') ? request() : null;
            $user ??= auth()->user();

            return AuditLog::create([
                'user_id' => $user?->getKey(),
                'impersonator_id' => $request?->hasSession() ? $request->session()->get(Impersonation::SESSION_KEY) : null,
                'event' => $event,
                'auditable_type' => $subject?->getMorphClass(),
                'auditable_id' => $subject?->getKey(),
                'auditable_label' => $subject ? Str::limit(static::labelFor($subject), 250) : null,
                'description' => $description ? Str::limit($description, 250) : null,
                'old_values' => $oldValues ?: null,
                'new_values' => $newValues ?: null,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent() ? Str::limit($request->userAgent(), 250) : null,
            ]);
        } catch (Throwable $e) {
            // Never let logging break the action being logged.
            report($e);

            return null;
        }
    }

    /**
     * A short name for the record, e.g. a page title or FAQ question.
     */
    public static function labelFor(Model $model): string
    {
        if (method_exists($model, 'auditLabel')) {
            return $model->auditLabel();
        }

        foreach (['title', 'name', 'question', 'author', 'key', 'email'] as $attribute) {
            if (filled($model->getAttribute($attribute))) {
                return (string) $model->getAttribute($attribute);
            }
        }

        return '#'.$model->getKey();
    }

    /**
     * Turns a value into something readable and storable. Arrays are flattened
     * into dot keys so only the parts that changed need to be shown.
     *
     * @return array<string, mixed>
     */
    public static function flatten(string $field, mixed $value): array
    {
        if (is_array($value)) {
            if ($value === []) {
                return [$field => null];
            }

            return collect(Arr::dot($value))
                ->mapWithKeys(fn (mixed $item, string|int $key): array => ["{$field}.{$key}" => static::normalize($item)])
                ->all();
        }

        return [$field => static::normalize($value)];
    }

    public static function normalize(mixed $value): mixed
    {
        return match (true) {
            $value === [] => null,
            $value instanceof DateTimeInterface => $value->format('Y-m-d H:i:s'),
            $value instanceof BackedEnum => $value->value,
            is_object($value) => json_encode($value),
            is_string($value) => Str::limit($value, static::MAX_VALUE_LENGTH),
            default => $value,
        };
    }
}
