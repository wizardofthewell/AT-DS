@extends('layouts.app')

@section('title', $page->slug === 'home' ? '' : $page->title)
@if($page->meta_title)
    @section('full_title', $page->meta_title)
@endif
@if($page->meta_description)
    @section('meta_description', $page->meta_description)
@endif

@section('content')
    @forelse($page->blocks ?? [] as $block)
        @includeIf('cms.blocks.'.($block['type'] ?? ''), ['data' => $block['data'] ?? []])
    @empty
        <x-section>
            <div class="container-site text-center">
                <h1 class="text-5xl">{{ $page->title }}</h1>
                <p class="mt-4 text-muted">This page has no sections yet. Add some in the admin under Website → Pages.</p>
            </div>
        </x-section>
    @endforelse
@endsection
