@extends('layouts.app')

@section('title', $service->title)
@section('meta_description', $service->summary ?? '')
@if($service->image_url)
    @section('share_image', $service->image_url)
@endif

@section('content')
    {{-- Header --}}
    <section class="relative isolate overflow-hidden bg-soft py-16 sm:py-24">
        <div class="absolute -right-32 -top-32 -z-10 h-96 w-96 rounded-full bg-accent/20 blur-3xl"></div>
        <div class="container-site grid items-center gap-12 lg:grid-cols-2">
            <div>
                <a href="{{ url('/services') }}" class="mb-6 inline-flex items-center gap-1 text-sm font-semibold text-primary hover:underline">
                    <x-heroicon-m-arrow-left class="h-4 w-4" /> All services
                </a>
                @if($service->tagline)
                    <p class="eyebrow mb-3">{{ $service->tagline }}</p>
                @endif
                <h1 class="text-5xl sm:text-6xl">{{ $service->title }}</h1>
                <x-ornament class="mt-6" />
                @if($service->summary)
                    <p class="mt-6 text-lg leading-relaxed text-muted">{{ $service->summary }}</p>
                @endif
                <div class="mt-8 flex flex-wrap items-center gap-5">
                    <a href="{{ $site->url($service->button_url ?: '/contact') }}" class="btn-primary">{{ $service->button_label ?: 'Get in Touch' }}</a>
                    @if($service->price_label)
                        <span class="font-heading text-2xl text-primary">{{ $service->price_label }}</span>
                    @endif
                </div>
            </div>
            <div>
                @if($service->image_url)
                    <img src="{{ $service->image_url }}" alt="{{ $service->title }}" class="aspect-[4/3] w-full rounded-[2rem] object-cover shadow-xl">
                @else
                    <div class="mx-auto flex aspect-square max-w-sm items-center justify-center rounded-full bg-white/60 text-primary">
                        @svg('heroicon-o-'.($service->icon ?: 'heart'), 'h-24 w-24 opacity-70')
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Body --}}
    <section class="py-16 sm:py-24">
        <div class="container-site grid gap-14 lg:grid-cols-3">
            <div class="prose prose-lg prose-site max-w-none lg:col-span-2">
                {!! $site->html($service->description) !!}
            </div>
            <aside class="space-y-8">
                @if(count($service->features ?? []))
                    <div class="card p-8">
                        <h2 class="text-2xl">What's included</h2>
                        <ul class="mt-5 space-y-3">
                            @foreach($service->features as $feature)
                                <li class="flex gap-3">
                                    <x-heroicon-o-check-circle class="mt-0.5 h-5 w-5 shrink-0 text-primary" />
                                    <span>{{ is_array($feature) ? ($feature['item'] ?? '') : $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="card bg-primary p-8 text-on-primary">
                    <h2 class="text-2xl">Have questions?</h2>
                    <p class="mt-3 opacity-90">I'd love to hear from you. A free, no-pressure consultation is the best place to start.</p>
                    <a href="{{ url('/contact') }}" class="btn-light mt-6 w-full">Contact Me</a>
                </div>
            </aside>
        </div>
    </section>

    @if($testimonials->isNotEmpty())
        <section class="tone-soft py-16 sm:py-24">
            <div class="container-site space-y-16">
                @foreach($testimonials->take(3) as $testimonial)
                    <x-quote :testimonial="$testimonial" size="md" />
                @endforeach
            </div>
        </section>
    @endif

    @if($others->isNotEmpty())
        <section class="py-16 sm:py-24">
            <div class="container-site">
                <x-section-heading heading="Other ways I can support you" />
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($others as $other)
                        <x-service-card :service="$other" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
