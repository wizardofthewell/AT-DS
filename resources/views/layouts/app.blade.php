@php
    $siteName = $site->name();
    $pageTitle = trim($__env->yieldContent('title'));
    $fullTitle = $pageTitle ? $pageTitle.' | '.$siteName : $siteName.($site->get('branding.tagline') ? ' | '.$site->get('branding.tagline') : '');
    $description = trim($__env->yieldContent('meta_description')) ?: $site->get('seo.description', '');
    $shareImage = trim($__env->yieldContent('share_image')) ?: $site->image($site->get('seo.share_image'));
    $favicon = $site->image($site->get('branding.favicon'));
    $gaId = $site->get('seo.google_analytics_id');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trim($__env->yieldContent('full_title')) ?: $fullTitle }}</title>
    @if($description)
        <meta name="description" content="{{ $description }}">
    @endif
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle ?: $siteName }}">
    @if($description)
        <meta property="og:description" content="{{ $description }}">
    @endif
    @if($shareImage)
        <meta property="og:image" content="{{ $shareImage }}">
    @endif

    @if($favicon)
        <link rel="icon" href="{{ $favicon }}">
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="{{ \App\Support\Theme::fontsUrl() }}">
    <style>:root { {!! \App\Support\Theme::cssVariables() !!} }</style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if($gaId)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
        <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config',@json($gaId));</script>
    @endif
    @stack('head')
</head>
<body class="flex min-h-screen flex-col">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-3 focus:rounded focus:bg-white focus:px-4 focus:py-2">Skip to content</a>

    @include('layouts.header')

    <main id="main" class="flex-1">
        @yield('content')
    </main>

    @include('layouts.footer')
</body>
</html>
