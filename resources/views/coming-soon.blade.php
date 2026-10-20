@php
    $social = $site->socialLinks();
    $email = $site->get('contact.email');
    $backgroundImage = $site->image($site->get('launch.image', $site->get('seo.share_image')));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.head')
    <meta name="robots" content="noindex">
</head>
<body class="tone-dark relative isolate flex min-h-screen flex-col items-center justify-center overflow-hidden px-4 py-16 text-center">
    @if($backgroundImage)
        <img src="{{ $backgroundImage }}" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-25">
    @endif

    <main class="w-full max-w-2xl">
        <x-logo light class="mx-auto" />

        <h1 class="mt-12 text-4xl text-white sm:text-6xl">{{ $site->placeholders($site->get('launch.heading', 'Great things are coming soon')) }}</h1>
        <x-ornament class="mx-auto mt-6" />

        @if($site->get('launch.message'))
            <p class="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-white/80">{{ $site->placeholders($site->get('launch.message')) }}</p>
        @elseif($site->motto())
            <p class="mt-6 font-heading text-2xl italic text-accent">{{ $site->motto() }}</p>
        @endif

        @if(!empty($email) && $email != 'hello@example.com')
            <p class="mt-10 text-white/80">
                In the meantime, say hello at
                <a href="mailto:{{ $email }}" class="text-white underline underline-offset-4 hover:opacity-80">{{ $email }}</a>
            </p>
        @endif
        @if(!empty($social) && count($social) > 0)
            <x-social-links :links="$social" class="mt-6 justify-center gap-5" size="w-5 h-5" />
        @endif
    </main>
</body>
</html>
