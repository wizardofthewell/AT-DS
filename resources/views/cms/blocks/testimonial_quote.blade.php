@php
    $testimonial = filled($data['testimonial_id'] ?? null)
        ? \App\Models\Testimonial::published()->find($data['testimonial_id'])
        : \App\Models\Testimonial::published()->where('is_featured', true)->inRandomOrder()->first();
@endphp
@if($testimonial)
    <x-section :data="$data">
        <div class="container-site">
            <x-quote :testimonial="$testimonial" />
        </div>
    </x-section>
@endif
