@props(['buttons' => [], 'light' => false])
@if(count($buttons ?? []))
    <div {{ $attributes->merge(['class' => 'flex flex-wrap gap-3']) }}>
        @foreach($buttons as $button)
            @continue(blank($button['label'] ?? null))
            @php
                $style = $button['style'] ?? 'primary';
                $class = match (true) {
                    $style === 'outline' && $light => 'btn border border-white/80 text-white hover:bg-white hover:text-ink',
                    $style === 'outline' => 'btn-outline',
                    default => 'btn-primary',
                };
                $url = $site->url($button['url'] ?? '#');
            @endphp
            <a href="{{ $url }}" class="{{ $class }}" @if(str_starts_with($url, 'http') && ! str_starts_with($url, url('/'))) target="_blank" rel="noopener" @endif>
                {{ $button['label'] }}
            </a>
        @endforeach
    </div>
@endif
