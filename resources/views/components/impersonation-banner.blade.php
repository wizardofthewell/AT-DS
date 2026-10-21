{{-- Shown while a super admin is logged in as another user. Inline styles so it works in both the admin panel and the public site. --}}
@if(\App\Support\Impersonation::isActive())
    <div style="position: relative; z-index: 50; display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.75rem; padding: 0.5rem 1rem; background: #b45309; color: #fff; font-size: 0.875rem; line-height: 1.25rem;">
        <span>You are logged in as <strong>{{ auth()->user()?->name }}</strong>. Everything you do is recorded under their name, marked as done by you.</span>
        <form method="POST" action="{{ route('impersonation.stop') }}" style="margin: 0;">
            @csrf
            <button type="submit" style="padding: 0.25rem 0.75rem; border-radius: 0.375rem; background: #fff; color: #78350f; font-weight: 600; cursor: pointer; border: 0;">
                Return to my account
            </button>
        </form>
    </div>
@endif
