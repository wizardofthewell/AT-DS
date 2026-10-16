{{-- Wraps a page-builder block, applying its Appearance options (background tone + anchor). --}}
@props(['data' => [], 'padding' => 'py-16 sm:py-24'])
@php
    $tone = $data['background'] ?? 'default';
    $anchor = $data['anchor'] ?? null;
@endphp
<section @if($anchor) id="{{ $anchor }}" @endif {{ $attributes->merge(['class' => "relative $padding ".($tone !== 'default' ? "tone-$tone" : '')]) }}>
    {{ $slot }}
</section>
