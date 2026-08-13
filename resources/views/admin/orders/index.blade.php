@extends('layouts.admin')

@section('title', 'Orders · Med Alert')
@section('heading', 'Orders')

@section('content')
    @php
        $filters = ['all' => 'All', 'new' => 'New', 'paid' => 'Paid', 'cancelled' => 'Cancelled'];
    @endphp

    <div class="rise mb-5 flex flex-wrap items-center justify-between gap-4">
        <div class="inline-flex rounded-xl border border-line bg-card p-1">
            @foreach ($filters as $value => $label)
                <a href="{{ route('admin.orders.index', $value === 'all' ? [] : ['status' => $value]) }}"
                   class="rounded-lg px-3.5 py-1.5 text-sm font-medium transition
                          {{ $status === $value
                              ? 'bg-gradient-to-r from-accent to-accent2 text-white shadow-md shadow-accent/25'
                              : 'text-muted hover:bg-elevated hover:text-ink' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <p class="text-sm text-muted">{{ $orders->total() }} {{ Str::plural('order', $orders->total()) }}</p>
    </div>

    <div class="rise overflow-hidden rounded-2xl border border-line bg-card" style="--delay: 80ms">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-elevated text-xs uppercase tracking-wider text-muted">
                    <tr>
                        <th class="px-4 py-3.5 font-medium">ID</th>
                        <th class="px-4 py-3.5 font-medium">Full Name</th>
                        <th class="px-4 py-3.5 font-medium">Email</th>
                        <th class="px-4 py-3.5 font-medium">Phone</th>
                        <th class="px-4 py-3.5 font-medium">Product</th>
                        <th class="px-4 py-3.5 font-medium">Price Label</th>
                        <th class="px-4 py-3.5 font-medium">Qty</th>
                        <th class="px-4 py-3.5 font-medium">Total</th>
                        <th class="px-4 py-3.5 font-medium">Status</th>
                        <th class="px-4 py-3.5 font-medium">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($orders as $order)
                        <tr class="row-hover cursor-pointer hover:bg-elevated"
                            onclick="window.location='{{ route('admin.orders.show', $order) }}'">
                            <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-accent">#{{ $order->id }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-medium text-ink">{{ $order->full_name }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-muted">{{ $order->email }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-muted">{{ $order->phone }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-muted">{{ $order->product?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-muted">{{ $order->productPrice?->label ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-muted">{{ $order->quantity }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-ink">${{ number_format($order->total_price, 2) }}</td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium capitalize {{ $order->statusClasses() }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-muted">{{ $order->created_at->format('M j, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-14 text-center text-muted">
                                No {{ $status === 'all' ? '' : $status }} orders found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($orders->hasPages())
        <div class="mt-5">{{ $orders->links() }}</div>
    @endif
@endsection
