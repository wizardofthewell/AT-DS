@php
    $social = $site->socialLinks();
    $navItems = $site->navItems();
@endphp
<footer class="tone-dark mt-auto">
    <div class="container-site grid gap-12 py-16 md:grid-cols-12">
        <div class="md:col-span-5">
            <x-logo light />
            @if($site->motto())
                <p class="mt-5 font-heading text-2xl italic text-accent">{{ $site->motto() }}</p>
            @endif
            @if($site->get('footer.about'))
                <p class="mt-5 max-w-sm leading-relaxed text-muted">{{ $site->get('footer.about') }}</p>
            @endif
            <x-social-links :links="$social" class="mt-6 gap-4" size="w-5 h-5" />
        </div>

        <div class="md:col-span-3">
            <h2 class="eyebrow mb-4 font-sans">Explore</h2>
            <ul class="space-y-2.5">
                @foreach($navItems as $item)
                    <li><a href="{{ $item['url'] }}" class="text-muted transition hover:text-white">{{ $item['label'] }}</a></li>
                @endforeach
            </ul>
        </div>

        <div class="md:col-span-4">
            <h2 class="eyebrow mb-4 font-sans">Get in touch</h2>
            <ul class="space-y-3 text-muted">
                @if($site->get('contact.email'))
                    <li class="flex gap-3">
                        <x-heroicon-o-envelope class="mt-0.5 h-5 w-5 shrink-0 text-accent" />
                        <a href="mailto:{{ $site->get('contact.email') }}" class="hover:text-white">{{ $site->get('contact.email') }}</a>
                    </li>
                @endif
                @if($site->get('contact.phone'))
                    <li class="flex gap-3">
                        <x-heroicon-o-phone class="mt-0.5 h-5 w-5 shrink-0 text-accent" />
                        <a href="{{ $site->tel($site->get('contact.phone')) }}" class="hover:text-white">{{ $site->get('contact.phone') }}</a>
                    </li>
                @endif
                @if($site->get('contact.service_area'))
                    <li class="flex gap-3">
                        <x-heroicon-o-map-pin class="mt-0.5 h-5 w-5 shrink-0 text-accent" />
                        <span>{{ $site->get('contact.service_area') }}</span>
                    </li>
                @endif
                @if($site->get('contact.hours'))
                    <li class="flex gap-3">
                        <x-heroicon-o-clock class="mt-0.5 h-5 w-5 shrink-0 text-accent" />
                        <span>{{ $site->get('contact.hours') }}</span>
                    </li>
                @endif
            </ul>
        </div>
    </div>

    @if($site->get('footer.acknowledgement'))
        <div class="container-site">
            <p class="border-t border-line py-8 text-center text-sm leading-relaxed text-muted">{{ $site->get('footer.acknowledgement') }}</p>
        </div>
    @endif

    <div class="border-t border-line">
        <div class="container-site flex flex-col items-center justify-between gap-2 py-6 text-xs text-muted sm:flex-row">
            <p>{{ $site->placeholders($site->get('footer.copyright', '© {year} {name}')) }}</p>
            <a href="{{ url('/admin') }}" class="hover:text-white">Admin login</a>
        </div>
    </div>
</footer>
