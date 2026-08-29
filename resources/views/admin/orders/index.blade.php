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
          class="rise relative z-20 mb-4 rounded-2xl border border-line bg-card p-4 sm:p-5" style="--delay: 60ms">

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

            {{-- Several accounts can be selected at once --}}
            <div class="relative" id="user-picker">
                <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-muted">Submitted by</label>

                <button type="button" id="user-picker-toggle"
                        class="{{ $input }} flex items-center justify-between gap-2 text-left"
                        aria-haspopup="true" aria-expanded="false">
                    <span id="user-picker-label" class="truncate">
                        @if (count($filters['user_ids']) === 0)
                            All users
                        @elseif (count($filters['user_ids']) === 1)
                            {{ $customers->firstWhere('id', $filters['user_ids'][0])?->name ?? '1 user' }}
                        @else
                            {{ count($filters['user_ids']) }} users selected
                        @endif
                    </span>
                    <svg class="h-4 w-4 shrink-0 text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div id="user-picker-panel"
                     class="absolute left-0 right-0 z-40 mt-1 hidden overflow-hidden rounded-xl border border-line bg-card shadow-2xl">
                    <div class="border-b border-line p-2">
                        <input type="search" id="user-picker-search" placeholder="Search accounts"
                               class="w-full rounded-lg border border-line bg-elevated px-3 py-2 text-sm text-ink placeholder-muted focus:border-accent focus:outline-none">
                    </div>

                    <div class="max-h-56 overflow-y-auto overscroll-contain p-1">
                        @forelse ($customers as $customer)
                            <label class="user-option flex cursor-pointer items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm transition hover:bg-elevated"
                                   data-name="{{ Str::lower($customer->name.' '.$customer->email) }}">
                                <input type="checkbox" name="user_ids[]" value="{{ $customer->id }}"
                                       @checked(in_array($customer->id, $filters['user_ids'], true))
                                       class="user-checkbox h-4 w-4 rounded border-line text-accent focus:ring-accent/30">
                                <span class="min-w-0">
                                    <span class="block truncate font-medium text-ink">{{ $customer->name }}</span>
                                    <span class="block truncate text-[11px] text-muted">{{ $customer->email }}</span>
                                </span>
                            </label>
                        @empty
                            <p class="px-2.5 py-4 text-center text-xs text-muted">No customer accounts yet.</p>
                        @endforelse

                        <p id="user-picker-empty" class="hidden px-2.5 py-4 text-center text-xs text-muted">No accounts match.</p>
                    </div>

                    <div class="flex items-center justify-between gap-2 border-t border-line bg-elevated px-2.5 py-2">
                        <button type="button" id="user-picker-clear"
                                class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-muted transition hover:text-danger">
                            Clear
                        </button>
                        <button type="button" id="user-picker-apply"
                                class="rounded-lg bg-gradient-to-r from-accent to-accent2 px-3 py-1.5 text-xs font-semibold text-white transition hover:opacity-90">
                            Apply
                        </button>
                    </div>
                </div>
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
    <div class="rise relative z-0 overflow-hidden rounded-2xl border border-line bg-card" style="--delay: 120ms">
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
                        <th class="px-4 py-3.5 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($orders as $order)
                        <tr id="order-row-{{ $order->id }}"
                            class="row-hover cursor-pointer transition-all duration-300 hover:bg-elevated"
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
                            <td class="px-4 py-3.5" onclick="event.stopPropagation()">
                                <div class="status-cell" data-order="{{ $order->id }}">
                                    <div class="relative inline-block">
                                        <select class="status-select w-full min-w-[9.5rem] cursor-pointer appearance-none rounded-full border-0 py-1 pl-2.5 pr-7 text-xs font-medium transition focus:outline-none focus:ring-2 focus:ring-accent/40 {{ $order->statusClasses() }}"
                                                data-current="{{ $order->status }}"
                                                aria-label="Change status of order #{{ $order->id }}">
                                            @foreach ($statusMeta as $value => $meta)
                                                <option value="{{ $value }}" @selected($order->status === $value)>{{ $meta['label'] }}</option>
                                            @endforeach
                                        </select>
                                        <svg class="pointer-events-none absolute right-2 top-1/2 h-3 w-3 -translate-y-1/2 opacity-70" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>

                                    <span class="status-meta mt-1 block whitespace-nowrap text-xs {{ $order->statusDateValue() ? 'font-medium text-info' : 'text-muted' }}">
                                        @if ($order->statusDateValue())
                                            {{ $order->statusDateLabel() }}: {{ $order->statusDateValue() }}
                                        @elseif ($order->statusChangedAt())
                                            {{ $order->statusChangedAt()->format('M j, g:i A') }}
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="block text-ink">{{ $order->submittedAt()->format('M j, Y') }}</span>
                                <span class="block text-xs text-muted">
                                    {{ $order->submittedAt()->format('g:i A') }}
                                    &middot; {{ $order->submittedAt()->diffForHumans() }}
                                </span>
                            </td>

                            <td class="px-4 py-3.5" onclick="event.stopPropagation()">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.orders.edit', $order) }}" title="Edit order"
                                       class="rounded-lg border border-line p-1.5 text-muted transition hover:border-accent hover:bg-accent/10 hover:text-accent">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.4-9.6a2 2 0 112.8 2.8L12 16l-4 1 1-4 9.6-9.6z"/>
                                        </svg>
                                    </a>

                                    <button type="button" title="Move to trash"
                                            class="trash-btn rounded-lg border border-line p-1.5 text-muted transition hover:border-danger hover:bg-danger/10 hover:text-danger"
                                            data-order="{{ $order->id }}" data-name="{{ $order->full_name }}">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.87 12.14A2 2 0 0116.14 21H7.86a2 2 0 01-1.99-1.86L5 7m5 4v6m4-6v6M9 7V4h6v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="px-4 py-14 text-center">
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

{{-- Asks for the date when a status needs one --}}
<div id="date-modal" class="fixed inset-0 z-[110] hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div id="date-modal-backdrop" class="absolute inset-0 bg-ink/50 opacity-0 backdrop-blur-sm transition-opacity duration-200"></div>

    <div id="date-modal-card"
         class="relative w-full max-w-sm scale-95 overflow-hidden rounded-2xl border border-line bg-card opacity-0 shadow-2xl transition-all duration-200">
        <div class="p-5 sm:p-6">
            <div class="mb-4 flex items-start gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-info/10 text-info">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </span>
                <div class="min-w-0 pt-0.5">
                    <h2 id="date-modal-title" class="text-base font-semibold text-ink">Choose a date</h2>
                    <p id="date-modal-help" class="mt-1 text-sm text-muted"></p>
                </div>
            </div>

            <input type="date" id="date-modal-input"
                   class="w-full rounded-xl border border-line bg-elevated px-3.5 py-2.5 text-sm text-ink focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30">
            <p id="date-modal-error" class="mt-1.5 hidden text-sm text-danger"></p>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-line bg-elevated px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
            <button type="button" id="date-modal-cancel"
                    class="w-full rounded-xl border border-line bg-card px-4 py-2.5 text-sm font-medium text-ink transition hover:bg-elevated sm:w-auto">
                Cancel
            </button>
            <button type="button" id="date-modal-save"
                    class="w-full rounded-xl bg-gradient-to-r from-accent to-accent2 px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90 sm:w-auto">
                Save
            </button>
        </div>
    </div>
</div>

{{-- Brief confirmation after a live status change --}}
<div id="toast" class="pointer-events-none fixed bottom-5 left-1/2 z-[120] hidden -translate-x-1/2">
    <div id="toast-body" class="rounded-xl border border-line bg-card px-4 py-2.5 text-sm font-medium text-ink shadow-2xl"></div>
</div>
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
        ['status', 'product_id', 'sort', 'per_page'].forEach(function (id) {
            document.getElementById(id).addEventListener('change', function () {
                form.submit();
            });
        });
    })();


    /* ---------------- multi select for accounts ---------------- */
    (function () {
        const wrap = document.getElementById('user-picker');

        if (!wrap) {
            return;
        }

        const toggle = document.getElementById('user-picker-toggle');
        const panel = document.getElementById('user-picker-panel');
        const label = document.getElementById('user-picker-label');
        const search = document.getElementById('user-picker-search');
        const empty = document.getElementById('user-picker-empty');
        const options = Array.from(wrap.querySelectorAll('.user-option'));
        const boxes = Array.from(wrap.querySelectorAll('.user-checkbox'));
        const form = document.getElementById('filter-form');

        function describe() {
            const chosen = boxes.filter(b => b.checked);

            if (chosen.length === 0) {
                label.textContent = 'All users';
            } else if (chosen.length === 1) {
                label.textContent = chosen[0].closest('.user-option').querySelector('span span').textContent.trim();
            } else {
                label.textContent = chosen.length + ' users selected';
            }
        }

        function open() {
            panel.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
            search.value = '';
            options.forEach(o => o.classList.remove('hidden'));
            empty.classList.add('hidden');
            search.focus();
        }

        function close() {
            panel.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
        }

        toggle.addEventListener('click', function () {
            panel.classList.contains('hidden') ? open() : close();
        });

        document.addEventListener('click', function (event) {
            if (!wrap.contains(event.target)) {
                close();
            }
        });

        search.addEventListener('input', function () {
            const term = search.value.trim().toLowerCase();
            let visible = 0;

            options.forEach(function (option) {
                const match = !term || option.dataset.name.includes(term);
                option.classList.toggle('hidden', !match);
                if (match) visible++;
            });

            empty.classList.toggle('hidden', visible > 0);
        });

        boxes.forEach(box => box.addEventListener('change', describe));

        document.getElementById('user-picker-clear').addEventListener('click', function () {
            boxes.forEach(b => { b.checked = false; });
            describe();
            form.submit();
        });

        document.getElementById('user-picker-apply').addEventListener('click', function () {
            form.submit();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !panel.classList.contains('hidden')) {
                close();
            }
        });

        describe();
    })();


    /* ---------------- move an order to the trash ---------------- */
    (function () {
        const endpoint = @json(route('admin.orders.destroy', ['order' => '__ORDER__']));
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

        document.querySelectorAll('.trash-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                const id = button.dataset.order;

                Modal.confirm({
                    title: 'Move to trash',
                    message: 'Order #' + id + ' from ' + button.dataset.name
                        + ' will be moved to the trash. You can restore it from there.',
                    confirmText: 'Move to trash',
                    onConfirm: async function () {
                        const row = document.getElementById('order-row-' + id);

                        try {
                            const response = await fetch(endpoint.replace('__ORDER__', id), {
                                method: 'DELETE',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf,
                                },
                            });

                            const payload = await response.json();

                            if (!response.ok) {
                                throw new Error(payload.message || 'Could not delete the order.');
                            }

                            // Slide it away towards the sidebar, then drop it.
                            row.style.transition = 'opacity .35s ease, transform .35s ease';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(-2rem) scale(.98)';

                            setTimeout(function () {
                                row.remove();
                                showToast(payload.message, false);
                                bumpTrashBadge(payload.trashed);
                            }, 350);
                        } catch (error) {
                            showToast(error.message, true);
                        }
                    },
                });
            });
        });

        // Keep the sidebar count honest without a reload.
        function bumpTrashBadge(count) {
            const link = document.querySelector('a[href$="/admin/orders/trash"]');

            if (!link) {
                return;
            }

            let badge = link.querySelector('span.rounded-full');

            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'rounded-full bg-gradient-to-r from-accent to-accent2 px-2 py-0.5 text-[11px] font-semibold text-white shadow-sm shadow-accent/30';
                link.appendChild(badge);
            }

            badge.textContent = count;
            badge.animate(
                [{ transform: 'scale(1)' }, { transform: 'scale(1.35)' }, { transform: 'scale(1)' }],
                { duration: 400, easing: 'ease-out' },
            );
        }
    })();

    /* ---------------- inline status editing ---------------- */
    (function () {
        const STATUS_DATES = @json(App\Models\Order::STATUS_DATES);
        const endpoint = @json(route('admin.orders.status', ['order' => '__ORDER__']));
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        const modal = document.getElementById('date-modal');
        const backdrop = document.getElementById('date-modal-backdrop');
        const card = document.getElementById('date-modal-card');
        const title = document.getElementById('date-modal-title');
        const help = document.getElementById('date-modal-help');
        const dateInput = document.getElementById('date-modal-input');
        const dateError = document.getElementById('date-modal-error');
        const cancelBtn = document.getElementById('date-modal-cancel');
        const saveBtn = document.getElementById('date-modal-save');

        const toast = document.getElementById('toast');
        const toastBody = document.getElementById('toast-body');

        let pending = null;

        function showToast(message, isError) {
            toastBody.textContent = message;
            toastBody.className = 'rounded-xl border px-4 py-2.5 text-sm font-medium shadow-2xl '
                + (isError ? 'border-danger/30 bg-danger/10 text-danger' : 'border-success/30 bg-success/10 text-success');
            toast.classList.remove('hidden');
            clearTimeout(toast.dataset.timer);
            toast.dataset.timer = setTimeout(() => toast.classList.add('hidden'), 2600);
        }

        function openModal(meta) {
            title.textContent = meta.label;
            help.textContent = 'Set ' + meta.help + '.';
            dateInput.value = new Date().toISOString().slice(0, 10);
            dateError.classList.add('hidden');

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            requestAnimationFrame(function () {
                backdrop.classList.add('opacity-100');
                card.classList.remove('scale-95', 'opacity-0');
            });

            dateInput.focus();
        }

        function closeModal() {
            backdrop.classList.remove('opacity-100');
            card.classList.add('scale-95', 'opacity-0');

            setTimeout(function () {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        function revert() {
            if (pending) {
                pending.select.value = pending.previous;
                paint(pending.select);
            }
            pending = null;
        }

        // Keep the pill's colour in step with the chosen option.
        function paint(select) {
            const cell = select.closest('.status-cell');
            const classes = select.dataset.classes;
            if (classes) {
                select.className = select.className.replace(/bg-\S+\/10|text-(warning|info|success|danger|brand|muted|accent)/g, '').trim()
                    + ' ' + classes;
            }
            return cell;
        }

        async function send(select, status, dateValue) {
            const cell = select.closest('.status-cell');
            const orderId = cell.dataset.order;
            const body = { status: status };

            if (STATUS_DATES[status]) {
                body[STATUS_DATES[status].column] = dateValue;
            }

            select.disabled = true;

            try {
                const response = await fetch(endpoint.replace('__ORDER__', orderId), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify(body),
                });

                const payload = await response.json();

                if (!response.ok) {
                    const first = payload.errors ? Object.values(payload.errors).flat()[0] : payload.message;
                    throw new Error(first || 'Could not update the status.');
                }

                select.dataset.classes = payload.classes;
                select.dataset.current = payload.status;
                paint(select);

                const meta = cell.querySelector('.status-meta');
                if (payload.date_value) {
                    meta.textContent = payload.date_label + ': ' + payload.date_value;
                    meta.className = 'status-meta mt-1 block whitespace-nowrap text-xs font-medium text-info';
                } else {
                    meta.textContent = payload.changed_at || '';
                    meta.className = 'status-meta mt-1 block whitespace-nowrap text-xs text-muted';
                }

                showToast(payload.message, false);
                pending = null;
            } catch (error) {
                showToast(error.message, true);
                revert();
            } finally {
                select.disabled = false;
            }
        }

        document.querySelectorAll('.status-select').forEach(function (select) {
            select.dataset.classes = select.className.match(/bg-\S+\/10\s+text-\S+/)?.[0] || '';

            select.addEventListener('change', function () {
                const status = select.value;
                const previous = select.dataset.current;

                if (status === previous) {
                    return;
                }

                if (STATUS_DATES[status]) {
                    pending = { select: select, status: status, previous: previous };
                    openModal(STATUS_DATES[status]);
                    return;
                }

                send(select, status, null);
            });
        });

        saveBtn.addEventListener('click', function () {
            if (!dateInput.value) {
                dateError.textContent = 'Pick a date to continue.';
                dateError.classList.remove('hidden');
                return;
            }

            const job = pending;
            closeModal();
            send(job.select, job.status, dateInput.value);
        });

        cancelBtn.addEventListener('click', function () {
            closeModal();
            revert();
        });

        backdrop.addEventListener('click', function () {
            closeModal();
            revert();
        });

        document.addEventListener('keydown', function (event) {
            if (modal.classList.contains('hidden')) return;
            if (event.key === 'Escape') { closeModal(); revert(); }
            if (event.key === 'Enter') saveBtn.click();
        });
    })();
</script>
@endpush
