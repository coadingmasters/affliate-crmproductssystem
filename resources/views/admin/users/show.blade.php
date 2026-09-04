@extends('layouts.admin')

@section('title', $user->name.' · Med Alert')
@section('heading', $user->name)

@section('content')

    {{-- Who this is --}}
    <div class="rise mb-4 flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-line bg-card p-5">
        <div class="flex items-center gap-4">
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-accent to-accent2 text-xl font-bold text-white shadow-lg shadow-accent/25">
                {{ Str::upper(Str::substr($user->name, 0, 1)) }}
            </span>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="truncate text-lg font-bold text-ink">{{ $user->name }}</h2>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $user->isAdmin() ? 'bg-accent/10 text-accent' : 'bg-elevated text-muted' }}">
                        {{ $user->isAdmin() ? 'Administrator' : 'Customer' }}
                    </span>
                </div>
                <p class="truncate text-sm text-muted">{{ $user->email }}</p>
                <p class="mt-0.5 text-xs text-muted">
                    Joined {{ $user->created_at?->timezone(config('app.display_timezone'))->format('M j, Y') }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.orders.index', ['user_ids' => [$user->id]]) }}"
               class="rounded-xl border border-line px-4 py-2.5 text-sm font-medium text-muted transition hover:border-accent hover:text-accent">
                View orders
            </a>
            <a href="{{ route('admin.users.index') }}"
               class="rounded-xl border border-line px-4 py-2.5 text-sm font-medium text-muted transition hover:border-accent hover:text-accent">
                All accounts
            </a>
        </div>
    </div>

    {{-- Totals --}}
    <div class="rise mb-4 grid grid-cols-2 gap-3 lg:grid-cols-5" style="--delay: 60ms">
        <div class="rounded-2xl border border-line bg-card p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-muted">Orders</p>
            <p class="mt-1.5 text-2xl font-bold text-ink">{{ number_format($orderCount) }}</p>
        </div>
        <div class="rounded-2xl border border-line bg-card p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-muted">Confirmed Value</p>
            <p class="mt-1.5 truncate text-2xl font-bold text-accent">${{ number_format($orderValue, 2) }}</p>
        </div>
        @foreach ($invoiceTotals as $row)
            <div class="rounded-2xl border border-line bg-card p-4">
                <p class="text-xs font-medium uppercase tracking-wider text-muted">{{ $row['label'] }} Invoices</p>
                <p class="mt-1.5 text-2xl font-bold text-{{ $row['tone'] }}">{{ number_format($row['count']) }}</p>
                <p class="mt-1 truncate text-[11px] text-muted">${{ number_format($row['amount'], 2) }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-4 xl:grid-cols-3">

        {{-- Invoices --}}
        <div class="rise xl:col-span-2" style="--delay: 110ms">
            <div class="overflow-hidden rounded-2xl border border-line bg-card">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4">
                    <h3 class="text-sm font-semibold text-ink">Invoices</h3>

                    <div class="flex flex-wrap items-center gap-1.5">
                        @foreach (['all' => 'All'] + collect(\App\Models\Invoice::STATUS_META)->map(fn ($m) => $m['label'])->all() as $value => $label)
                            <a href="{{ route('admin.users.show', [$user, 'invoice_status' => $value === 'all' ? null : $value]) }}"
                               class="rounded-lg px-3 py-1.5 text-xs font-medium transition
                                      {{ $invoiceStatus === $value
                                          ? 'bg-accent/10 text-accent'
                                          : 'text-muted hover:bg-elevated hover:text-ink' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="divide-y divide-line">
                    @forelse ($invoices as $invoice)
                        <div id="invoice-{{ $invoice->id }}" class="p-4 sm:p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-mono text-sm font-bold text-ink">{{ $invoice->number }}</span>
                                        <span data-chip="{{ $invoice->id }}"
                                              class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $invoice->statusClasses() }}">
                                            {{ $invoice->statusLabel() }}
                                        </span>
                                    </div>

                                    <p class="mt-1 text-sm text-muted">
                                        <a href="{{ route('admin.orders.show', $invoice->order_id) }}" class="font-medium text-ink transition hover:text-accent">
                                            Order #{{ $invoice->order_id }}
                                        </a>
                                        &middot; {{ $invoice->order?->product?->name ?? 'Removed product' }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-muted">
                                        Sent {{ $invoice->sentAtLabel() }}
                                        <span data-changed="{{ $invoice->id }}">
                                            @if ($invoice->statusChangedAtLabel())
                                                &middot; updated {{ $invoice->statusChangedAtLabel() }}
                                            @endif
                                        </span>
                                    </p>

                                    @if ($invoice->note)
                                        <p class="mt-2 rounded-lg border border-line bg-elevated px-3 py-2 text-xs text-ink">
                                            <span class="font-semibold">Their note:</span> {{ $invoice->note }}
                                        </p>
                                    @endif
                                </div>

                                <div class="text-right">
                                    <p class="text-xl font-extrabold tracking-tight text-ink">${{ number_format($invoice->amount, 2) }}</p>

                                    <select data-invoice="{{ $invoice->id }}"
                                            class="invoice-status mt-2 rounded-lg border border-line bg-elevated px-2.5 py-1.5 text-xs font-medium text-ink transition focus:border-accent focus:outline-none">
                                        @foreach (\App\Models\Invoice::STATUS_META as $value => $meta)
                                            <option value="{{ $value }}" @selected($invoice->status === $value)>{{ $meta['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-16 text-center">
                            <svg class="mx-auto mb-3 h-10 w-10 text-muted" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-sm text-muted">
                                {{ $invoiceStatus === 'all'
                                    ? 'This account has not sent any invoices yet.'
                                    : 'No '.Str::lower($invoiceStatus).' invoices.' }}
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

            @if ($invoices->hasPages())
                <div class="mt-4">{{ $invoices->links('vendor.pagination.admin') }}</div>
            @endif
        </div>

        {{-- Settings --}}
        <div class="rise" style="--delay: 160ms">
            <div class="rounded-2xl border border-line bg-card p-5">
                <h3 class="mb-1 text-sm font-semibold text-ink">Settings</h3>
                <p class="mb-4 text-xs text-muted">
                    {{ $user->isAdmin()
                        ? 'Only the password can be changed on the administrator account.'
                        : 'Change the name, email or password for this account.' }}
                </p>

                <a href="{{ route('admin.users.edit', $user) }}"
                   class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-accent to-accent2 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-accent/25 transition hover:opacity-90">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.4-9.6a2 2 0 112.8 2.8L12 16l-4 1 1-4 9.6-9.6z"/>
                    </svg>
                    Edit account
                </a>

                @unless ($user->isAdmin())
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                          class="mt-2"
                          data-confirm-title="Delete account"
                          data-confirm="{{ $user->name }} and their access will be removed. Their orders stay in the system."
                          data-confirm-text="Delete account">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full rounded-xl border border-danger/30 px-4 py-2.5 text-sm font-medium text-danger transition hover:bg-danger hover:text-white">
                            Delete account
                        </button>
                    </form>
                @endunless

                <dl class="mt-5 space-y-3 border-t border-line pt-4 text-sm">
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-muted">Role</dt>
                        <dd class="font-medium text-ink">{{ $user->isAdmin() ? 'Administrator' : 'Customer' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-muted">Email</dt>
                        <dd class="truncate font-medium text-ink">{{ $user->email }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-muted">Account ID</dt>
                        <dd class="font-medium text-ink">#{{ $user->id }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <div id="toast" class="fixed bottom-5 left-1/2 z-[120] hidden -translate-x-1/2">
        <div id="toast-body"></div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const endpoint = @json(route('admin.invoices.status', ['invoice' => '__ID__']));
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const toast = document.getElementById('toast');
        const toastBody = document.getElementById('toast-body');

        function showToast(message, isError) {
            toastBody.textContent = message;
            toastBody.className = 'rounded-xl border px-4 py-2.5 text-sm font-medium shadow-2xl '
                + (isError ? 'border-danger/30 bg-danger/10 text-danger' : 'border-success/30 bg-success/10 text-success');
            toast.classList.remove('hidden');
            clearTimeout(toast.dataset.timer);
            toast.dataset.timer = setTimeout(() => toast.classList.add('hidden'), 2600);
        }

        document.querySelectorAll('.invoice-status').forEach(function (select) {
            let previous = select.value;

            select.addEventListener('change', async function () {
                const id = select.dataset.invoice;
                select.disabled = true;

                try {
                    const response = await fetch(endpoint.replace('__ID__', id), {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({ status: select.value }),
                    });

                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(payload.message || 'Could not update the invoice.');
                    }

                    const chip = document.querySelector('[data-chip="' + id + '"]');
                    chip.textContent = payload.label;
                    chip.className = 'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ' + payload.classes;
                    chip.animate(
                        [{ transform: 'scale(1)' }, { transform: 'scale(1.12)' }, { transform: 'scale(1)' }],
                        { duration: 320, easing: 'ease-out' },
                    );

                    document.querySelector('[data-changed="' + id + '"]').textContent = ' · updated ' + payload.changed;

                    previous = select.value;
                    showToast(payload.message, false);
                } catch (error) {
                    select.value = previous;
                    showToast(error.message, true);
                } finally {
                    select.disabled = false;
                }
            });
        });
    })();
</script>
@endpush
