@props(['askDueDate' => true])
@php
    $services = \App\Models\Service::published()->ordered()->pluck('title');
@endphp
@if(session('inquiry_sent'))
    <div class="card flex flex-col items-center p-10 text-center" role="status">
        <span class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-soft text-primary">
            <x-heroicon-o-check class="h-7 w-7" />
        </span>
        <h3 class="text-3xl">Message sent</h3>
        <p class="mt-3 max-w-md text-muted">{{ $site->get('contact.thank_you', 'Thank you for reaching out! I\'ll be in touch within one business day.') }}</p>
    </div>
@else
    <form method="POST" action="{{ route('contact.submit') }}" {{ $attributes->merge(['class' => 'card grid gap-5 p-6 sm:grid-cols-2 sm:p-10']) }}>
        @csrf
        <div class="hidden" aria-hidden="true">
            <label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
        </div>

        <div>
            <label for="cf-name" class="form-label">Name</label>
            <input id="cf-name" name="name" value="{{ old('name') }}" required autocomplete="name" class="form-input">
            @error('name') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="cf-email" class="form-label">Email</label>
            <input id="cf-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="form-input">
            @error('email') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="cf-phone" class="form-label">Phone <span class="font-normal text-muted">(optional)</span></label>
            <input id="cf-phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" class="form-input">
        </div>
        @if($askDueDate)
            <div>
                <label for="cf-due" class="form-label">Due date or baby's birthday <span class="font-normal text-muted">(optional)</span></label>
                <input id="cf-due" type="date" name="due_date" value="{{ old('due_date') }}" class="form-input">
                @error('due_date') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
        @endif
        @if($services->isNotEmpty())
            <div class="{{ $askDueDate ? 'sm:col-span-2' : '' }}">
                <label for="cf-service" class="form-label">I'm interested in</label>
                <select id="cf-service" name="service_interest" class="form-input">
                    <option value="">Not sure yet — let's chat</option>
                    @foreach($services as $title)
                        <option @selected(old('service_interest') === $title)>{{ $title }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="sm:col-span-2">
            <label for="cf-message" class="form-label">Message</label>
            <textarea id="cf-message" name="message" rows="5" required class="form-input" placeholder="Tell me a little about yourself and how I can support you.">{{ old('message') }}</textarea>
            @error('message') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
        <div class="sm:col-span-2">
            <button type="submit" class="btn-primary w-full sm:w-auto">
                Send Message
                <x-heroicon-m-paper-airplane class="h-4 w-4" />
            </button>
        </div>
    </form>
@endif
