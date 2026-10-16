@php
    $packages = $data['packages'] ?? [];
    $cols = match (count($packages)) {
        1 => 'max-w-md mx-auto',
        2 => 'md:grid-cols-2 max-w-4xl mx-auto',
        default => 'md:grid-cols-2 lg:grid-cols-3',
    };
@endphp
<x-section :data="$data">
    <div class="container-site">
        <x-section-heading :heading="$data['heading'] ?? null" :intro="$data['intro'] ?? null" />
        <div class="grid items-stretch gap-8 {{ $cols }}">
            @foreach($packages as $package)
                @php $highlight = (bool) ($package['highlighted'] ?? false); @endphp
                <div class="card relative flex flex-col p-8 sm:p-10 {{ $highlight ? 'ring-2 ring-primary lg:-my-4 lg:py-14' : '' }}">
                    @if($highlight)
                        <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 rounded-full bg-primary px-4 py-1 text-xs font-semibold uppercase tracking-widest text-on-primary">Most popular</span>
                    @endif
                    <h3 class="text-3xl">{{ $package['name'] ?? '' }}</h3>
                    @if(!empty($package['price']))
                        <p class="mt-4 font-heading text-5xl text-primary">{{ $package['price'] }}</p>
                    @endif
                    @if(!empty($package['price_note']))
                        <p class="mt-1 text-sm text-muted">{{ $package['price_note'] }}</p>
                    @endif
                    @if(!empty($package['description']))
                        <p class="mt-5 leading-relaxed text-muted">{{ $package['description'] }}</p>
                    @endif
                    @if(count($package['features'] ?? []))
                        <ul class="mt-6 space-y-3 border-t border-line pt-6 text-[15px]">
                            @foreach($package['features'] as $feature)
                                <li class="flex gap-3">
                                    <x-heroicon-o-check class="mt-0.5 h-5 w-5 shrink-0 text-primary" />
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @if(!empty($package['button_label']))
                        <div class="mt-auto pt-8">
                            <a href="{{ $site->url($package['button_url'] ?? '/contact') }}" class="{{ $highlight ? 'btn-primary' : 'btn-outline' }} w-full">{{ $package['button_label'] }}</a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        @if(!empty($data['footnote']))
            <p class="mx-auto mt-10 max-w-2xl text-center text-sm text-muted">{{ $data['footnote'] }}</p>
        @endif
    </div>
</x-section>
