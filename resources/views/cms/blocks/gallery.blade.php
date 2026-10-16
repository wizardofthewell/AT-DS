@php
    $images = collect($data['images'] ?? [])->map(fn ($path) => $site->image($path))->filter()->values();
    $cols = match ((int) ($data['columns'] ?? 3)) {
        2 => 'sm:columns-2',
        4 => 'sm:columns-2 lg:columns-4',
        default => 'sm:columns-2 lg:columns-3',
    };
@endphp
@if($images->isNotEmpty())
    <x-section :data="$data">
        <div class="container-site" x-data="{ lightbox: null, images: @js($images) }" @keydown.escape.window="lightbox = null">
            <x-section-heading :heading="$data['heading'] ?? null" />
            <div class="gap-4 {{ $cols }} [&>*]:mb-4">
                @foreach($images as $image)
                    <button type="button" @click="lightbox = {{ $loop->index }}" class="block w-full overflow-hidden rounded-2xl">
                        <img src="{{ $image }}" alt="" loading="lazy" class="w-full transition duration-500 hover:scale-105">
                    </button>
                @endforeach
            </div>

            <div x-show="lightbox !== null" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-dark/90 p-4" @click.self="lightbox = null">
                <button type="button" @click="lightbox = null" class="absolute right-4 top-4 p-2 text-white" aria-label="Close"><x-heroicon-o-x-mark class="h-8 w-8" /></button>
                <button type="button" @click="lightbox = (lightbox - 1 + images.length) % images.length" class="absolute left-2 p-3 text-white" aria-label="Previous"><x-heroicon-m-chevron-left class="h-8 w-8" /></button>
                <img :src="images[lightbox]" alt="" class="max-h-[85vh] max-w-full rounded-xl">
                <button type="button" @click="lightbox = (lightbox + 1) % images.length" class="absolute right-2 p-3 text-white" aria-label="Next"><x-heroicon-m-chevron-right class="h-8 w-8" /></button>
            </div>
        </div>
    </x-section>
@endif
