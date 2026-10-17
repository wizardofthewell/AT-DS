@props(['light' => false])
@php
    $logo = $site->image($site->get('branding.logo'));
    $height = (int) $site->get('branding.logo_height', 64);
@endphp
<a href="{{ url('/') }}" {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center']) }} aria-label="{{ $site->name() }} — home">
    @if($logo)
        <img src="{{ $logo }}" alt="{{ $site->name() }}" style="height: {{ $height }}px" class="w-auto {{ $light ? 'brightness-0 invert' : '' }}">
    @else
        <span class="flex flex-col leading-none">
            <span class="font-heading text-[1.75rem] tracking-wide {{ $light ? 'text-white' : 'text-ink' }}">{{ $site->logoText() }}</span>
            @if($site->get('branding.tagline'))
                <span class="mt-1 text-[10px] font-semibold uppercase tracking-[0.3em] {{ $light ? 'text-accent' : 'text-primary' }}">{{ $site->get('branding.tagline') }}</span>
            @endif
        </span>
    @endif
</a>
