@php
    $image = $site->image($data['image'] ?? null);
    $dark = $image && ($data['overlay'] ?? 'dark') === 'dark';
    $center = ($data['align'] ?? 'center') === 'center';
    $height = match ($data['height'] ?? 'large') {
        'medium' => 'min-h-[50vh] py-20',
        'full' => 'min-h-[calc(100vh-7rem)] py-24',
        default => 'min-h-[70vh] py-24',
    };
    $isFirst = ($loop->index ?? 0) === 0;
@endphp
<section class="relative isolate flex items-center overflow-hidden {{ $height }} {{ $dark ? 'text-white' : '' }}">
    @if($image)
        <img src="{{ $image }}" alt="" class="absolute inset-0 -z-20 h-full w-full object-cover" @if($isFirst) fetchpriority="high" @else loading="lazy" @endif>
        <div class="absolute inset-0 -z-10 {{ $dark ? 'bg-gradient-to-b from-dark/50 via-dark/40 to-dark/60' : 'bg-cream/75' }}"></div>
    @else
        <div class="absolute inset-0 -z-10 bg-soft"></div>
        <div class="absolute -left-32 -top-32 -z-10 h-[28rem] w-[28rem] rounded-full bg-accent/25 blur-3xl"></div>
        <div class="absolute -bottom-40 -right-24 -z-10 h-[32rem] w-[32rem] rounded-full bg-primary/20 blur-3xl"></div>
        <svg class="absolute bottom-0 left-0 -z-10 h-24 w-full text-cream" viewBox="0 0 1440 96" preserveAspectRatio="none" fill="currentColor" aria-hidden="true">
            <path d="M0 64c160-40 320-56 480-40s320 64 480 64 320-48 480-72v80H0Z"/>
        </svg>
    @endif

    <div class="container-site">
        <div class="{{ $center ? 'mx-auto max-w-3xl text-center' : 'max-w-2xl' }}">
            @if(!empty($data['eyebrow']))
                <p class="eyebrow mb-5 {{ $dark ? '!text-white/85' : '' }}">{{ $site->placeholders($data['eyebrow']) }}</p>
            @endif
            <h1 class="text-5xl leading-[1.05] sm:text-6xl lg:text-7xl">{{ $site->placeholders($data['heading'] ?? '') }}</h1>
            @if(!empty($data['subheading']))
                <p class="mt-6 text-lg leading-relaxed sm:text-xl {{ $dark ? 'text-white/90' : 'text-muted' }}">{{ $site->placeholders($data['subheading']) }}</p>
            @endif
            <x-buttons :buttons="$data['buttons'] ?? []" :light="$dark" class="mt-10 {{ $center ? 'justify-center' : '' }}" />
        </div>
    </div>
</section>
