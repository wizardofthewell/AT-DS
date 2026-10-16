@php
    $image = $site->image($data['image'] ?? null);
    $imageRight = ($data['image_side'] ?? 'left') === 'right';
@endphp
<x-section :data="$data">
    <div class="container-site grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
        <div class="{{ $imageRight ? 'lg:order-2' : '' }}">
            <div class="relative mx-auto max-w-md lg:max-w-none">
                <div class="absolute -inset-4 -z-0 translate-x-4 translate-y-4 rounded-[2rem] bg-accent/25 {{ $imageRight ? '-translate-x-4' : '' }}" aria-hidden="true"></div>
                @if($image)
                    <img src="{{ $image }}" alt="{{ $data['image_alt'] ?? '' }}" loading="lazy" class="relative aspect-[4/5] w-full rounded-[2rem] object-cover shadow-lg">
                @else
                    <div class="relative flex aspect-[4/5] w-full items-center justify-center rounded-[2rem] bg-soft text-primary/50">
                        <x-heroicon-o-photo class="h-16 w-16" />
                    </div>
                @endif
            </div>
        </div>
        <div>
            @if(!empty($data['eyebrow']))
                <p class="eyebrow mb-3">{{ $site->placeholders($data['eyebrow']) }}</p>
            @endif
            @if(!empty($data['heading']))
                <h2 class="text-4xl sm:text-5xl">{{ $site->placeholders($data['heading']) }}</h2>
                <x-ornament class="mt-5" />
            @endif
            <div class="prose prose-lg prose-site mt-6 max-w-none">
                {!! $site->html($data['content'] ?? '') !!}
            </div>
            <x-buttons :buttons="$data['buttons'] ?? []" class="mt-8" />
        </div>
    </div>
</x-section>
