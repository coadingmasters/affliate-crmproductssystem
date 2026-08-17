@extends('layouts.admin')

@section('title', 'Users · Med Alert')
@section('heading', 'Users')

@section('content')
    @php
        $filters = ['all' => 'All', 'user' => 'Customers', 'admin' => 'Admins'];
    @endphp

    {{-- Credentials to hand over, shown once right after creating/resetting --}}
    @if (session('credentials'))
        @php $cred = session('credentials'); @endphp
        <div class="rise mb-5 overflow-hidden rounded-2xl border border-accent/30 bg-accent/5">
            <div class="flex items-center gap-2.5 border-b border-accent/20 px-5 py-3">
                <svg class="h-5 w-5 text-accent" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.7 5.7l-1.6 1.6a1 1 0 01-.7.3H9v2a1 1 0 01-1 1H6v2a1 1 0 01-1 1H3a1 1 0 01-1-1v-2.6a1 1 0 01.3-.7L8.3 12A6 6 0 1121 9z"/>
                </svg>
                <h2 class="text-sm font-semibold text-ink">Share these login details with the user</h2>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                <div>
                    <p class="mb-1 text-xs uppercase tracking-wider text-muted">Email</p>
                    <div class="flex items-center gap-2">
                        <code id="cred-email" class="flex-1 truncate rounded-lg border border-line bg-card px-3 py-2 text-sm font-medium text-ink">{{ $cred['email'] }}</code>
                        <button type="button" class="copy-btn shrink-0 rounded-lg border border-line px-2.5 py-2 text-xs font-medium text-muted transition hover:border-accent hover:text-accent" data-target="cred-email">Copy</button>
                    </div>
                </div>
                <div>
                    <p class="mb-1 text-xs uppercase tracking-wider text-muted">Password</p>
                    <div class="flex items-center gap-2">
                        <code id="cred-pass" class="flex-1 truncate rounded-lg border border-line bg-card px-3 py-2 text-sm font-medium text-ink">{{ $cred['password'] }}</code>
                        <button type="button" class="copy-btn shrink-0 rounded-lg border border-line px-2.5 py-2 text-xs font-medium text-muted transition hover:border-accent hover:text-accent" data-target="cred-pass">Copy</button>
                    </div>
                </div>
            </div>

            <p class="border-t border-accent/20 px-5 py-2.5 text-xs text-muted">
                This password is shown only once — it is stored encrypted and cannot be read again. You can always set a new one from Edit.
            </p>
        </div>
    @endif

    <div class="rise mb-5 flex flex-wrap items-center justify-between gap-4">
        <div class="inline-flex rounded-xl border border-line bg-card p-1">
            @foreach ($filters as $value => $label)
                <a href="{{ route('admin.users.index', $value === 'all' ? [] : ['role' => $value]) }}"
                   class="rounded-lg px-3.5 py-1.5 text-sm font-medium transition
                          {{ $role === $value ? 'bg-gradient-to-r from-accent to-accent2 text-white shadow-md shadow-accent/25' : 'text-muted hover:bg-elevated hover:text-ink' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <a href="{{ route('admin.users.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-accent to-accent2 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-accent/25 transition hover:opacity-90">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v6m3-3h-6m-3-2a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Create User
        </a>
    </div>

    <div class="rise overflow-hidden rounded-2xl border border-line bg-card" style="--delay: 80ms">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-elevated text-xs uppercase tracking-wider text-muted">
                    <tr>
                        <th class="px-5 py-3.5 font-medium">User</th>
                        <th class="px-5 py-3.5 font-medium">Role</th>
                        <th class="px-5 py-3.5 font-medium">Orders</th>
                        <th class="px-5 py-3.5 font-medium">Created</th>
                        <th class="px-5 py-3.5 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($users as $account)
                        <tr class="row-hover hover:bg-elevated">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-accent/15 to-accent2/15 text-sm font-bold text-accent">
                                        {{ Str::upper(Str::substr($account->name, 0, 1)) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-medium text-ink">
                                            {{ $account->name }}
                                            @if ($account->is(auth()->user()))
                                                <span class="ml-1 text-xs font-normal text-muted">(you)</span>
                                            @endif
                                        </p>
                                        <p class="truncate text-xs text-muted">{{ $account->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                @if ($account->isAdmin())
                                    <span class="inline-flex rounded-full bg-accent/10 px-2.5 py-1 text-xs font-medium text-accent">Admin</span>
                                @else
                                    <span class="inline-flex rounded-full bg-muted/10 px-2.5 py-1 text-xs font-medium text-muted">Customer</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-muted">{{ $account->orders_count }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-muted">{{ $account->created_at->format('M j, Y') }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $account) }}"
                                       class="rounded-lg border border-line px-3 py-1.5 text-xs font-medium text-ink transition hover:border-accent/40 hover:bg-accent/10 hover:text-accent">
                                        Edit
                                    </a>
                                    @unless ($account->is(auth()->user()))
                                        <form method="POST" action="{{ route('admin.users.destroy', $account) }}"
                                              data-confirm-title="Delete user"
                                              data-confirm="{{ $account->name }} will lose access immediately. Their past orders are kept."
                                              data-confirm-text="Delete user">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-lg border border-danger/30 px-3 py-1.5 text-xs font-medium text-danger transition hover:bg-danger hover:text-white">
                                                Delete
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-14 text-center">
                                <p class="text-muted">No accounts yet.</p>
                                <a href="{{ route('admin.users.create') }}" class="mt-2 inline-block text-sm font-medium text-accent hover:underline">Create the first one</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($users->hasPages())
        <div class="mt-5">{{ $users->links() }}</div>
    @endif
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.copy-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const text = document.getElementById(button.dataset.target).textContent;
            navigator.clipboard.writeText(text).then(function () {
                const original = button.textContent;
                button.textContent = 'Copied';
                setTimeout(function () { button.textContent = original; }, 1400);
            });
        });
    });
</script>
@endpush
