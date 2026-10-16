@php
    $posts = \App\Models\Post::published()->latest('published_at')->limit((int) ($data['limit'] ?? 3) ?: 3)->get();
@endphp
@if($posts->isNotEmpty())
    <x-section :data="$data">
        <div class="container-site">
            <x-section-heading :heading="$data['heading'] ?? null" />
            <div class="grid gap-8 md:grid-cols-3">
                @foreach($posts as $post)
                    @include('blog.partials.card', ['post' => $post])
                @endforeach
            </div>
            <div class="mt-12 text-center">
                <a href="{{ route('blog.index') }}" class="btn-outline">View all posts</a>
            </div>
        </div>
    </x-section>
@endif
