@php
    $cols = match ((int) ($data['columns'] ?? 3)) {
        2 => 'sm:grid-cols-2',
        4 => 'sm:grid-cols-2 lg:grid-cols-4',
        default => 'sm:grid-cols-2 lg:grid-cols-3',
    };
@endphp
<x-section :data="$data">
    <div class="container-site">
        <x-section-heading :heading="$data['heading'] ?? null" :intro="$data['intro'] ?? null" />
        <div class="grid gap-10 {{ $cols }}">
            @foreach($data['items'] ?? [] as $item)
                <div class="text-center">
                    <span class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-accent-soft text-primary ring-8 ring-accent-soft/40">
                        @svg('heroicon-o-'.($item['icon'] ?? 'heart'), 'h-7 w-7')
                    </span>
                    <h3 class="text-2xl">{{ $item['title'] ?? '' }}</h3>
                    @if(!empty($item['text']))
                        <p class="mx-auto mt-3 max-w-xs leading-relaxed text-muted">{{ $item['text'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</x-section>
