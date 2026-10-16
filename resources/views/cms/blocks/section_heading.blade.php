<x-section :data="$data" padding="pt-16 sm:pt-24 pb-0">
    <div class="container-site">
        <x-section-heading :eyebrow="$data['eyebrow'] ?? null" :heading="$data['heading'] ?? null" :intro="$data['intro'] ?? null" class="!mb-0" />
    </div>
</x-section>
