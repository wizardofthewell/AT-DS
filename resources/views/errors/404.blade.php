@extends('layouts.app')

@section('title', 'Page not found')

@section('content')
    <x-section>
        <div class="container-site max-w-2xl py-10 text-center">
            <p class="eyebrow">404</p>
            <h1 class="mt-4 text-5xl">This page has wandered off</h1>
            <x-ornament class="mx-auto mt-5" />
            <p class="mt-6 text-lg text-muted">The page you're looking for doesn't exist or may have moved.</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ url('/') }}" class="btn-primary">Back to home</a>
                <a href="{{ url('/contact') }}" class="btn-outline">Contact me</a>
            </div>
        </div>
    </x-section>
@endsection
