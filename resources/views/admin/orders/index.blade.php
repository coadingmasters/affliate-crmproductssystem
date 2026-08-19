@extends('layouts.admin')

@section('title', 'Orders · Med Alert')
@section('heading', 'Orders')

@section('content')
    @php
        $input = 'w-full rounded-xl border border-line bg-elevated px-3.5 py-2.5 text-sm text-ink placeholder-muted transition focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30';
    @endphp

    {{-- Totals for the current filter, not the whole table --}}
    <div class="rise mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-line bg-card p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-muted">Orders</p>
            <p class="mt-1 text-2xl font-bold text-ink">{{ number_format($totalOrders) }}</p>
        </div>
        <div class="rounded-2xl border border-line bg-card p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-muted">Value</p>
            <p class="mt-1 text-2xl font-bold text-accent">${{ number_format($totalRevenue, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-line bg-card p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-muted">User Commission</p>
            <p class="mt-1 text-2xl font-bold text-success">${{ number_format($totalUserCommission, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-line bg-card p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-muted">Admin Commission</p>
            <p class="mt-1 text-2xl font-bold text-info">${{ number_format($totalAdminCommission, 2) }}</p>
        </div>
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('admin.orders.index') }}" id="filter-form"
          class="rise mb-4 rounded-2xl border border-line bg-card p-4 sm:p-5" style="--delay: 60ms">

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-6">
            <div class="sm:col-span-2 xl:col-span-1">
                <label for="q" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-muted">Search</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                    </svg>
                    <input type="search" name="q" id="q" value="{{ $filters['q'] }}"
                           placeholder="Customer, account, phone or #id"
                           class="{{ $input }} pl-9">
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
                <label for="product_id" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-muted">Product</label>
                <select name="product_id" id="product_id" class="{{ $input }}">
                    <option value="">All products</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected($filters['product_id'] === $product->id)>{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="user_id" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-muted">Submitted by</label>
                <select name="user_id" id="user_id" class="{{ $input }}">
                    <option value="">All users</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected($filters['user_id'] === $customer->id)>{{ $customer->name }}</option>
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
                <label for="sort" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-muted">Sort by</label>
                <select name="sort" id="sort" class="{{ $input }}">
                    @foreach ($sorts as $value => $label)
                        <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Only relevant when "Custom range" is picked --}}
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
                    class="rounded-xl bg-gradient-to-r from-accent to-accent2 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-accent/25 transition hover:opacity-90">
                Apply filters
            </button>

            @if ($activeFilterCount > 0)
                <a href="{{ route('admin.orders.index') }}"
                   class="rounded-xl border border-line px-4 py-2.5 text-sm font-medium text-muted transition hover:border-danger hover:text-danger">
                    Clear ({{ $activeFilterCount }})
                </a>
            @endif

            <div class="ml-auto flex items-center gap-2">
                <label for="per_page" class="text-xs text-muted">Per page</label>
                <select name="per_page" id="per_page"
                        class="rounded-lg border border-line bg-elevated px-2.5 py-1.5 text-sm text-ink focus:border-accent focus:outline-none">
                    @foreach ($perPageOptions as $option)
                        <option value="{{ $option }}" @selected($filters['per_page'] === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    {{-- Results --}}
    <div class="rise overflow-hidden rounded-2xl border border-line bg-card" style="--delay: 120ms">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-elevated text-xs uppercase tracking-wider text-muted">
                    <tr>
                        <th class="px-4 py-3.5 font-medium">ID</th>
                        <th class="px-4 py-3.5 font-medium">Submitted By</th>
                        <th class="px-4 py-3.5 font-medium">Full Name</th>
                        <th class="px-4 py-3.5 font-medium">Email</th>
                        <th class="px-4 py-3.5 font-medium">Phone</th>
                        <th class="px-4 py-3.5 font-medium">Product</th>
                        <th class="px-4 py-3.5 font-medium">Price Label</th>
                        <th class="px-4 py-3.5 font-medium">Qty</th>
                        <th class="px-4 py-3.5 font-medium">Total</th>
                        <th class="px-4 py-3.5 font-medium">User Commission</th>
                        <th class="px-4 py-3.5 font-medium">Admin Commission</th>
                        <th class="px-4 py-3.5 font-medium">Status</th>
                        <th class="px-4 py-3.5 font-medium">Submitted</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($orders as $order)
                        <tr class="row-hover cursor-pointer hover:bg-elevated"
                            onclick="window.location='{{ route('admin.orders.show', $order) }}'">
                            <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-accent">#{{ $order->id }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                @if ($order->user)
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-accent/10 text-[11px] font-bold text-accent">
                                            {{ Str::upper(Str::substr($order->user->name, 0, 1)) }}
                                        </span>
                                        <span>
                                            <span class="block font-medium text-ink">{{ $order->user->name }}</span>
                                            <span class="block text-xs text-muted">{{ $order->user->email }}</span>
                                        </span>
                                    </div>
                                @else
                                    <span class="text-xs text-muted">Account removed</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-medium text-ink">{{ $order->full_name }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-muted">{{ $order->email }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-muted">{{ $order->phone }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-muted">{{ $order->product?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-muted">{{ $order->productPrice?->label ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-muted">{{ $order->quantity }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-ink">${{ number_format($order->total_price, 2) }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-success">${{ number_format($order->user_commission_total, 2) }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-info">${{ number_format($order->admin_commission_total, 2) }}</td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $order->statusClasses() }}">
                                    {{ $order->statusLabel() }}
                                </span>
                                @if ($order->post_date)
                                    <span class="mt-1 block whitespace-nowrap text-xs font-medium text-info">
                                        Pays {{ $order->postDateLabel() }}
                                    </span>
                                @elseif ($order->statusChangedAt())
                                    <span class="mt-1 block whitespace-nowrap text-xs text-muted">
                                        {{ $order->statusChangedAt()->format('M j, g:i A') }}
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="block text-ink">{{ $order->submittedAt()->format('M j, Y') }}</span>
                                <span class="block text-xs text-muted">
                                    {{ $order->submittedAt()->format('g:i A') }}
                                    &middot; {{ $order->submittedAt()->diffForHumans() }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="px-4 py-14 text-center">
                                <p class="text-muted">No orders match these filters.</p>
                                @if ($activeFilterCount > 0)
                                    <a href="{{ route('admin.orders.index') }}" class="mt-2 inline-block text-sm font-medium text-accent hover:underline">
                                        Clear filters
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
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
        const customRange = document.getElementById('custom-range');

        // Show the date inputs only for a custom range.
        period.addEventListener('change', function () {
            const custom = period.value === 'custom';
            customRange.classList.toggle('hidden', !custom);
            customRange.classList.toggle('grid', custom);

            if (!custom) {
                form.submit();
            }
        });

        // These apply immediately; the search box waits for Apply.
        ['status', 'product_id', 'user_id', 'sort', 'per_page'].forEach(function (id) {
            document.getElementById(id).addEventListener('change', function () {
                form.submit();
            });
        });
    })();
</script>
@endpush
