@php
    $input = 'w-full rounded-xl border bg-elevated px-3.5 py-2.5 text-sm text-ink placeholder-muted transition focus:outline-none focus:ring-2 focus:ring-accent/30';
    $okBorder = 'border-line focus:border-accent';

    // The super admin record is password-only; everything else is a customer.
    $adminAccount = $user->exists && $user->isAdmin();
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    @if ($adminAccount)
        <div class="rise rounded-2xl border border-info/30 bg-info/10 p-4 sm:p-5">
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-info" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-ink">Administrator account</p>
                    <p class="mt-0.5 text-sm text-muted">
                        This is the system administrator. Only the password can be changed &mdash; the name, email and role are locked.
                    </p>
                </div>
            </div>
        </div>

        <div class="rise rounded-2xl border border-line bg-card p-5 sm:p-6" style="--delay: 60ms">
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wider text-muted">Name</dt>
                    <dd class="mt-1 text-sm font-medium text-ink">{{ $user->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-muted">Email</dt>
                    <dd class="mt-1 truncate text-sm font-medium text-ink">{{ $user->email }}</dd>
                </div>
            </dl>
        </div>
    @else
        <div class="rise rounded-2xl border border-line bg-card p-5 sm:p-6">
            <div class="mb-5 flex items-center gap-2.5">
                <h2 class="text-sm font-semibold text-ink">Account Details</h2>
                <span class="rounded-full bg-muted/10 px-2.5 py-0.5 text-xs font-medium text-muted">Customer</span>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1.5 block text-sm font-medium text-ink">Full Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                           placeholder="John Smith"
                           class="{{ $input }} {{ $errors->has('name') ? 'border-danger' : $okBorder }}">
                    @error('name')
                        <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-ink">Email <span class="font-normal text-muted">(their username)</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                           placeholder="john@example.com"
                           class="{{ $input }} {{ $errors->has('email') ? 'border-danger' : $okBorder }}">
                    @error('email')
                        <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <p class="mt-4 text-xs text-muted">
                All accounts created here are customers. They can place orders and see their own dashboard only.
            </p>
        </div>
    @endif

    <div class="rise rounded-2xl border border-line bg-card p-5 sm:p-6" style="--delay: 90ms">
        <h2 class="text-sm font-semibold text-ink">{{ $user->exists ? 'Reset Password' : 'Password' }}</h2>
        <p class="mb-4 mt-0.5 text-xs text-muted">
            {{ $user->exists
                ? ($adminAccount
                    ? 'Enter a new password for the administrator account.'
                    : 'Leave blank to keep the current password. Setting a new one shows it once so you can share it.')
                : 'You will see this once after saving, so you can pass it to the user.' }}
        </p>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-start">
            <div class="flex-1">
                <input type="text" name="password" id="password" value="{{ old('password') }}"
                       placeholder="{{ $user->exists && ! $adminAccount ? 'Leave blank to keep current' : 'At least 8 characters' }}"
                       autocomplete="new-password"
                       class="{{ $input }} {{ $errors->has('password') ? 'border-danger' : $okBorder }}">
                @error('password')
                    <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>
            <button type="button" id="suggest-password"
                    class="shrink-0 rounded-xl border border-accent px-4 py-2.5 text-xs font-semibold text-accent transition hover:bg-accent hover:text-white"
                    data-suggestion="{{ $suggestedPassword }}">
                Generate
            </button>
        </div>
        <p class="mt-2 text-xs text-muted">Shown in plain text on purpose &mdash; you need to read it to share it.</p>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <button type="submit"
                class="rounded-xl bg-gradient-to-r from-accent to-accent2 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-accent/25 transition hover:opacity-90">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('admin.users.index') }}"
           class="rounded-xl border border-line px-5 py-2.5 text-center text-sm font-medium text-ink transition hover:bg-elevated">Cancel</a>
    </div>
</form>

@push('scripts')
<script>
    (function () {
        const button = document.getElementById('suggest-password');
        const field = document.getElementById('password');

        button.addEventListener('click', function () {
            field.value = button.dataset.suggestion;
            field.focus();
        });
    })();
</script>
@endpush
