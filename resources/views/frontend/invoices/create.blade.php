@extends('layouts.customer')

@section('title', 'New Invoice · Med Alert')
@section('heading', 'New Invoice')

@php
    $input = 'w-full rounded-xl border border-line bg-elevated px-3.5 py-2.5 text-sm text-ink placeholder-muted transition focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25';
    $th = 'border border-[#3E9E77] px-3 py-3 text-center text-xs font-bold uppercase tracking-wide';
    $td = 'border border-[#CBDDD3] px-3 py-2.5 text-center';
@endphp

@section('content')

    @if (session('error'))
        <div class="rise mb-4 rounded-2xl border border-danger/30 bg-danger/10 px-4 py-3 text-sm font-medium text-danger">
            {{ session('error') }}
        </div>
    @endif

    @error('orders')
        <div class="rise mb-4 rounded-2xl border border-danger/30 bg-danger/10 px-4 py-3 text-sm font-medium text-danger">
            {{ $message }}
        </div>
    @enderror

    {{-- Narrow the list down to a period. Nothing is claimed by picking one. --}}
    <form method="GET" action="{{ route('invoices.create') }}" id="period-form"
          class="rise mb-4 rounded-2xl border border-line bg-card p-4 shadow-sm sm:p-5">

        <p class="mb-3 text-xs text-muted">
            <span class="font-semibold text-ink">Step 1.</span> Narrow the list, then tick the orders you want on this invoice.
        </p>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div>
                <label for="period" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-muted">Show orders from</label>
                <select name="period" id="period" class="{{ $input }}">
                    @foreach ($periods as $value => $label)
                        <option value="{{ $value }}" @selected($period === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div id="from-wrap" class="{{ $period === 'custom' ? '' : 'hidden' }}">
                <label for="from" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-muted">From</label>
                <input type="date" name="from" id="from" value="{{ $from }}" class="{{ $input }}">
            </div>

            <div id="to-wrap" class="{{ $period === 'custom' ? '' : 'hidden' }}">
                <label for="to" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-muted">To</label>
                <input type="date" name="to" id="to" value="{{ $to }}" class="{{ $input }}">
            </div>
        </div>

        @if ($period === 'custom')
            <button type="submit" class="mt-3 rounded-xl border border-line px-4 py-2 text-sm font-medium text-muted transition hover:border-brand hover:text-brand">
                Show orders
            </button>
        @endif
    </form>

    {{-- What the current selection comes to --}}
    <div class="rise mb-4 grid gap-3 sm:grid-cols-3" style="--delay: 60ms">
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

    {{-- Pick the orders, then send --}}
    <form method="POST" action="{{ route('invoices.store') }}" id="claim-form">
        @csrf

        <div class="rise overflow-hidden rounded-2xl border border-line bg-card shadow-sm" style="--delay: 120ms">
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
                                        <input type="checkbox" name="orders[]" value="{{ $order->id }}" checked
                                               data-pick
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
                            <span data-count-word>orders</span>.
                            Once sent, these cannot be claimed for again.
                        </p>
                        <button type="submit" data-submit
                                class="rounded-xl bg-gradient-to-r from-brand to-brand2 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40">
                            Generate invoice
                        </button>
                    </div>
                </div>
            @else
                <div class="px-6 py-14 text-center">
                    <p class="text-sm font-medium text-ink">Nothing to claim for in {{ $rangeLabel }}.</p>
                    <p class="mt-1 text-xs text-muted">
                        An order appears here once it reaches Sale or Active Account and has not been invoiced yet.
                    </p>
                    <a href="{{ route('invoices.index') }}" class="mt-3 inline-block text-sm font-medium text-brand hover:underline">Back to invoices</a>
                </div>
            @endif
        </div>
    </form>
@endsection

@push('scripts')
<script>
    (function () {
        const periodForm = document.getElementById('period-form');
        const period = document.getElementById('period');
        const fromWrap = document.getElementById('from-wrap');
        const toWrap = document.getElementById('to-wrap');

        period.addEventListener('change', function () {
            const isCustom = period.value === 'custom';
            fromWrap.classList.toggle('hidden', !isCustom);
            toWrap.classList.toggle('hidden', !isCustom);

            // A preset needs no dates, so show its orders straight away.
            if (!isCustom) {
                periodForm.submit();
            }
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

            const commission = chosen.reduce(function (sum, pick) {
                return sum + parseFloat(pick.dataset.commission || 0);
            }, 0);

            const value = chosen.reduce(function (sum, pick) {
                return sum + parseFloat(pick.dataset.orderValue || 0);
            }, 0);

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
