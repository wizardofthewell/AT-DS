@php $style = $data['style'] ?? 'ornament'; @endphp
<x-section :data="$data" padding="{{ $style === 'space' ? 'py-10' : 'py-6' }}">
    <div class="container-site">
        @if($style === 'ornament')
            <x-ornament class="mx-auto !h-6 !w-40" />
        @elseif($style === 'line')
            <hr class="border-line">
        @endif
    </div>
</x-section>
