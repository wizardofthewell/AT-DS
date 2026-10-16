@props(['service', 'buttonLabel' => 'Learn More'])
<article {{ $attributes->merge(['class' => 'card group flex h-full flex-col overflow-hidden']) }}>
    <a href="{{ $service->url }}" class="relative block aspect-[4/3] overflow-hidden bg-soft" tabindex="-1" aria-hidden="true">
        @if($service->image_url)
            <img src="{{ $service->image_url }}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
        @else
            <div class="flex h-full w-full items-center justify-center bg-[radial-gradient(circle_at_30%_20%,var(--color-accent-soft),transparent_60%),radial-gradient(circle_at_80%_80%,color-mix(in_oklab,var(--color-primary)_18%,transparent),transparent_55%)]">
                <span class="flex h-20 w-20 items-center justify-center rounded-full bg-white/70 text-primary shadow-sm">
                    @svg('heroicon-o-'.($service->icon ?: 'heart'), 'h-9 w-9')
                </span>
            </div>
        @endif
    </a>
    <div class="flex flex-1 flex-col p-7 text-center">
        <h3 class="text-2xl"><a href="{{ $service->url }}" class="hover:text-primary">{{ $service->title }}</a></h3>
        @if($service->tagline)
            <p class="mt-2 text-sm font-medium text-primary">{{ $service->tagline }}</p>
        @endif
        @if($service->summary)
            <p class="mt-3 text-[15px] leading-relaxed text-muted">{{ \Illuminate\Support\Str::limit($service->summary, 150) }}</p>
        @endif
        <div class="mt-auto pt-6">
            <a href="{{ $service->url }}" class="btn-outline !px-6 !py-2.5">{{ $buttonLabel ?: 'Learn More' }}</a>
        </div>
    </div>
</article>
