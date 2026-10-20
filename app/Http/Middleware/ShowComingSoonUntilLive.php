<?php

namespace App\Http\Middleware;

use App\Support\Site;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shows the coming soon page to visitors until the site is switched live in
 * Site Settings. Signed-in admins still see the full site so they can preview it.
 */
class ShowComingSoonUntilLive
{
    public function __construct(protected Site $site) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (static::shouldShowComingSoon($request, $this->site)) {
            return static::comingSoonResponse();
        }

        return $next($request);
    }

    public static function shouldShowComingSoon(Request $request, Site $site): bool
    {
        return ! $site->isLive() && ! $request->user();
    }

    public static function comingSoonResponse(): Response
    {
        return response()->view('coming-soon');
    }
}
