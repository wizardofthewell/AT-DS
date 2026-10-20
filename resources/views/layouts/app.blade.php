<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.head')
</head>
<body class="flex min-h-screen flex-col">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-3 focus:rounded focus:bg-white focus:px-4 focus:py-2">Skip to content</a>

    @if(! $site->isLive() && auth()->check())
        <div class="bg-ink px-4 py-2 text-center text-xs text-white">
            Preview — visitors currently see the coming soon page.
            <a href="{{ \App\Filament\Pages\ManageSiteSettings::getUrl() }}" class="underline hover:opacity-80">Go live</a>
        </div>
    @endif

    @include('layouts.header')

    <main id="main" class="flex-1">
        @yield('content')
    </main>

    @include('layouts.footer')
</body>
</html>
