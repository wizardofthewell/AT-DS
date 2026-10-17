@extends('layouts.app')

@section('title', 'Blog')

@section('content')
    <section class="bg-soft py-16 text-center sm:py-20">
        <div class="container-site">
            <x-section-heading eyebrow="Journal" heading="Blog & Resources" intro="Thoughts, tips and gentle guidance for the fourth trimester and life with a new baby." class="!mb-0" />
        </div>
    </section>

    <section class="py-16 sm:py-24">
        <div class="container-site">
            @if($posts->isEmpty())
                <p class="text-center text-muted">No posts yet — check back soon.</p>
            @else
                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($posts as $post)
                        @include('blog.partials.card', ['post' => $post])
                    @endforeach
                </div>
                <div class="mt-12">{{ $posts->links() }}</div>
            @endif
        </div>
    </section>
@endsection
