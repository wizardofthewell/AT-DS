@props(['links' => [], 'size' => 'w-4 h-4'])
@if(count($links))
    <div {{ $attributes->merge(['class' => 'flex items-center']) }}>
        @foreach($links as $network => $url)
            <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($network) }}" class="transition hover:opacity-70">
                @svg('bi-'.$network, $size)
            </a>
        @endforeach
    </div>
@endif
