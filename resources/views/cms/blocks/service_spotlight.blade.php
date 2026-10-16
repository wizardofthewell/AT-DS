@php
    $service = \App\Models\Service::published()->find($data['service_id'] ?? null);
    $imageRight = ($data['image_side'] ?? 'right') === 'right';
    $testimonial = ($data['show_testimonial'] ?? true) && $service
        ? $service->testimonials()->published()->ordered()->first()
        : null;
@endphp
@if($service)
    <x-section :data="$data">
        <div class="container-site">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
                <div class="{{ $imageRight ? 'lg:order-2' : '' }}">
                    @if($service->image_url)
                        <img src="{{ $service->image_url }}" alt="{{ $service->title }}" loading="lazy" class="aspect-[4/5] w-full rounded-[2rem] object-cover shadow-lg">
                    @else
                        <div class="flex aspect-[4/5] w-full items-center justify-center rounded-[2rem] bg-[radial-gradient(circle_at_30%_20%,var(--color-accent-soft),transparent_60%),radial-gradient(circle_at_80%_80%,color-mix(in_oklab,var(--color-primary)_22%,transparent),transparent_55%)] bg-white">
                            <span class="flex h-28 w-28 items-center justify-center rounded-full bg-white/80 text-primary shadow-sm">
                                @svg('heroicon-o-'.($service->icon ?: 'heart'), 'h-12 w-12')
                            </span>
                        </div>
                    @endif
                </div>
                <div>
                    @if($service->tagline)
                        <p class="eyebrow mb-3">{{ $service->tagline }}</p>
                    @endif
                    <h2 class="text-4xl sm:text-5xl">{{ $service->title }}</h2>
                    <x-ornament class="mt-5" />
                    @if($service->summary)
                        <p class="mt-6 text-lg leading-relaxed text-muted">{{ $service->summary }}</p>
                    @endif
                    @if(($data['show_features'] ?? true) && count($service->features ?? []))
                        <ul class="mt-6 grid gap-3 sm:grid-cols-2">
                            @foreach($service->features as $feature)
                                <li class="flex gap-3">
                                    <x-heroicon-o-check-circle class="mt-0.5 h-5 w-5 shrink-0 text-primary" />
                                    <span>{{ is_array($feature) ? ($feature['item'] ?? '') : $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <a href="{{ $service->url }}" class="btn-primary">{{ $service->button_label ?: 'Learn More' }}</a>
                        @if($service->price_label)
                            <span class="text-sm font-semibold text-muted">{{ $service->price_label }}</span>
                        @endif
                    </div>
                </div>
            </div>

            @if($testimonial)
                <div class="mt-16 border-t border-line pt-14">
                    <x-quote :testimonial="$testimonial" size="md" />
                </div>
            @endif
        </div>
    </x-section>
@endif
