@php
    $navItems = $site->navItems();
    $showCta = $site->get('navigation.show_cta', true);
    $ctaLabel = $site->get('navigation.cta_label', 'Book a Consult');
    $ctaUrl = $site->url($site->get('navigation.cta_url', '/contact'));
    $social = $site->socialLinks();
@endphp

@if($site->get('topbar.enabled', true) && ($site->get('topbar.text') || $social))
    <div class="bg-primary text-on-primary text-xs sm:text-[13px]">
        <div class="container-site flex items-center justify-between gap-4 py-2">
            <div class="hidden items-center gap-3 sm:flex">
                <x-social-links :links="$social" class="gap-3 opacity-90" size="w-3.5 h-3.5" />
            </div>
            <p class="flex-1 text-center tracking-wide">
                {{ $site->get('topbar.text') }}
                @if($site->get('topbar.link_label') && $site->get('topbar.link_url'))
                    <a href="{{ $site->url($site->get('topbar.link_url')) }}" class="ml-2 font-semibold underline underline-offset-4 hover:no-underline">{{ $site->get('topbar.link_label') }}</a>
                @endif
            </p>
            <div class="hidden sm:block">
                @if($site->get('contact.phone'))
                    <a href="{{ $site->tel($site->get('contact.phone')) }}" class="opacity-90 hover:opacity-100">{{ $site->get('contact.phone') }}</a>
                @endif
            </div>
        </div>
    </div>
@endif

<header x-data="{ open: false, scrolled: false }"
        x-init="scrolled = window.scrollY > 10"
        @scroll.window="scrolled = window.scrollY > 10"
        @keydown.escape.window="open = false"
        :class="scrolled ? 'shadow-[0_6px_24px_-12px_rgb(0_0_0/0.2)]' : ''"
        class="sticky top-0 z-40 border-b border-line bg-cream/90 backdrop-blur transition-shadow">
    <div class="container-site flex items-center justify-between gap-6 py-4">
        <x-logo />

        {{-- Desktop navigation --}}
        <nav class="hidden items-center gap-1 lg:flex" aria-label="Main">
            @foreach($navItems as $item)
                @if(count($item['children']))
                    <div class="relative" x-data="{ sub: false }" @mouseenter="sub = true" @mouseleave="sub = false">
                        <button type="button" @click="sub = !sub" :aria-expanded="sub"
                                class="flex items-center gap-1 rounded-full px-4 py-2 text-[15px] font-medium transition hover:text-primary {{ $site->isActive($item['url']) ? 'text-primary' : '' }}">
                            {{ $item['label'] }}
                            <x-heroicon-m-chevron-down class="h-4 w-4 transition" ::class="sub && 'rotate-180'" />
                        </button>
                        <div x-show="sub" x-cloak x-transition.origin.top
                             class="absolute left-1/2 top-full w-60 -translate-x-1/2 pt-2">
                            <div class="card overflow-hidden py-2">
                                @if($item['url'] !== '#' && $item['url'] !== url('#'))
                                    <a href="{{ $item['url'] }}" class="block px-5 py-2.5 text-sm font-semibold hover:bg-soft hover:text-primary">{{ $item['label'] }}</a>
                                @endif
                                @foreach($item['children'] as $child)
                                    <a href="{{ $child['url'] }}" class="block px-5 py-2.5 text-sm hover:bg-soft hover:text-primary">{{ $child['label'] }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ $item['url'] }}" @if($item['external']) target="_blank" rel="noopener" @endif
                       class="rounded-full px-4 py-2 text-[15px] font-medium transition hover:text-primary {{ $site->isActive($item['url']) ? 'text-primary' : '' }}">
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach

            @if($showCta && $ctaLabel)
                <a href="{{ $ctaUrl }}" class="btn-primary ml-3 !py-2.5">{{ $ctaLabel }}</a>
            @endif
        </nav>

        <button type="button" class="-mr-2 rounded-full p-2 lg:hidden" @click="open = true" aria-label="Open menu">
            <x-heroicon-o-bars-3 class="h-7 w-7" />
        </button>
    </div>

    {{-- Mobile navigation (teleported: the header's backdrop-blur would otherwise trap position:fixed) --}}
    <template x-teleport="body">
    <div x-show="open" x-cloak class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true">
        <div x-show="open" x-transition.opacity class="absolute inset-0 bg-dark/40" @click="open = false"></div>
        <nav x-show="open"
             x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
             class="absolute inset-y-0 right-0 flex w-full max-w-sm flex-col overflow-y-auto bg-cream px-6 py-5 shadow-2xl">
            <div class="mb-6 flex items-center justify-between">
                <x-logo class="scale-90 origin-left" />
                <button type="button" class="rounded-full p-2" @click="open = false" aria-label="Close menu">
                    <x-heroicon-o-x-mark class="h-7 w-7" />
                </button>
            </div>
            <div class="flex flex-col divide-y divide-line">
                @foreach($navItems as $item)
                    @if(count($item['children']))
                        <div x-data="{ sub: false }">
                            <button type="button" @click="sub = !sub" class="flex w-full items-center justify-between py-3.5 text-lg font-medium">
                                {{ $item['label'] }}
                                <x-heroicon-m-chevron-down class="h-5 w-5 transition" ::class="sub && 'rotate-180'" />
                            </button>
                            <div x-show="sub" x-collapse class="pb-3 pl-4">
                                @if($item['url'] !== '#' && $item['url'] !== url('#'))
                                    <a href="{{ $item['url'] }}" class="block py-2 text-muted">{{ $item['label'] }}</a>
                                @endif
                                @foreach($item['children'] as $child)
                                    <a href="{{ $child['url'] }}" class="block py-2 text-muted">{{ $child['label'] }}</a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ $item['url'] }}" @if($item['external']) target="_blank" rel="noopener" @endif class="py-3.5 text-lg font-medium {{ $site->isActive($item['url']) ? 'text-primary' : '' }}">{{ $item['label'] }}</a>
                    @endif
                @endforeach
            </div>
            @if($showCta && $ctaLabel)
                <a href="{{ $ctaUrl }}" class="btn-primary mt-8">{{ $ctaLabel }}</a>
            @endif
            <div class="mt-auto space-y-2 pt-10 text-sm text-muted">
                @if($site->get('contact.email'))
                    <a href="mailto:{{ $site->get('contact.email') }}" class="block">{{ $site->get('contact.email') }}</a>
                @endif
                @if($site->get('contact.phone'))
                    <a href="{{ $site->tel($site->get('contact.phone')) }}" class="block">{{ $site->get('contact.phone') }}</a>
                @endif
                <x-social-links :links="$social" class="gap-4 pt-3 text-primary" />
            </div>
        </nav>
    </div>
    </template>
</header>
