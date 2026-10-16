@php
    $faqs = \App\Models\Faq::published()
        ->when($data['category'] ?? null, fn ($q, $category) => $q->where('category', $category))
        ->ordered()
        ->get();
@endphp
@if($faqs->isNotEmpty())
    <x-section :data="$data">
        <div class="container-site">
            <x-section-heading :heading="$data['heading'] ?? null" :intro="$data['intro'] ?? null" />
            <div class="mx-auto max-w-3xl divide-y divide-line border-y border-line" x-data="{ open: null }">
                @foreach($faqs as $faq)
                    <div>
                        <h3>
                            <button type="button" @click="open = open === {{ $faq->id }} ? null : {{ $faq->id }}"
                                    :aria-expanded="open === {{ $faq->id }}"
                                    class="flex w-full items-center justify-between gap-6 py-6 text-left font-heading text-2xl transition hover:text-primary">
                                {{ $faq->question }}
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-line transition"
                                      :class="open === {{ $faq->id }} && 'rotate-45 bg-primary border-primary text-on-primary'">
                                    <x-heroicon-m-plus class="h-4 w-4" />
                                </span>
                            </button>
                        </h3>
                        <div x-show="open === {{ $faq->id }}" x-collapse x-cloak>
                            <div class="prose prose-site max-w-none pb-6">
                                {!! $site->html($faq->answer) !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-section>
@endif
