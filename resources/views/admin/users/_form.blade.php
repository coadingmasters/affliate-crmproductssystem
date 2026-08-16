@php
    $input = 'w-full rounded-xl border bg-elevated px-3.5 py-2.5 text-sm text-ink placeholder-muted transition focus:outline-none focus:ring-2 focus:ring-accent/30';
    $okBorder = 'border-line focus:border-accent';
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <div class="rise rounded-2xl border border-line bg-card p-5 sm:p-6">
        <h2 class="mb-5 text-sm font-semibold text-ink">Account Details</h2>

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

        <div class="mt-5">
            <label class="mb-1.5 block text-sm font-medium text-ink">Role</label>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach ([['user', 'Customer', 'Can place orders and see their own history'], ['admin', 'Admin', 'Full access to this admin panel']] as [$value, $label, $hint])
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3.5 transition
                                  {{ old('role', $user->role) === $value ? 'border-accent bg-accent/5' : 'border-line hover:border-accent/40' }}">
                        <input type="radio" name="role" value="{{ $value }}" @checked(old('role', $user->role) === $value)
                               class="mt-0.5 h-4 w-4 border-line text-accent focus:ring-accent/30">
                        <span>
                            <span class="block text-sm font-medium text-ink">{{ $label }}</span>
                            <span class="block text-xs text-muted">{{ $hint }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('role')
                <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="rise rounded-2xl border border-line bg-card p-5 sm:p-6" style="--delay: 90ms">
        <h2 class="text-sm font-semibold text-ink">{{ $user->exists ? 'Reset Password' : 'Password' }}</h2>
        <p class="mb-4 mt-0.5 text-xs text-muted">
            {{ $user->exists
                ? 'Leave blank to keep the current password. Setting a new one shows it once so you can share it.'
                : 'You will see this once after saving, so you can pass it to the user.' }}
        </p>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-start">
            <div class="flex-1">
                <input type="text" name="password" id="password" value="{{ old('password') }}"
                       placeholder="{{ $user->exists ? 'Leave blank to keep current' : 'At least 8 characters' }}"
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
        <p class="mt-2 text-xs text-muted">Shown in plain text on purpose — you need to read it to share it.</p>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="rounded-xl bg-gradient-to-r from-accent to-accent2 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-accent/25 transition hover:opacity-90">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('admin.users.index') }}"
           class="rounded-xl border border-line px-5 py-2.5 text-sm font-medium text-ink transition hover:bg-elevated">Cancel</a>
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
