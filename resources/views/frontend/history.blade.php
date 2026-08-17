@extends('layouts.app')

@section("title", "My Dashboard · Med Alert")

@section('content')
    @include('partials.account-bar')

    <div class="rise mb-5">
        <h1 class="text-2xl font-extrabold tracking-tight text-ink sm:text-3xl">My Dashboard</h1>
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
            <div class="rounded-2xl border border-line bg-card/90 p-4 backdrop-blur">
                <p class="text-xs font-medium uppercase tracking-wider text-muted">{{ $tile['label'] }}</p>
                <p class="mt-1.5 text-2xl font-bold {{ $tile['tone'] }}">{{ $tile['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="rise mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2" style="--delay: 140ms">
        <div class="flex items-center justify-between gap-3 rounded-2xl border border-brand/25 bg-gradient-to-r from-brand/10 to-brand2/10 px-5 py-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-muted">Total Ordered</p>
                <p class="text-xs text-muted">On completed orders</p>
            </div>
            <p class="text-2xl font-extrabold tracking-tight text-brand">${{ number_format($totalSpent, 2) }}</p>
        </div>

        <div class="flex items-center justify-between gap-3 rounded-2xl border border-success/30 bg-success/10 px-5 py-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-muted">Commission Earned</p>
                <p class="text-xs text-muted">Paid once settled</p>
            </div>
            <p class="text-2xl font-extrabold tracking-tight text-success">${{ number_format($commissionEarned, 2) }}</p>
        </div>
    </div>

    @if ($commissionPending > 0)
        <div class="rise mb-5 flex items-center justify-between gap-3 rounded-xl border border-line bg-card/80 px-4 py-3 backdrop-blur" style="--delay: 170ms">
            <span class="text-xs text-muted">Commission still in progress</span>
            <span class="text-sm font-bold text-ink">${{ number_format($commissionPending, 2) }}</span>
        </div>
    @endif

    {{-- Orders --}}
    <div class="rise space-y-3" style="--delay: 200ms">
        @forelse ($orders as $order)
            @php
                $badge = $order->statusClasses();
                $label = $order->customerStatusLabel();
                $progress = $order->progress();
            @endphp
            <div class="rounded-2xl border border-line bg-card/90 p-4 backdrop-blur transition hover:border-brand/40 sm:p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2.5">
                            <span class="text-sm font-bold text-ink">Order #{{ $order->id }}</span>
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badge }}">
                                {{ $label }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-muted">
                            {{ $order->created_at->format('M j, Y \a\t g:i A') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-extrabold tracking-tight text-brand">${{ number_format($order->total_price, 2) }}</p>
                        @if ($order->commission_total > 0)
                            <p class="mt-0.5 text-xs font-semibold text-success">
                                Commission ${{ number_format($order->commission_total, 2) }}
                            </p>
                        @endif
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
            <div class="rounded-2xl border border-line bg-card/90 px-6 py-14 text-center backdrop-blur">
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
        <div class="mt-5">{{ $orders->links() }}</div>
    @endif

    <p class="rise mt-6 text-center text-xs text-muted" style="--delay: 280ms">
        &copy; {{ date('Y') }} Med Alert
    </p>
@endsection
