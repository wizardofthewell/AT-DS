<article class="card group flex flex-col overflow-hidden">
    <a href="{{ $post->url }}" class="block aspect-[16/10] overflow-hidden bg-soft">
        @if($post->cover_image_url)
            <img src="{{ $post->cover_image_url }}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
        @else
            <div class="flex h-full items-center justify-center bg-accent-soft text-primary/60"><x-heroicon-o-book-open class="h-12 w-12" /></div>
        @endif
    </a>
    <div class="flex flex-1 flex-col p-7">
        <time datetime="{{ $post->published_at->toDateString() }}" class="text-xs font-semibold uppercase tracking-widest text-primary">{{ $post->published_at->format('F j, Y') }}</time>
        <h3 class="mt-3 text-2xl"><a href="{{ $post->url }}" class="hover:text-primary">{{ $post->title }}</a></h3>
        @if($post->excerpt)
            <p class="mt-3 leading-relaxed text-muted">{{ \Illuminate\Support\Str::limit($post->excerpt, 140) }}</p>
        @endif
        <a href="{{ $post->url }}" class="mt-auto inline-flex items-center gap-1 pt-5 text-sm font-semibold text-primary">Read more <x-heroicon-m-arrow-right class="h-4 w-4" /></a>
    </div>
</article>
