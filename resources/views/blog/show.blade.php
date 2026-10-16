@extends('layouts.app')

@section('title', $post->title)
@section('meta_description', $post->meta_description ?: ($post->excerpt ?? ''))
@if($post->cover_image_url)
    @section('share_image', $post->cover_image_url)
@endif

@section('content')
    <article>
        <header class="bg-soft py-16 text-center sm:py-20">
            <div class="container-site max-w-3xl">
                <a href="{{ route('blog.index') }}" class="mb-6 inline-flex items-center gap-1 text-sm font-semibold text-primary hover:underline">
                    <x-heroicon-m-arrow-left class="h-4 w-4" /> All posts
                </a>
                <time datetime="{{ $post->published_at->toDateString() }}" class="eyebrow block">{{ $post->published_at->format('F j, Y') }}</time>
                <h1 class="mt-4 text-4xl sm:text-6xl">{{ $post->title }}</h1>
                <x-ornament class="mx-auto mt-6" />
            </div>
        </header>

        @if($post->cover_image_url)
            <div class="container-site -mb-4 mt-12 max-w-4xl">
                <img src="{{ $post->cover_image_url }}" alt="" class="aspect-[16/9] w-full rounded-[2rem] object-cover shadow-lg">
            </div>
        @endif

        <div class="container-site max-w-3xl py-16">
            <div class="prose prose-lg prose-site max-w-none">
                {!! $site->html($post->body) !!}
            </div>
        </div>
    </article>

    @if($more->isNotEmpty())
        <section class="tone-soft py-16 sm:py-20">
            <div class="container-site">
                <x-section-heading heading="Keep reading" />
                <div class="grid gap-8 md:grid-cols-3">
                    @foreach($more as $other)
                        @include('blog.partials.card', ['post' => $other])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
