@extends('layouts.customer')

@section('title', 'All Orders · Med Alert')
@section('heading', 'All Orders')

@php
    $input = 'w-full rounded-xl border border-line bg-elevated px-3.5 py-2.5 text-sm text-ink placeholder-muted transition focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/25';
@endphp

@section('content')

    {{-- Totals for the current filter --}}
    <div class="rise mb-4 grid grid-cols-2 gap-3">
        <div class="rounded-2xl border border-line bg-card p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-muted">Orders</p>
            <p class="mt-1 text-2xl font-bold text-ink">{{ number_format($totalOrders) }}</p>
        </div>
        <div class="rounded-2xl border border-line bg-card p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-muted">Value</p>
            <p class="mt-1 text-2xl font-bold text-brand">${{ number_format($totalValue, 2) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('order.list') }}" id="filter-form"
          class="rise mb-4 rounded-2xl border border-line bg-card p-4 shadow-sm sm:p-5" style="--delay: 60ms">

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
                    <option value="all">All statuses</option>
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
            <button type="submit"
                    class="rounded-xl bg-gradient-to-r from-brand to-brand2 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:opacity-90">
                Apply filters
            </button>

            @if ($activeFilterCount > 0)
                <a href="{{ route('order.list') }}"
                   class="rounded-xl border border-line px-4 py-2.5 text-sm font-medium text-muted transition hover:border-danger hover:text-danger">
                    Clear ({{ $activeFilterCount }})
                </a>
            @endif

            <div class="ml-auto flex items-center gap-2">
                <label for="per_page" class="text-xs text-muted">Per page</label>
                <select name="per_page" id="per_page"
                        class="rounded-lg border border-line bg-elevated px-2.5 py-1.5 text-sm text-ink focus:border-brand focus:outline-none">
                    @foreach ($perPageOptions as $option)
                        <option value="{{ $option }}" @selected($filters['per_page'] === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    {{-- Orders --}}
    <div class="rise space-y-3" style="--delay: 120ms">
        @forelse ($orders as $order)
            <a href="{{ route('order.show', $order) }}"
               class="block rounded-2xl border border-line bg-card p-4 shadow-sm transition hover:border-brand/40 hover:shadow-md sm:p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="text-sm font-bold text-ink">Order #{{ $order->id }}</span>
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $order->statusClasses() }}">
                                {{ $order->customerStatusLabel() }}
                            </span>

                            @if ($order->invoice)
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $order->invoice->statusClasses() }}">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Invoice {{ $order->invoice->statusLabel() }}
                                </span>
                            @endif

                            @if ($order->hasVoiceNote())
                                <span class="inline-flex items-center gap-1 rounded-full bg-brand/10 px-2.5 py-0.5 text-xs font-semibold text-brand">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-14 0m7 7v3m0-6a4 4 0 01-4-4V6a4 4 0 118 0v5a4 4 0 01-4 4z"/>
                                    </svg>
                                    Voice note
                                </span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-muted">Submitted {{ $order->submittedAtLabel() }}</p>
                        @if ($order->statusDateValue())
                            <p class="mt-0.5 text-xs font-semibold text-brand">
                                {{ $order->statusDateLabel() }}: {{ $order->statusDateValue() }}
                            </p>
                        @endif
                    </div>

                    <div class="text-right">
                        <p class="text-xl font-extrabold tracking-tight text-brand">${{ number_format($order->total_price, 2) }}</p>
                        <p class="mt-0.5 text-xs text-muted">
                            {{ $order->product?->name ?? '—' }} &middot; {{ $order->productPrice?->label ?? '—' }}
                        </p>
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-between border-t border-line pt-3">
                    <p class="truncate text-xs text-muted">{{ $order->address }}</p>
                    <span class="ml-3 inline-flex shrink-0 items-center gap-1 text-xs font-semibold text-brand">
                        View
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-line bg-card px-6 py-14 text-center shadow-sm">
                <p class="text-sm font-medium text-ink">No orders match these filters.</p>
                @if ($activeFilterCount > 0)
                    <a href="{{ route('order.list') }}" class="mt-2 inline-block text-sm font-medium text-brand hover:underline">Clear filters</a>
                @else
                    <a href="{{ route('order.create') }}" class="mt-2 inline-block text-sm font-medium text-brand hover:underline">Place your first order</a>
                @endif
            </div>
        @endforelse
    </div>

    @if ($orders->total() > 0)
        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-muted">
                Showing <span class="font-semibold text-ink">{{ $orders->firstItem() }}</span>
                to <span class="font-semibold text-ink">{{ $orders->lastItem() }}</span>
                of <span class="font-semibold text-ink">{{ number_format($orders->total()) }}</span>
                {{ Str::plural('order', $orders->total()) }}
            </p>

            @if ($orders->hasPages())
                <div>{{ $orders->links('vendor.pagination.admin') }}</div>
            @endif
        </div>
    @endif
@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('filter-form');
        const period = document.getElementById('period');
        const custom = document.getElementById('custom-range');

        period.addEventListener('change', function () {
            const isCustom = period.value === 'custom';
            custom.classList.toggle('hidden', !isCustom);
            custom.classList.toggle('grid', isCustom);

            if (!isCustom) {
                form.submit();
            }
        });

        ['status', 'product_id', 'sort', 'per_page'].forEach(function (id) {
            document.getElementById(id).addEventListener('change', function () {
                form.submit();
            });
        });
    })();
</script>
@endpush
