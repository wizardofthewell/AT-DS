<x-section :data="$data">
    <div class="container-site">
        <x-section-heading :heading="$data['heading'] ?? null" :intro="$data['intro'] ?? null" />
        @php
            $stepCols = match (count($data['steps'] ?? [])) {
                1 => '',
                2 => 'md:grid-cols-2',
                3 => 'md:grid-cols-3',
                default => 'md:grid-cols-4',
            };
        @endphp
        <ol class="relative mx-auto grid max-w-5xl gap-10 {{ $stepCols }}">
            <div class="absolute left-0 right-0 top-7 hidden h-px bg-line md:block" aria-hidden="true"></div>
            @foreach($data['steps'] ?? [] as $step)
                <li class="relative text-center">
                    <span class="relative mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-primary font-heading text-2xl text-on-primary shadow-md ring-8 ring-[var(--color-cream)]">
                        {{ $loop->iteration }}
                    </span>
                    <h3 class="text-2xl">{{ $step['title'] ?? '' }}</h3>
                    @if(!empty($step['text']))
                        <p class="mt-3 leading-relaxed text-muted">{{ $step['text'] }}</p>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</x-section>
