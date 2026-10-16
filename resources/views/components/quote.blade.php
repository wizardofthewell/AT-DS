{{-- A single testimonial, large and centred. --}}
@props(['testimonial', 'size' => 'lg'])
<figure {{ $attributes->merge(['class' => 'mx-auto max-w-3xl text-center']) }}>
    <svg class="mx-auto mb-6 h-10 w-10 text-accent" viewBox="0 0 40 40" fill="currentColor" aria-hidden="true">
        <path d="M17 10c-6 2-10 7-10 14v6h10V20h-5c0-4 2-6.5 6-7.5L17 10Zm16 0c-6 2-10 7-10 14v6h10V20h-5c0-4 2-6.5 6-7.5L33 10Z" opacity=".8"/>
    </svg>
    <blockquote class="font-heading {{ $size === 'lg' ? 'text-2xl sm:text-3xl' : 'text-xl sm:text-2xl' }} italic leading-snug">
        “{{ $testimonial->quote }}”
    </blockquote>
    <figcaption class="mt-8 flex items-center justify-center gap-4">
        @if($testimonial->photo_url)
            <img src="{{ $testimonial->photo_url }}" alt="" class="h-12 w-12 rounded-full object-cover">
        @endif
        <span class="text-left">
            <span class="block font-semibold">{{ $testimonial->author }}</span>
            @if($testimonial->context)
                <span class="block text-sm text-muted">{{ $testimonial->context }}</span>
            @endif
        </span>
    </figcaption>
</figure>
