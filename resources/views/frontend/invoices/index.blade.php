@extends('layouts.customer')

@section('title', 'Invoices · Med Alert')
@section('heading', 'Invoices')

@section('content')

    @if (session('status'))
        <div class="rise mb-4 rounded-2xl border border-success/30 bg-success/10 px-4 py-3 text-sm font-medium text-success">
            {{ session('status') }}
        </div>
    @endif

    {{-- What is still waiting to be claimed --}}
    <div class="rise mb-4 overflow-hidden rounded-2xl border border-brand/25 bg-gradient-to-br from-brand/10 via-card to-brand2/10 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4 p-5 sm:p-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-muted">Ready to claim</p>
                <p class="mt-2 text-4xl font-extrabold tracking-tight text-brand sm:text-[2.75rem] sm:leading-none">
                    ${{ number_format($unbilled, 2) }}
                </p>
                <p class="mt-2 text-xs text-muted">
                    {{ $unbilledCount }} {{ Str::plural('order', $unbilledCount) }} earned and not yet invoiced
                </p>
            </div>

            @if ($unbilledCount > 0)
                <a href="{{ route('invoices.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand to-brand2 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:opacity-90">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    New invoice
                </a>
            @endif
        </div>
    </div>

    {{-- Where the claims stand --}}
    <div class="rise mb-4 grid gap-3 sm:grid-cols-3" style="--delay: 60ms">
        @foreach ($totals as $row)
            <div class="rounded-2xl border border-line bg-card p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted">{{ $row['label'] }}</p>
                <p class="mt-1 text-2xl font-bold text-ink">${{ number_format($row['amount'], 2) }}</p>
                <p class="mt-1 text-xs text-muted">
                    {{ $row['count'] }} {{ Str::plural('invoice', $row['count']) }} &middot; {{ $row['help'] }}
                </p>
            </div>
        @endforeach
    </div>

    {{-- The claims themselves --}}
    <div class="rise overflow-hidden rounded-2xl border border-line bg-card shadow-sm" style="--delay: 120ms">
        @if ($invoices->count() > 0)
            <div class="divide-y divide-line">
                @foreach ($invoices as $invoice)
                    <a href="{{ route('invoices.show', $invoice) }}"
                       class="flex flex-wrap items-center justify-between gap-4 p-4 transition hover:bg-brand/5 sm:p-5">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <span class="font-mono text-sm font-bold text-ink">{{ $invoice->number }}</span>
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $invoice->statusClasses() }}">
                                    {{ $invoice->statusLabel() }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm font-medium text-ink">{{ $invoice->subjectLabel() }}</p>
                            <p class="mt-0.5 text-xs text-muted">
                                Sent {{ $invoice->sentAtLabel() }}
                                @if ($invoice->coversPeriod())
                                    &middot; {{ $invoice->orders_count }} {{ Str::plural('order', $invoice->orders_count) }}
                                @endif
                            </p>
                        </div>

                        <div class="text-right">
                            <p class="text-xl font-extrabold tracking-tight text-brand">
                                ${{ number_format($invoice->amount, 2) }}
                            </p>
                            @if ($invoice->admin_note)
                                <p class="mt-0.5 max-w-[16rem] truncate text-xs text-muted">
                                    Reply: {{ $invoice->admin_note }}
                                </p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="px-6 py-14 text-center">
                <p class="text-sm font-medium text-ink">You have not sent an invoice yet.</p>
                <p class="mt-1 text-xs text-muted">
                    Claim for a week once its orders have converted.
                </p>
                @if ($unbilledCount > 0)
                    <a href="{{ route('invoices.create') }}" class="mt-3 inline-block text-sm font-medium text-brand hover:underline">
                        Raise your first invoice
                    </a>
                @endif
            </div>
        @endif
    </div>

    @if ($invoices->hasPages())
        <div class="mt-4">{{ $invoices->links('vendor.pagination.admin') }}</div>
    @endif
@endsection
