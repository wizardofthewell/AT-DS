<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Lets a super admin sign in as another user to see exactly what they see,
 * then return to their own account.
 */
class Impersonation
{
    public const SESSION_KEY = 'impersonator_id';

    /**
     * True while switching accounts, so the switch is not logged as a normal sign-in.
     */
    public static bool $isSwitching = false;

    public static function canImpersonate(?User $actor, User $target): bool
    {
        return $actor?->isSuperAdmin()
            && ! static::isActive()
            && ! $actor->is($target)
            && ! $target->isSuperAdmin();
    }

    public static function isActive(): bool
    {
        return session()->has(static::SESSION_KEY);
    }

    public static function impersonator(): ?User
    {
        return static::isActive() ? User::find(session(static::SESSION_KEY)) : null;
    }

    public static function start(User $target): void
    {
        $impersonator = Auth::user();

        Audit::record('impersonation_started', $target, description: "Logged in as {$target->name}");

        session()->put(static::SESSION_KEY, $impersonator->getKey());
        static::switchTo($target);
    }

    public static function stop(): ?User
    {
        $impersonator = static::impersonator();
        $target = Auth::user();

        if (! $impersonator) {
            session()->forget(static::SESSION_KEY);

            return null;
        }

        session()->forget(static::SESSION_KEY);
        static::switchTo($impersonator);

        Audit::record('impersonation_stopped', $target, description: "Returned from {$target?->name}'s account");

        return $impersonator;
    }

    protected static function switchTo(User $user): void
    {
        static::$isSwitching = true;

        try {
            Auth::guard('web')->login($user);
        } finally {
            static::$isSwitching = false;
        }

        // Let AuthenticateSession store the new user's password hash on the next request.
        session()->forget('password_hash_'.Auth::getDefaultDriver());
    }
}
