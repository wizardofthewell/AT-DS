<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

/**
 * One entry in the admin activity log: a content change, sign-in, backup, etc.
 */
class AuditLog extends Model
{
    use MassPrunable;

    public const UPDATED_AT = null;

    /**
     * How many days entries are kept before the nightly prune removes them.
     */
    public const RETENTION_DAYS = 365;

    public const EVENTS = [
        'created' => 'Created',
        'updated' => 'Updated',
        'deleted' => 'Deleted',
        'login' => 'Signed in',
        'logout' => 'Signed out',
        'login_failed' => 'Failed sign-in',
        'password_reset' => 'Password reset',
        'impersonation_started' => 'Logged in as user',
        'impersonation_stopped' => 'Returned to own account',
        'backup_created' => 'Backup created',
        'backup_deleted' => 'Backup deleted',
        'backup_failed' => 'Backup failed',
        'system' => 'System action',
    ];

    protected $fillable = [
        'user_id',
        'impersonator_id',
        'event',
        'auditable_type',
        'auditable_id',
        'auditable_label',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function impersonator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'impersonator_id');
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subDays(static::RETENTION_DAYS));
    }

    public function eventLabel(): string
    {
        return static::EVENTS[$this->event] ?? Str::headline($this->event);
    }

    /**
     * Human-friendly name of the kind of record, e.g. "Testimonial".
     */
    public function subjectTypeLabel(): ?string
    {
        return $this->auditable_type ? Str::headline(class_basename($this->auditable_type)) : null;
    }

    /**
     * Pairs old and new values per field for the "changes" view.
     *
     * @return array<string, array{old: mixed, new: mixed}>
     */
    public function fieldChanges(): array
    {
        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];

        return collect(array_keys($old + $new))
            ->mapWithKeys(fn (string $field): array => [$field => ['old' => $old[$field] ?? null, 'new' => $new[$field] ?? null]])
            ->all();
    }
}
