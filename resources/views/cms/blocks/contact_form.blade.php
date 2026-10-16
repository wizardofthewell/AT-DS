@php
    $showDetails = $data['show_details'] ?? true;
    $data['anchor'] = ($data['anchor'] ?? null) ?: 'contact';
@endphp
<x-section :data="$data">
    <div class="container-site">
        <div class="grid gap-12 {{ $showDetails ? 'lg:grid-cols-5' : 'mx-auto max-w-3xl' }}">
            <div class="{{ $showDetails ? 'lg:col-span-2' : 'text-center' }}">
                <x-section-heading :heading="$data['heading'] ?? null" :intro="$data['intro'] ?? null" :align="$showDetails ? 'left' : 'center'" class="!mb-8" />

                @if($showDetails)
                    <ul class="space-y-5">
                        @if($site->get('contact.email'))
                            <li class="flex items-start gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-accent-soft text-primary"><x-heroicon-o-envelope class="h-5 w-5" /></span>
                                <div><p class="text-sm text-muted">Email</p><a href="mailto:{{ $site->get('contact.email') }}" class="font-semibold hover:text-primary">{{ $site->get('contact.email') }}</a></div>
                            </li>
                        @endif
                        @if($site->get('contact.phone'))
                            <li class="flex items-start gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-accent-soft text-primary"><x-heroicon-o-phone class="h-5 w-5" /></span>
                                <div><p class="text-sm text-muted">Phone / text</p><a href="{{ $site->tel($site->get('contact.phone')) }}" class="font-semibold hover:text-primary">{{ $site->get('contact.phone') }}</a></div>
                            </li>
                        @endif
                        @if($site->get('contact.service_area'))
                            <li class="flex items-start gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-accent-soft text-primary"><x-heroicon-o-map-pin class="h-5 w-5" /></span>
                                <div><p class="text-sm text-muted">Serving</p><p class="font-semibold">{{ $site->get('contact.service_area') }}</p></div>
                            </li>
                        @endif
                        @if($site->get('contact.booking_url'))
                            <li class="pt-2">
                                <a href="{{ $site->get('contact.booking_url') }}" target="_blank" rel="noopener" class="btn-outline">
                                    <x-heroicon-o-calendar-days class="h-5 w-5" /> Book a time online
                                </a>
                            </li>
                        @endif
                    </ul>
                @endif
            </div>
            <div class="{{ $showDetails ? 'lg:col-span-3' : '' }}">
                <x-contact-form :ask-due-date="$data['ask_due_date'] ?? true" />
            </div>
        </div>
    </div>
</x-section>
