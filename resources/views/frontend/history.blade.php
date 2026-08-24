@extends('layouts.customer')

@section("title", "My Dashboard · Med Alert")
@section('heading', 'My Dashboard')

@section('content')
    <div class="rise mb-5">
        <h2 class="text-xl font-bold tracking-tight text-ink sm:text-2xl">Welcome back, {{ Str::before(auth()->user()->name, ' ') }}</h2>
        <p class="mt-1 text-sm text-muted">Everything you have ordered, and where it stands.</p>
    </div>

    {{-- Summary --}}
    <div class="rise mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4" style="--delay: 80ms">
        @php
            $tiles = [
                ['label' => 'Total Orders', 'value' => $totalOrders, 'tone' => 'text-ink'],
                ['label' => 'In Progress', 'value' => $newOrders, 'tone' => 'text-brand'],
                ['label' => 'Completed', 'value' => $paidOrders, 'tone' => 'text-success'],
                ['label' => 'Cancelled', 'value' => $cancelledOrders, 'tone' => 'text-danger'],
            ];
        @endphp
        @foreach ($tiles as $tile)
            <div class="rounded-2xl border border-line bg-card p-4 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wider text-muted">{{ $tile['label'] }}</p>
                <p class="mt-1.5 text-2xl font-bold {{ $tile['tone'] }}">{{ $tile['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="rise mb-5 flex items-center justify-between gap-3 rounded-2xl border border-brand/25 bg-gradient-to-r from-brand/10 to-brand2/10 px-5 py-4" style="--delay: 140ms">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-muted">Total Ordered</p>
            <p class="text-xs text-muted">On completed orders</p>
        </div>
        <p class="text-2xl font-extrabold tracking-tight text-brand">${{ number_format($totalSpent, 2) }}</p>
    </div>


    {{-- Orders --}}
    <div class="rise space-y-3" style="--delay: 200ms">
        @forelse ($orders as $order)
            @php
                $badge = $order->statusClasses();
                $label = $order->customerStatusLabel();
                $progress = $order->progress();
            @endphp
            <div class="rounded-2xl border border-line bg-card p-4 shadow-sm transition hover:border-brand/40 hover:shadow-md sm:p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2.5">
                            <span class="text-sm font-bold text-ink">Order #{{ $order->id }}</span>
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badge }}">
                                {{ $label }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-muted">
                            Submitted {{ $order->submittedAtLabel() }}
                        </p>
                        @if ($order->post_date)
                            <p class="mt-0.5 text-xs font-semibold text-brand">
                                Payment due {{ $order->postDateLabel() }}
                            </p>
                        @endif
                        @if ($order->statusChangedAtLabel())
                            <p class="mt-0.5 text-xs text-muted">
                                {{ $label }} on {{ $order->statusChangedAtLabel() }}
                            </p>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-extrabold tracking-tight text-brand">${{ number_format($order->total_price, 2) }}</p>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-3 border-t border-line pt-3 sm:grid-cols-3">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-muted">Product</p>
                        <p class="mt-0.5 text-sm font-medium text-ink">{{ $order->product?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-muted">Package</p>
                        <p class="mt-0.5 text-sm font-medium text-ink">{{ $order->productPrice?->label ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-muted">Quantity</p>
                        <p class="mt-0.5 text-sm font-medium text-ink">{{ $order->quantity }}</p>
                    </div>
                </div>

                <div class="mt-3 border-t border-line pt-3">
                    <p class="text-xs uppercase tracking-wider text-muted">Delivery Address</p>
                    <p class="mt-0.5 whitespace-pre-line text-sm text-ink">{{ $order->address }}</p>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-line bg-card px-6 py-14 text-center shadow-sm">
                <svg class="mx-auto mb-3 h-10 w-10 text-muted" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                </svg>
                <p class="text-sm font-medium text-ink">You have not placed any orders yet.</p>
                <a href="{{ route('order.create') }}"
                   class="cta mt-4 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand to-brand2 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand/30">
                    Place your first order
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        @endforelse
    </div>

    @if ($orders->hasPages())
        <div class="mt-5">{{ $orders->links('vendor.pagination.admin') }}</div>
    @endif

@endsection
