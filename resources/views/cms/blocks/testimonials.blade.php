@php
    $testimonials = \App\Models\Testimonial::published()
        ->when($data['featured_only'] ?? true, fn ($q) => $q->where('is_featured', true))
        ->ordered()
        ->limit((int) ($data['limit'] ?? 8) ?: 8)
        ->get();
@endphp
@if($testimonials->isNotEmpty())
    <x-section :data="$data">
        <div class="container-site">
            <x-section-heading :heading="$data['heading'] ?? null" class="!mb-8" />
            <div x-data="slider({{ $testimonials->count() }})" @mouseenter="stop" @mouseleave="play" @focusin="stop"
                 class="relative" role="region" aria-roledescription="carousel" aria-label="Testimonials">
                <div class="grid">
                    @foreach($testimonials as $testimonial)
                        <div x-show="current === {{ $loop->index }}"
                             x-transition:enter="transition duration-700 ease-out" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition duration-300 ease-in absolute inset-x-0" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             @if(! $loop->first) x-cloak @endif
                             class="col-start-1 row-start-1 px-10 sm:px-16" aria-roledescription="slide">
                            <x-quote :testimonial="$testimonial" size="md" />
                        </div>
                    @endforeach
                </div>

                @if($testimonials->count() > 1)
                    <button type="button" @click="prev" class="absolute left-0 top-1/2 -translate-y-1/2 rounded-full p-2 text-muted transition hover:bg-white hover:text-primary" aria-label="Previous testimonial">
                        <x-heroicon-m-chevron-left class="h-6 w-6" />
                    </button>
                    <button type="button" @click="next" class="absolute right-0 top-1/2 -translate-y-1/2 rounded-full p-2 text-muted transition hover:bg-white hover:text-primary" aria-label="Next testimonial">
                        <x-heroicon-m-chevron-right class="h-6 w-6" />
                    </button>
                    <div class="mt-10 flex justify-center gap-2">
                        @foreach($testimonials as $testimonial)
                            <button type="button" @click="go({{ $loop->index }})" class="h-2 rounded-full transition-all"
                                    :class="current === {{ $loop->index }} ? 'w-8 bg-primary' : 'w-2 bg-line hover:bg-muted'"
                                    aria-label="Show testimonial {{ $loop->iteration }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </x-section>
@endif
