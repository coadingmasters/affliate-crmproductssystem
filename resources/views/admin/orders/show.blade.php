@extends('layouts.admin')

@section('title', "Order #{$order->id} · Med Alert")
@section('heading', "Order #{$order->id}")

@section('content')
    <div class="rise mb-5">
        <a href="{{ route('admin.orders.index') }}"
           class="group inline-flex items-center gap-1.5 text-sm font-medium text-muted transition hover:text-accent">
            <svg class="h-4 w-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to orders
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Customer + order detail --}}
        <div class="space-y-4 lg:col-span-2">
            <div class="rise rounded-2xl border border-line bg-card p-5 sm:p-6" style="--delay: 60ms">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-ink">Customer</h2>
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $order->statusClasses() }}">
                        {{ $order->statusLabel() }}
                    </span>
                </div>

                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">Full Name</dt>
                        <dd class="mt-1 text-sm font-medium text-ink">{{ $order->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">Email</dt>
                        <dd class="mt-1 text-sm font-medium">
                            <a href="mailto:{{ $order->email }}" class="text-accent hover:underline">{{ $order->email }}</a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">Phone</dt>
                        <dd class="mt-1 text-sm font-medium">
                            <a href="tel:{{ $order->phone }}" class="text-accent hover:underline">{{ $order->phone }}</a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">Submitted</dt>
                        <dd class="mt-1 text-sm font-medium text-ink">
                            {{ $order->submittedAtLabel() }}
                            <span class="block text-xs font-normal text-muted">{{ $order->submittedAt()->diffForHumans() }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">Status Changed</dt>
                        <dd class="mt-1 text-sm font-medium text-ink">
                            @if ($order->statusChangedAtLabel())
                                {{ $order->statusChangedAtLabel() }}
                                <span class="block text-xs font-normal text-muted">
                                    {{ $order->timeToStatus() }} after it was submitted
                                </span>
                            @else
                                <span class="text-muted">Not changed yet</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">Account</dt>
                        <dd class="mt-1 text-sm font-medium text-ink">
                            @if ($order->user)
                                {{ $order->user->name }}
                                <span class="text-xs text-muted">({{ $order->user->email }})</span>
                            @else
                                <span class="text-muted">Guest / account removed</span>
                            @endif
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs uppercase tracking-wider text-muted">Address</dt>
                        <dd class="mt-1 whitespace-pre-line rounded-xl border border-line bg-elevated p-3 text-sm font-medium text-ink">{{ $order->address }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rise overflow-hidden rounded-2xl border border-line bg-card" style="--delay: 120ms">
                <h2 class="border-b border-line px-5 py-4 text-sm font-semibold text-ink sm:px-6">Order</h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-elevated text-xs uppercase tracking-wider text-muted">
                            <tr>
                                <th class="px-5 py-3 font-medium sm:px-6">Product</th>
                                <th class="px-5 py-3 font-medium">Option</th>
                                <th class="px-5 py-3 font-medium">Unit Price</th>
                                <th class="px-5 py-3 font-medium">Qty</th>
                                <th class="px-5 py-3 font-medium">Commission</th>
                                <th class="px-5 py-3 text-right font-medium sm:px-6">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="px-5 py-4 font-medium text-ink sm:px-6">{{ $order->product?->name ?? '—' }}</td>
                                <td class="px-5 py-4 text-muted">{{ $order->productPrice?->label ?? '—' }}</td>
                                <td class="px-5 py-4 text-muted">
                                    {{ $order->productPrice ? '$'.number_format($order->productPrice->price, 2) : '—' }}
                                </td>
                                <td class="px-5 py-4 text-muted">{{ $order->quantity }}</td>
                                <td class="whitespace-nowrap px-5 py-4 font-semibold text-success">
                                    ${{ number_format($order->commission_total, 2) }}
                                </td>
                                <td class="px-5 py-4 text-right text-lg font-bold text-accent sm:px-6">
                                    ${{ number_format($order->total_price, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Status + notes --}}
        <div class="lg:col-span-1">
            <div class="rise rounded-2xl border border-line bg-card p-5 sm:p-6" style="--delay: 180ms">
                <h2 class="mb-5 text-sm font-semibold text-ink">Manage Order</h2>

                <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="status" class="mb-1.5 block text-sm font-medium text-ink">Status</label>
                        <select name="status" id="status"
                                class="w-full rounded-xl border bg-elevated px-3.5 py-2.5 text-sm text-ink transition focus:outline-none focus:ring-2 focus:ring-accent/30
                                       {{ $errors->has('status') ? 'border-danger' : 'border-line focus:border-accent' }}">
                            @foreach (App\Models\Order::STATUS_META as $value => $meta)
                                <option value="{{ $value }}" @selected(old('status', $order->status) === $value)>
                                    {{ $meta['label'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="notes" class="mb-1.5 block text-sm font-medium text-ink">Internal Notes</label>
                        <textarea name="notes" id="notes" rows="7"
                                  placeholder="Visible to admins only"
                                  class="w-full rounded-xl border bg-elevated px-3.5 py-2.5 text-sm text-ink placeholder-muted transition focus:outline-none focus:ring-2 focus:ring-accent/30
                                         {{ $errors->has('notes') ? 'border-danger' : 'border-line focus:border-accent' }}">{{ old('notes', $order->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            class="w-full rounded-xl bg-gradient-to-r from-accent to-accent2 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-accent/25 transition hover:opacity-90 hover:shadow-xl hover:shadow-accent/30">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
