<?php

namespace App\Models\Concerns;

use App\Support\Audit;
use Illuminate\Database\Eloquent\Model;

/**
 * Records create, update and delete events for the model in the activity log.
 */
trait Auditable
{
    /**
     * Attributes never worth logging.
     *
     * @var list<string>
     */
    protected static array $auditIgnored = ['created_at', 'updated_at', 'remember_token'];

    /**
     * Attributes whose values are hidden in the log; only the fact they changed is shown.
     *
     * @var list<string>
     */
    protected static array $auditMasked = ['password'];

    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            $values = static::auditValues($model, array_keys($model->getAttributes()), fn (string $key): mixed => $model->getAttribute($key));

            Audit::record('created', $model, newValues: array_filter($values, fn (mixed $value): bool => $value !== null && $value !== ''));
        });

        static::updated(function (Model $model): void {
            $fields = array_keys($model->getChanges());
            $old = static::auditValues($model, $fields, fn (string $key): mixed => $model->getOriginal($key));
            $new = static::auditValues($model, $fields, fn (string $key): mixed => $model->getAttribute($key), maskedAs: '(changed)');

            $changedKeys = collect(array_keys($old + $new))
                ->filter(fn (string $key): bool => ($old[$key] ?? null) !== ($new[$key] ?? null));

            if ($changedKeys->isEmpty()) {
                return;
            }

            Audit::record(
                'updated',
                $model,
                oldValues: $changedKeys->mapWithKeys(fn (string $key): array => [$key => $old[$key] ?? null])->all(),
                newValues: $changedKeys->mapWithKeys(fn (string $key): array => [$key => $new[$key] ?? null])->all(),
            );
        });

        static::deleted(function (Model $model): void {
            $values = static::auditValues($model, array_keys($model->getAttributes()), fn (string $key): mixed => $model->getAttribute($key));

            Audit::record('deleted', $model, oldValues: array_filter($values, fn (mixed $value): bool => $value !== null && $value !== ''));
        });
    }

    /**
     * @param  list<string>  $fields
     * @param  callable(string): mixed  $resolve
     * @return array<string, mixed>
     */
    protected static function auditValues(Model $model, array $fields, callable $resolve, string $maskedAs = '(hidden)'): array
    {
        $values = [];

        foreach ($fields as $field) {
            if (in_array($field, static::$auditIgnored, true)) {
                continue;
            }

            if (in_array($field, static::$auditMasked, true)) {
                $values[$field] = $maskedAs;

                continue;
            }

            $values += Audit::flatten($field, $resolve($field));
        }

        return $values;
    }
}
