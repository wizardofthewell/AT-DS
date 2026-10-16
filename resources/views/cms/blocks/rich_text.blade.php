<x-section :data="$data">
    <div class="container-site">
        <div class="{{ ($data['width'] ?? 'narrow') === 'narrow' ? 'mx-auto max-w-3xl' : '' }}">
            @if(!empty($data['heading']))
                <x-section-heading :heading="$data['heading']" />
            @endif
            <div class="prose prose-lg prose-site max-w-none">
                {!! $site->html($data['content'] ?? '') !!}
            </div>
        </div>
    </div>
</x-section>
