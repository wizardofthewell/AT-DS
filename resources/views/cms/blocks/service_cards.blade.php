@php
    $query = \App\Models\Service::published()->ordered();
    $services = match ($data['which'] ?? 'featured') {
        'all' => $query->get(),
        'chosen' => $query->whereIn('id', $data['service_ids'] ?? [])->get(),
        default => $query->where('is_featured', true)->get(),
    };
    $carousel = ($data['layout'] ?? 'grid') === 'carousel';
@endphp
@if($services->isNotEmpty())
    <x-section :data="$data">
        <div class="container-site">
            <x-section-heading :heading="$data['heading'] ?? null" :intro="$data['intro'] ?? null" />

            @if($carousel)
                <div x-data="carousel" class="relative">
                    <div x-ref="track" class="no-scrollbar -mx-5 flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth px-5 pb-6 sm:-mx-8 sm:px-8">
                        @foreach($services as $service)
                            <x-service-card :service="$service" :button-label="$data['button_label'] ?? null"
                                            class="w-[85%] shrink-0 snap-start sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)]" />
                        @endforeach
                    </div>
                    @if($services->count() > 3)
                        <div class="mt-4 flex justify-center gap-3">
                            <button type="button" @click="prev" :disabled="atStart" class="rounded-full border border-line bg-white p-3 transition hover:border-primary hover:text-primary disabled:opacity-40" aria-label="Previous">
                                <x-heroicon-m-arrow-left class="h-5 w-5" />
                            </button>
                            <button type="button" @click="next" :disabled="atEnd" class="rounded-full border border-line bg-white p-3 transition hover:border-primary hover:text-primary disabled:opacity-40" aria-label="Next">
                                <x-heroicon-m-arrow-right class="h-5 w-5" />
                            </button>
                        </div>
                    @endif
                </div>
            @else
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($services as $service)
                        <x-service-card :service="$service" :button-label="$data['button_label'] ?? null" />
                    @endforeach
                </div>
            @endif
        </div>
    </x-section>
@endif
