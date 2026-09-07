@extends('layouts.customer')

@section('title', 'Invoices · Med Alert')
@section('heading', 'Invoices')

@php
    $input = 'w-full rounded-xl border border-line bg-elevated px-3.5 py-2.5 text-sm text-ink placeholder-muted transition focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25';
    $th = 'border border-[#3E9E77] px-3 py-3 text-center text-xs font-bold uppercase tracking-wide';
    $td = 'border border-[#CBDDD3] px-3 py-2.5 text-center';
@endphp

@section('content')

    @if (session('error'))
        <div data-print-hide class="rise mb-4 rounded-2xl border border-danger/30 bg-danger/10 px-4 py-3 text-sm font-medium text-danger">
            {{ session('error') }}
        </div>
    @endif

    @error('orders')
        <div class="rise mb-4 rounded-2xl border border-danger/30 bg-danger/10 px-4 py-3 text-sm font-medium text-danger">{{ $message }}</div>
    @enderror

    {{-- What is still owed --}}
    <div class="rise mb-4 overflow-hidden rounded-2xl border border-brand/25 bg-gradient-to-br from-brand/10 via-card to-brand2/10 p-5 shadow-sm sm:p-6">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted">Ready to claim</p>
        <p class="mt-2 text-4xl font-extrabold tracking-tight text-brand sm:text-[2.75rem] sm:leading-none">
            ${{ number_format($unbilled, 2) }}
        </p>
        <p class="mt-2 text-xs text-muted">
            {{ $unbilledCount }} {{ Str::plural('order', $unbilledCount) }} earned and not yet invoiced
        </p>
    </div>

    {{-- ── Build an invoice ─────────────────────────────────────────── --}}
    <section id="build" class="rise mb-8" style="--delay: 60ms">
        <h2 class="mb-3 flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-muted">
            <span class="grid h-6 w-6 place-items-center rounded-lg bg-brand/10 text-xs font-extrabold text-brand">1</span>
            Build an invoice
        </h2>

        {{-- Filters --}}
        <form method="GET" action="{{ route('invoices.index') }}" id="filter-form"
              class="mb-3 rounded-2xl border border-line bg-card p-4 shadow-sm sm:p-5">

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
                <div class="sm:col-span-2 xl:col-span-1">
                    <label for="q" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-muted">Search</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                        </svg>
                        <input type="search" name="q" id="q" value="{{ $filters['q'] }}"
                               placeholder="Name, address or #id" class="{{ $input }} pl-9">
                    </div>
                </div>

                <div>
                    <label for="period" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-muted">Date range</label>
                    <select name="period" id="period" class="{{ $input }}">
                        @foreach ($periods as $value => $label)
                            <option value="{{ $value }}" @selected($filters['period'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-muted">Status</label>
                    <select name="status" id="status" class="{{ $input }}">
                        <option value="all">All earned</option>
                        @foreach ($statusMeta as $value => $meta)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="product_id" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-muted">Product</label>
                    <select name="product_id" id="product_id" class="{{ $input }}">
                        <option value="">All products</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected($filters['product_id'] === $product->id)>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="sort" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-muted">Sort by</label>
                    <select name="sort" id="sort" class="{{ $input }}">
                        @foreach ($sorts as $value => $label)
                            <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="custom-range" class="mt-3 grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4 {{ $filters['period'] === 'custom' ? 'grid' : 'hidden' }}">
                <div>
                    <label for="from" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-muted">From</label>
                    <input type="date" name="from" id="from" value="{{ $filters['from'] }}" class="{{ $input }}">
                </div>
                <div>
                    <label for="to" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-muted">To</label>
                    <input type="date" name="to" id="to" value="{{ $filters['to'] }}" class="{{ $input }}">
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-3 border-t border-line pt-4">
                <button type="submit" class="rounded-xl bg-gradient-to-r from-brand to-brand2 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:opacity-90">
                    Apply filters
                </button>

                @if ($activeFilterCount > 0)
                    <a href="{{ route('invoices.index') }}"
                       class="rounded-xl border border-line px-4 py-2.5 text-sm font-medium text-muted transition hover:border-danger hover:text-danger">
                        Clear ({{ $activeFilterCount }})
                    </a>
                @endif

                <p class="ml-auto text-xs text-muted">Showing earned orders not yet invoiced &middot; {{ $rangeLabel }}</p>
            </div>
        </form>

        {{-- Live totals for the ticked rows --}}
        <div class="mb-3 grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-line bg-card p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted">Selected</p>
                <p class="mt-1 text-2xl font-bold text-ink">
                    <span data-count>0</span>
                    <span class="text-sm font-medium text-muted">of {{ $orders->count() }} {{ Str::plural('order', $orders->count()) }}</span>
                </p>
            </div>
            <div class="rounded-2xl border border-line bg-card p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted">Order value</p>
                <p class="mt-1 text-2xl font-bold text-ink" data-value>$0.00</p>
            </div>
            <div class="rounded-2xl border border-brand/30 bg-brand/5 p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted">You are claiming</p>
                <p class="mt-1 text-2xl font-bold text-brand" data-total>$0.00</p>
            </div>
        </div>

        {{-- Pick, then generate --}}
        <form method="POST" action="{{ route('invoices.store') }}" id="claim-form">
            @csrf
            <div class="overflow-hidden rounded-2xl border border-line bg-card shadow-sm">
                @if ($orders->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[880px] border-collapse text-sm">
                            <thead>
                                <tr class="bg-[#5CBF8E] text-[#0A2A1A]">
                                    <th class="{{ $th }} w-12">
                                        <input type="checkbox" id="check-all" checked
                                               class="h-4 w-4 cursor-pointer rounded border-[#3E9E77] accent-[#0F7A3D]"
                                               aria-label="Select every order">
                                    </th>
                                    <th class="{{ $th }}">Date</th>
                                    <th class="{{ $th }} text-left">Customer</th>
                                    <th class="{{ $th }} text-left">Product</th>
                                    <th class="{{ $th }}">Status</th>
                                    <th class="{{ $th }}">Order Value</th>
                                    <th class="{{ $th }}">Your Commission</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr data-row class="{{ $loop->even ? 'bg-[#EAF6EE]' : 'bg-white' }} transition">
                                        <td class="{{ $td }}">
                                            <input type="checkbox" name="orders[]" value="{{ $order->id }}" checked data-pick
                                                   data-commission="{{ (float) $order->user_commission_total }}"
                                                   data-order-value="{{ (float) $order->total_price }}"
                                                   class="h-4 w-4 cursor-pointer rounded border-line accent-[#0F7A3D]"
                                                   aria-label="Include order {{ $order->id }}">
                                        </td>
                                        <td class="{{ $td }} whitespace-nowrap">{{ $order->submittedAt()->format('n/j/Y') }}</td>
                                        <td class="{{ $td }} text-left font-semibold text-ink">{{ $order->full_name }}</td>
                                        <td class="{{ $td }} whitespace-nowrap text-left">{{ $order->product?->name ?? '—' }}</td>
                                        <td class="border border-[#CBDDD3] px-3 py-2.5 text-center text-xs font-bold whitespace-nowrap {{ $order->sheetStatusClasses() }}">
                                            {{ $order->customerStatusLabel() }}
                                        </td>
                                        <td class="{{ $td }} whitespace-nowrap tabular-nums">${{ number_format($order->total_price, 2) }}</td>
                                        <td class="{{ $td }} whitespace-nowrap font-bold tabular-nums text-success">${{ number_format($order->user_commission_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-[#DCEFE4] font-bold text-ink">
                                    <td class="{{ $td }} text-left" colspan="5">Selected total</td>
                                    <td class="{{ $td }} whitespace-nowrap tabular-nums" data-value>$0.00</td>
                                    <td class="{{ $td }} whitespace-nowrap tabular-nums text-success" data-total>$0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="border-t border-line p-4 sm:p-5">
                        <label for="note" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-muted">
                            Note <span class="normal-case tracking-normal">(optional)</span>
                        </label>
                        <textarea name="note" id="note" rows="2" maxlength="1000"
                                  placeholder="Anything the team should know about this claim"
                                  class="{{ $input }}">{{ old('note') }}</textarea>
                        @error('note')
                            <p class="mt-1 text-xs font-medium text-danger">{{ $message }}</p>
                        @enderror

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                            <p class="text-xs text-muted">
                                Claiming <span class="font-semibold text-ink" data-total>$0.00</span>
                                across <span class="font-semibold text-ink" data-count>0</span>
                                <span data-count-word>orders</span>. Once sent, these cannot be claimed for again.
                            </p>
                            <button type="submit" data-submit
                                    class="rounded-xl bg-gradient-to-r from-brand to-brand2 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40">
                                Generate invoice
                            </button>
                        </div>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <p class="text-sm font-medium text-ink">Nothing to claim for in {{ $rangeLabel }}.</p>
                        <p class="mt-1 text-xs text-muted">
                            An order appears here once it reaches Sale or Active Account and has not been invoiced yet.
                        </p>
                        @if ($activeFilterCount > 0)
                            <a href="{{ route('invoices.index') }}" class="mt-3 inline-block text-sm font-medium text-brand hover:underline">Clear filters</a>
                        @endif
                    </div>
                @endif
            </div>
        </form>
    </section>

    {{-- ── Invoices already raised ──────────────────────────────────── --}}
    <section class="rise" style="--delay: 120ms">
        <h2 class="mb-3 flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-muted">
            <span class="grid h-6 w-6 place-items-center rounded-lg bg-brand/10 text-xs font-extrabold text-brand">2</span>
            Your invoices
        </h2>

        <div class="mb-3 grid gap-3 sm:grid-cols-3">
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

        <div class="overflow-hidden rounded-2xl border border-line bg-card shadow-sm">
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
                                    @if ($invoice->share_token)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-info/10 px-2.5 py-0.5 text-xs font-semibold text-info">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                            </svg>
                                            Shared
                                        </span>
                                    @endif
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
                                <p class="text-xl font-extrabold tracking-tight text-brand">${{ number_format($invoice->amount, 2) }}</p>
                                @if ($invoice->admin_note)
                                    <p class="mt-0.5 max-w-[16rem] truncate text-xs text-muted">Reply: {{ $invoice->admin_note }}</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <p class="text-sm font-medium text-ink">You have not sent an invoice yet.</p>
                    <p class="mt-1 text-xs text-muted">Tick the orders above and generate your first one.</p>
                </div>
            @endif
        </div>

        @if ($invoices->hasPages())
            <div class="mt-4">{{ $invoices->links('vendor.pagination.admin') }}</div>
        @endif
    </section>
@endsection

@push('scripts')
<script>
    (function () {
        const filterForm = document.getElementById('filter-form');
        const period = document.getElementById('period');
        const custom = document.getElementById('custom-range');

        period.addEventListener('change', function () {
            const isCustom = period.value === 'custom';
            custom.classList.toggle('hidden', !isCustom);
            custom.classList.toggle('grid', isCustom);

            // A preset needs no dates, so apply it straight away.
            if (!isCustom) {
                filterForm.submit();
            }
        });

        ['status', 'product_id', 'sort'].forEach(function (id) {
            document.getElementById(id).addEventListener('change', function () {
                filterForm.submit();
            });
        });

        const picks = Array.from(document.querySelectorAll('[data-pick]'));

        if (!picks.length) {
            return;
        }

        const checkAll = document.getElementById('check-all');
        const submit = document.querySelector('[data-submit]');
        const money = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });

        function retally() {
            const chosen = picks.filter(function (pick) { return pick.checked; });

            const commission = chosen.reduce(function (sum, p) { return sum + parseFloat(p.dataset.commission || 0); }, 0);
            const value = chosen.reduce(function (sum, p) { return sum + parseFloat(p.dataset.orderValue || 0); }, 0);

            document.querySelectorAll('[data-total]').forEach(function (el) { el.textContent = money.format(commission); });
            document.querySelectorAll('[data-value]').forEach(function (el) { el.textContent = money.format(value); });
            document.querySelectorAll('[data-count]').forEach(function (el) { el.textContent = chosen.length; });
            document.querySelectorAll('[data-count-word]').forEach(function (el) {
                el.textContent = chosen.length === 1 ? 'order' : 'orders';
            });

            // Nothing ticked is nothing to invoice.
            submit.disabled = chosen.length === 0;

            checkAll.checked = chosen.length === picks.length;
            checkAll.indeterminate = chosen.length > 0 && chosen.length < picks.length;

            picks.forEach(function (pick) {
                pick.closest('[data-row]').classList.toggle('opacity-40', !pick.checked);
            });
        }

        picks.forEach(function (pick) { pick.addEventListener('change', retally); });

        checkAll.addEventListener('change', function () {
            picks.forEach(function (pick) { pick.checked = checkAll.checked; });
            retally();
        });

        retally();
    })();
</script>
@endpush
