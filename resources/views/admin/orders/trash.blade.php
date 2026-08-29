@extends('layouts.admin')

@section('title', 'Trash · Med Alert')
@section('heading', 'Trash')

@section('content')
    <div class="rise mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-start gap-3 rounded-2xl border border-warning/30 bg-warning/10 px-4 py-3">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-warning" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-ink">
                Deleted orders stay here until you remove them for good. Restoring puts one back exactly as it was.
            </p>
        </div>

        <form method="GET" action="{{ route('admin.orders.trash') }}" class="flex items-center gap-2">
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                </svg>
                <input type="search" name="q" value="{{ $search }}" placeholder="Search trash"
                       class="w-56 rounded-xl border border-line bg-elevated py-2.5 pl-9 pr-3.5 text-sm text-ink placeholder-muted focus:border-accent focus:outline-none">
            </div>
            <button type="submit" class="rounded-xl border border-line px-4 py-2.5 text-sm font-medium text-muted transition hover:border-accent hover:text-accent">
                Search
            </button>
        </form>
    </div>

    <div class="rise overflow-hidden rounded-2xl border border-line bg-card" style="--delay: 80ms">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-elevated text-xs uppercase tracking-wider text-muted">
                    <tr>
                        <th class="px-4 py-3.5 font-medium">ID</th>
                        <th class="px-4 py-3.5 font-medium">Customer</th>
                        <th class="px-4 py-3.5 font-medium">Product</th>
                        <th class="px-4 py-3.5 font-medium">Total</th>
                        <th class="px-4 py-3.5 font-medium">Status</th>
                        <th class="px-4 py-3.5 font-medium">Deleted</th>
                        <th class="px-4 py-3.5 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($orders as $order)
                        <tr class="row-hover hover:bg-elevated">
                            <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-muted">#{{ $order->id }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <p class="font-medium text-ink">{{ $order->full_name }}</p>
                                <p class="text-xs text-muted">{{ $order->email }}</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-muted">
                                {{ $order->product?->name ?? '—' }}
                                <span class="text-xs">({{ $order->productPrice?->label ?? '—' }})</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-ink">${{ number_format($order->total_price, 2) }}</td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $order->statusClasses() }}">
                                    {{ $order->statusLabel() }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="block text-ink">{{ $order->deleted_at?->timezone(config('app.display_timezone'))->format('M j, Y') }}</span>
                                <span class="block text-xs text-muted">{{ $order->deleted_at?->diffForHumans() }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.orders.restore', $order->id) }}">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-success/30 px-3 py-1.5 text-xs font-medium text-success transition hover:bg-success hover:text-white">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M20 20v-5h-5M20 9A8 8 0 006.3 5.3M4 15a8 8 0 0013.7 3.7"/>
                                            </svg>
                                            Restore
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.orders.force-delete', $order->id) }}"
                                          data-confirm-title="Delete permanently"
                                          data-confirm="Order #{{ $order->id }} will be erased for good, along with any voice note. This cannot be undone."
                                          data-confirm-text="Delete forever">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-danger/30 px-3 py-1.5 text-xs font-medium text-danger transition hover:bg-danger hover:text-white">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.87 12.14A2 2 0 0116.14 21H7.86a2 2 0 01-1.99-1.86L5 7m5 4v6m4-6v6M9 7V4h6v3M4 7h16"/>
                                            </svg>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <svg class="mx-auto mb-3 h-10 w-10 text-muted" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.87 12.14A2 2 0 0116.14 21H7.86a2 2 0 01-1.99-1.86L5 7m5 4v6m4-6v6M9 7V4h6v3M4 7h16"/>
                                </svg>
                                <p class="text-muted">{{ $search ? 'Nothing in the trash matches that search.' : 'The trash is empty.' }}</p>
                                <a href="{{ route('admin.orders.index') }}" class="mt-2 inline-block text-sm font-medium text-accent hover:underline">
                                    Back to orders
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($orders->hasPages())
        <div class="mt-4">{{ $orders->links('vendor.pagination.admin') }}</div>
    @endif
@endsection
