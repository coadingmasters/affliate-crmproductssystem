@extends('layouts.customer')

@section('title', 'My Dashboard · Med Alert')
@section('heading', 'My Dashboard')

@php
    // Axis money stays exact until the labels would get too wide to fit.
    $axisMoney = fn (float $v) => $v >= 10000
        ? '$'.rtrim(rtrim(number_format($v / 1000, 1), '0'), '.').'k'
        : '$'.number_format($v);

    // Snap a step up to 1, 2, 2.5, 4 or 5 times a power of ten, so the four
    // gridlines always land on round numbers instead of $348 and $696.
    $niceStep = function (float $raw): float {
        if ($raw <= 0) {
            return 25.0;
        }

        $magnitude = 10 ** floor(log10($raw));

        foreach ([1, 2, 2.5, 4, 5, 10] as $multiple) {
            if ($raw <= $multiple * $magnitude) {
                return $multiple * $magnitude;
            }
        }

        return 10 * $magnitude;
    };

    // Chart geometry. One series, one hue - the axis carries the scale.
    $chart = ['w' => 720, 'h' => 220, 'top' => 18, 'right' => 14, 'bottom' => 30, 'left' => 52];
    $plotW = $chart['w'] - $chart['left'] - $chart['right'];
    $plotH = $chart['h'] - $chart['top'] - $chart['bottom'];

    $peak = (float) $series->max('value');
    $ceiling = $peak > 0 ? $niceStep($peak / 4) * 4 : 100.0;

    $stepX = $series->count() > 1 ? $plotW / ($series->count() - 1) : 0;
    $x = fn (int $i) => $chart['left'] + $i * $stepX;
    $y = fn (float $v) => $chart['top'] + $plotH - ($ceiling > 0 ? $v / $ceiling * $plotH : 0);

    $points = $series->map(fn ($row, $i) => ['cx' => $x($i), 'cy' => $y($row['value'])] + $row);
    $line = $points->map(fn ($p) => round($p['cx'], 1).','.round($p['cy'], 1))->implode(' ');
    $area = $line
        .' '.round($chart['left'] + $plotW, 1).','.($chart['top'] + $plotH)
        .' '.$chart['left'].','.($chart['top'] + $plotH);

    $peakIndex = $peak > 0 ? $points->search(fn ($p) => (float) $p['value'] === $peak) : false;
@endphp

@section('content')

    {{-- Greeting --}}
    <div class="rise mb-5">
        <h2 class="text-xl font-bold tracking-tight text-ink sm:text-2xl">
            Welcome back, {{ Str::before(auth()->user()->name, ' ') }}
        </h2>
        <p class="mt-1 text-sm text-muted">What you have earned, and where every order stands.</p>
    </div>

    {{-- Hero: the number that matters most --}}
    <div class="rise mb-4 overflow-hidden rounded-2xl border border-brand/25 bg-gradient-to-br from-brand/10 via-card to-brand2/10 shadow-sm" style="--delay: 60ms">
        <div class="grid gap-px bg-line/60 sm:grid-cols-3">

            <div class="bg-card/80 p-5 sm:p-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted">Commission Earned</p>
                <p class="mt-2 text-4xl font-extrabold tracking-tight text-brand sm:text-[2.75rem] sm:leading-none">
                    ${{ number_format($earned, 2) }}
                </p>
                <p class="mt-2 text-xs text-muted">
                    Confirmed on {{ $paidOrders }} {{ Str::plural('order', $paidOrders) }}
                </p>
            </div>

            <div class="bg-card/60 p-5 sm:p-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted">Pending</p>
                <p class="mt-2 text-2xl font-bold tracking-tight text-ink">${{ number_format($pending, 2) }}</p>
                <p class="mt-2 text-xs text-muted">
                    Waiting on {{ $newOrders }} {{ Str::plural('order', $newOrders) }} still in progress
                </p>
            </div>

            <div class="bg-card/60 p-5 sm:p-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted">Lifetime Value</p>
                <p class="mt-2 text-2xl font-bold tracking-tight text-ink">${{ number_format($lifetime, 2) }}</p>
                <p class="mt-2 text-xs text-muted">Earned plus pending</p>
            </div>
        </div>
    </div>

    {{-- Supporting figures --}}
    <div class="rise mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4" style="--delay: 110ms">
        @php
            $tiles = [
                ['label' => 'Orders Submitted', 'value' => number_format($totalOrders), 'note' => 'All time'],
                ['label' => 'Confirmed', 'value' => number_format($paidOrders), 'note' => number_format($conversionRate, 0).'% of your orders'],
                ['label' => 'In Progress', 'value' => number_format($newOrders), 'note' => 'Still being processed'],
                ['label' => 'Revenue Generated', 'value' => '$'.number_format($revenue, 2), 'note' => 'Value of confirmed orders'],
            ];
        @endphp
        @foreach ($tiles as $tile)
            <div class="rounded-2xl border border-line bg-card p-4 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wider text-muted">{{ $tile['label'] }}</p>
                <p class="mt-1.5 truncate text-2xl font-bold text-ink">{{ $tile['value'] }}</p>
                <p class="mt-1 truncate text-[11px] text-muted">{{ $tile['note'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Earnings over time --}}
    <div class="rise mb-4 rounded-2xl border border-line bg-card p-4 shadow-sm sm:p-5" style="--delay: 160ms">
        <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
            <h3 class="text-sm font-semibold text-ink">Commission earned by month</h3>
            <p class="text-xs text-muted">Last {{ $series->count() }} months &middot; confirmed orders only</p>
        </div>

        @if ($peak > 0)
            <div id="earnings-chart" class="relative -mx-1">
                <svg viewBox="0 0 {{ $chart['w'] }} {{ $chart['h'] }}" class="w-full" role="img"
                     aria-label="Commission earned each month for the last {{ $series->count() }} months">
                    <defs>
                        <linearGradient id="earn-fill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="rgb(var(--brand))" stop-opacity=".26"/>
                            <stop offset="100%" stop-color="rgb(var(--brand))" stop-opacity="0"/>
                        </linearGradient>
                    </defs>

                    {{-- Recessive grid, four steps --}}
                    @for ($i = 0; $i <= 4; $i++)
                        @php
                            $gy = $chart['top'] + $plotH - ($i / 4 * $plotH);
                            $gv = $ceiling * $i / 4;
                        @endphp
                        <line x1="{{ $chart['left'] }}" y1="{{ round($gy, 1) }}"
                              x2="{{ $chart['left'] + $plotW }}" y2="{{ round($gy, 1) }}"
                              stroke="rgb(var(--line))" stroke-width="1"/>
                        <text x="{{ $chart['left'] - 10 }}" y="{{ round($gy + 4, 1) }}" text-anchor="end"
                              fill="rgb(var(--muted))" font-size="11">{{ $axisMoney($gv) }}</text>
                    @endfor

                    <polygon points="{{ $area }}" fill="url(#earn-fill)"/>
                    <polyline points="{{ $line }}" fill="none" stroke="rgb(var(--brand))"
                              stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>

                    @foreach ($points as $i => $p)
                        {{-- A 2px surface ring keeps markers legible where the line passes under them --}}
                        <circle cx="{{ round($p['cx'], 1) }}" cy="{{ round($p['cy'], 1) }}" r="4"
                                fill="rgb(var(--card))" stroke="rgb(var(--brand))" stroke-width="2"/>

                        <text x="{{ round($p['cx'], 1) }}" y="{{ $chart['h'] - 9 }}" text-anchor="middle"
                              fill="rgb(var(--muted))" font-size="11">{{ $p['short'] }}</text>

                        {{-- Only the peak is labelled directly; the rest are on hover --}}
                        @if ($peakIndex === $i)
                            <text x="{{ round($p['cx'], 1) }}" y="{{ round($p['cy'] - 12, 1) }}"
                                  text-anchor="{{ $i === 0 ? 'start' : ($i === $points->count() - 1 ? 'end' : 'middle') }}"
                                  fill="rgb(var(--ink))" font-size="12" font-weight="700">
                                ${{ number_format($p['value'], 0) }}
                            </text>
                        @endif
                    @endforeach

                    <line id="earnings-crosshair" x1="0" y1="{{ $chart['top'] }}" x2="0"
                          y2="{{ $chart['top'] + $plotH }}" stroke="rgb(var(--brand))" stroke-width="1"
                          stroke-dasharray="3 3" opacity="0"/>

                    {{-- Hit targets are far wider than the markers --}}
                    @foreach ($points as $i => $p)
                        <rect class="earn-hit" x="{{ round($p['cx'] - $stepX / 2, 1) }}" y="{{ $chart['top'] }}"
                              width="{{ round(max($stepX, 24), 1) }}" height="{{ $plotH }}" fill="transparent"
                              data-x="{{ round($p['cx'], 1) }}" data-label="{{ $p['label'] }}"
                              data-value="${{ number_format($p['value'], 2) }}"
                              data-orders="{{ $p['orders'] }} {{ Str::plural('order', $p['orders']) }}"></rect>
                    @endforeach
                </svg>

                <div id="earnings-tip"
                     class="pointer-events-none absolute z-10 hidden -translate-x-1/2 -translate-y-full rounded-xl border border-line bg-card px-3 py-2 text-xs shadow-xl">
                    <p id="earnings-tip-label" class="font-semibold text-ink"></p>
                    <p id="earnings-tip-value" class="mt-0.5 font-bold text-brand"></p>
                    <p id="earnings-tip-orders" class="text-[11px] text-muted"></p>
                </div>
            </div>

            {{-- The same numbers, reachable without the chart --}}
            <details class="mt-3 text-xs">
                <summary class="cursor-pointer text-muted transition hover:text-brand">View as a table</summary>
                <table class="mt-2 w-full text-left">
                    <thead class="text-[11px] uppercase tracking-wider text-muted">
                        <tr>
                            <th class="py-1 font-medium">Month</th>
                            <th class="py-1 font-medium">Orders</th>
                            <th class="py-1 text-right font-medium">Earned</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($series as $row)
                            <tr>
                                <td class="py-1.5 text-ink">{{ $row['label'] }}</td>
                                <td class="py-1.5 text-muted">{{ $row['orders'] }}</td>
                                <td class="py-1.5 text-right font-semibold text-ink">${{ number_format($row['value'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </details>
        @else
            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-line px-4 py-12 text-center">
                <svg class="mb-2 h-8 w-8 text-muted" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 15l3.5-3.5 3 3L21 7"/>
                </svg>
                <p class="text-sm font-medium text-ink">No confirmed earnings yet</p>
                <p class="mt-1 text-xs text-muted">Your commission appears here as soon as an order is confirmed.</p>
            </div>
        @endif
    </div>

    <div class="mb-5 grid gap-4 lg:grid-cols-2">

        {{-- Where the money comes from --}}
        <div class="rise rounded-2xl border border-line bg-card p-4 shadow-sm sm:p-5" style="--delay: 200ms">
            <h3 class="mb-3 text-sm font-semibold text-ink">Top earning products</h3>

            @forelse ($topProducts as $row)
                @php $share = $topProducts->max('earned') > 0 ? $row['earned'] / $topProducts->max('earned') * 100 : 0; @endphp
                <div class="mb-3 last:mb-0">
                    <div class="mb-1 flex items-baseline justify-between gap-3">
                        <span class="truncate text-sm font-medium text-ink">{{ $row['name'] }}</span>
                        <span class="shrink-0 text-sm font-bold text-ink">${{ number_format($row['earned'], 2) }}</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-elevated">
                        <div class="h-full rounded-full bg-brand transition-[width] duration-700 ease-out"
                             style="width: {{ round($share, 1) }}%"></div>
                    </div>
                    <p class="mt-1 text-[11px] text-muted">{{ $row['orders'] }} confirmed {{ Str::plural('order', $row['orders']) }}</p>
                </div>
            @empty
                <p class="py-8 text-center text-sm text-muted">Nothing confirmed yet.</p>
            @endforelse
        </div>

        {{-- Status mix. Text carries identity, never colour alone. --}}
        <div class="rise rounded-2xl border border-line bg-card p-4 shadow-sm sm:p-5" style="--delay: 240ms">
            <h3 class="mb-3 text-sm font-semibold text-ink">Your orders by status</h3>

            @forelse ($statusBreakdown as $row)
                <div class="flex items-center justify-between gap-3 border-b border-line py-2 last:border-0">
                    <span class="flex min-w-0 items-center gap-2.5">
                        <span class="h-2 w-2 shrink-0 rounded-full bg-{{ $row['tone'] === 'muted' ? 'muted' : $row['tone'] }}"></span>
                        <span class="truncate text-sm text-ink">{{ $row['label'] }}</span>
                    </span>
                    <span class="shrink-0 text-sm font-semibold text-ink">{{ $row['value'] }}</span>
                </div>
            @empty
                <p class="py-8 text-center text-sm text-muted">No orders yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Recent orders --}}
    <div class="rise mb-3 flex items-center justify-between gap-3" style="--delay: 280ms">
        <h3 class="text-sm font-semibold text-ink">Recent orders</h3>
        <a href="{{ route('order.list') }}" class="text-xs font-medium text-brand transition hover:underline">
            View all orders
        </a>
    </div>

    <div class="rise space-y-3" style="--delay: 300ms">
        @forelse ($orders as $order)
            @php
                $isEarned = in_array($order->status, \App\Models\Order::EARNING_STATUSES, true);
                $isLost = in_array($order->status, \App\Models\Order::LOST_STATUSES, true);
            @endphp
            <div class="rounded-2xl border border-line bg-card p-4 shadow-sm transition hover:border-brand/40 hover:shadow-md sm:p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2.5">
                            <a href="{{ route('order.show', $order) }}" class="text-sm font-bold text-ink transition hover:text-brand">
                                Order #{{ $order->id }}
                            </a>
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $order->statusClasses() }}">
                                {{ $order->customerStatusLabel() }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-muted">Submitted {{ $order->submittedAtLabel() }}</p>
                        @if ($order->statusDateValue())
                            <p class="mt-0.5 text-xs font-semibold text-brand">
                                {{ $order->statusDateLabel() }}: {{ $order->statusDateValue() }}
                            </p>
                        @endif
                        @if ($order->statusChangedAtLabel())
                            <p class="mt-0.5 text-xs text-muted">
                                {{ $order->customerStatusLabel() }} on {{ $order->statusChangedAtLabel() }}
                            </p>
                        @endif
                    </div>

                    <div class="text-right">
                        <p class="text-xl font-extrabold tracking-tight text-ink">${{ number_format($order->total_price, 2) }}</p>
                        <p class="mt-0.5 text-xs {{ $isLost ? 'text-muted line-through' : ($isEarned ? 'font-semibold text-success' : 'text-muted') }}">
                            {{ $isEarned ? 'You earned' : ($isLost ? 'No commission' : 'You will earn') }}
                            @unless ($isLost)
                                ${{ number_format($order->user_commission_total, 2) }}
                            @endunless
                        </p>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-3 border-t border-line pt-3 sm:grid-cols-3">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-muted">Product</p>
                        <p class="mt-0.5 truncate text-sm font-medium text-ink">{{ $order->product?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-muted">Package</p>
                        <p class="mt-0.5 truncate text-sm font-medium text-ink">{{ $order->productPrice?->label ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-muted">Quantity</p>
                        <p class="mt-0.5 text-sm font-medium text-ink">{{ $order->quantity }}</p>
                    </div>
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

@push('scripts')
<script>
    (function () {
        const chart = document.getElementById('earnings-chart');

        if (!chart) {
            return;
        }

        const svg = chart.querySelector('svg');
        const crosshair = document.getElementById('earnings-crosshair');
        const tip = document.getElementById('earnings-tip');
        const tipLabel = document.getElementById('earnings-tip-label');
        const tipValue = document.getElementById('earnings-tip-value');
        const tipOrders = document.getElementById('earnings-tip-orders');
        const viewWidth = svg.viewBox.baseVal.width;

        function hide() {
            tip.classList.add('hidden');
            crosshair.setAttribute('opacity', '0');
        }

        chart.querySelectorAll('.earn-hit').forEach(function (hit) {
            function show() {
                const x = parseFloat(hit.dataset.x);

                crosshair.setAttribute('x1', x);
                crosshair.setAttribute('x2', x);
                crosshair.setAttribute('opacity', '.5');

                tipLabel.textContent = hit.dataset.label;
                tipValue.textContent = hit.dataset.value;
                tipOrders.textContent = hit.dataset.orders;

                // The SVG scales to its container, so map viewBox units across.
                const scale = chart.clientWidth / viewWidth;
                tip.style.left = (x * scale) + 'px';
                tip.style.top = (parseFloat(hit.getAttribute('y')) * scale) + 'px';
                tip.classList.remove('hidden');
            }

            hit.addEventListener('mouseenter', show);
            hit.addEventListener('focus', show);
            hit.addEventListener('blur', hide);
            hit.setAttribute('tabindex', '0');
        });

        chart.addEventListener('mouseleave', hide);
    })();
</script>
@endpush
