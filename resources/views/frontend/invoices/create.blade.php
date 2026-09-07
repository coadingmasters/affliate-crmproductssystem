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

    {{-- Pick the period --}}
    <form method="GET" action="{{ route('invoices.create') }}" id="period-form"
          class="rise mb-4 rounded-2xl border border-line bg-card p-4 shadow-sm sm:p-5">

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div>
                <label for="period" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-muted">Claim for</label>
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

    {{-- What that period is worth --}}
    <div class="rise mb-4 grid gap-3 sm:grid-cols-3" style="--delay: 60ms">
        <div class="rounded-2xl border border-line bg-card p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-muted">Period</p>
            <p class="mt-1 text-lg font-bold text-ink">{{ $rangeLabel }}</p>
        </div>
        <div class="rounded-2xl border border-line bg-card p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-muted">Orders</p>
            <p class="mt-1 text-2xl font-bold text-ink">{{ $orders->count() }}</p>
        </div>
        <div class="rounded-2xl border border-brand/30 bg-brand/5 p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-muted">Your earnings</p>
            <p class="mt-1 text-2xl font-bold text-brand">${{ number_format($earnings, 2) }}</p>
        </div>
    </div>

    {{-- The orders being claimed for --}}
    <div class="rise overflow-hidden rounded-2xl border border-line bg-card shadow-sm" style="--delay: 120ms">
        @if ($orders->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] border-collapse text-sm">
                    <thead>
                        <tr class="bg-[#5CBF8E] text-[#0A2A1A]">
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
                            <tr class="{{ $loop->even ? 'bg-[#EAF6EE]' : 'bg-white' }}">
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
                            <td class="{{ $td }} text-left" colspan="4">Total for {{ $rangeLabel }}</td>
                            <td class="{{ $td }} whitespace-nowrap tabular-nums">${{ number_format($orderValue, 2) }}</td>
                            <td class="{{ $td }} whitespace-nowrap tabular-nums text-success">${{ number_format($earnings, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Send it --}}
            <form method="POST" action="{{ route('invoices.store') }}" class="border-t border-line p-4 sm:p-5">
                @csrf
                <input type="hidden" name="period" value="{{ $period }}">
                <input type="hidden" name="from" value="{{ $from }}">
                <input type="hidden" name="to" value="{{ $to }}">

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
                        Claiming <span class="font-semibold text-ink">${{ number_format($earnings, 2) }}</span>
                        across {{ $orders->count() }} {{ Str::plural('order', $orders->count()) }}.
                        Once sent, these orders cannot be claimed for again.
                    </p>
                    <button type="submit"
                            class="rounded-xl bg-gradient-to-r from-brand to-brand2 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:opacity-90">
                        Send invoice
                    </button>
                </div>
            </form>
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
@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('period-form');
        const period = document.getElementById('period');
        const fromWrap = document.getElementById('from-wrap');
        const toWrap = document.getElementById('to-wrap');

        period.addEventListener('change', function () {
            const isCustom = period.value === 'custom';
            fromWrap.classList.toggle('hidden', !isCustom);
            toWrap.classList.toggle('hidden', !isCustom);

            // A preset needs no dates, so show it straight away.
            if (!isCustom) {
                form.submit();
            }
        });
    })();
</script>
@endpush
