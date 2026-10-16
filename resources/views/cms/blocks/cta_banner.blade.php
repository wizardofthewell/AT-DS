@php
    $image = $site->image($data['image'] ?? null);
@endphp
<section class="px-5 py-16 sm:px-8 sm:py-24">
    <div class="relative isolate mx-auto max-w-6xl overflow-hidden rounded-[2rem] bg-primary px-6 py-16 text-center text-on-primary sm:px-16 sm:py-20">
        @if($image)
            <img src="{{ $image }}" alt="" loading="lazy" class="absolute inset-0 -z-20 h-full w-full object-cover">
            <div class="absolute inset-0 -z-10 bg-dark/60"></div>
        @else
            <div class="absolute -right-20 -top-20 -z-10 h-72 w-72 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-24 -left-16 -z-10 h-80 w-80 rounded-full bg-accent/30"></div>
        @endif
        <h2 class="mx-auto max-w-2xl text-4xl sm:text-5xl {{ $image ? 'text-white' : '' }}">{{ $site->placeholders($data['heading'] ?? '') }}</h2>
        @if(!empty($data['text']))
            <p class="mx-auto mt-5 max-w-xl text-lg opacity-90 {{ $image ? 'text-white' : '' }}">{{ $site->placeholders($data['text']) }}</p>
        @endif
        @if(count($data['buttons'] ?? []))
            <div class="mt-10 flex flex-wrap justify-center gap-3">
                @foreach($data['buttons'] as $button)
                    @continue(blank($button['label'] ?? null))
                    <a href="{{ $site->url($button['url'] ?? '#') }}"
                       class="{{ ($button['style'] ?? 'primary') === 'outline' ? 'btn border border-current hover:bg-white hover:text-ink' : 'btn-light' }}">
                        {{ $button['label'] }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
