@extends('layouts.app')

@section('content')
    <x-section>
        <div class="container-site max-w-2xl text-center">
            <h1 class="text-5xl">Welcome!</h1>
            <x-ornament class="mx-auto mt-5" />
            <p class="mt-6 text-lg text-muted">There's no homepage yet. Sign in to the admin and create a page with the address <strong>home</strong>, or run the starter content seeder.</p>
            <a href="{{ url('/admin') }}" class="btn-primary mt-8">Go to admin</a>
        </div>
    </x-section>
@endsection
