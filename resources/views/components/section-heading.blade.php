@props(['eyebrow' => null, 'heading' => null, 'intro' => null, 'align' => 'center'])
@if($eyebrow || $heading || $intro)
    <div {{ $attributes->merge(['class' => 'mb-12 '.($align === 'center' ? 'mx-auto max-w-2xl text-center' : 'max-w-2xl')]) }}>
        @if($eyebrow)
            <p class="eyebrow mb-3">{{ $site->placeholders($eyebrow) }}</p>
        @endif
        @if($heading)
            <h2 class="text-4xl sm:text-5xl">{{ $site->placeholders($heading) }}</h2>
        @endif
        <x-ornament class="{{ $align === 'center' ? 'mx-auto' : '' }} mt-5" />
        @if($intro)
            <p class="mt-5 text-lg leading-relaxed text-muted">{{ $site->placeholders($intro) }}</p>
        @endif
    </div>
@endif
