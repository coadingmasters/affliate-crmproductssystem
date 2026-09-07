{{-- The invoice itself. Shared by the partner's copy, the admin's, and print. --}}
<div class="invoice-sheet overflow-hidden rounded-2xl border border-line bg-card shadow-sm">

    {{-- Brand band --}}
    <div class="invoice-band relative overflow-hidden bg-gradient-to-r from-[#7C2D12] via-[#C2410C] to-[#EA580C] px-6 py-7 text-white sm:px-10">
        <div class="relative flex flex-wrap items-start justify-between gap-6">
            <div>
                <div class="flex items-center gap-3">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-white/15 ring-1 ring-white/25">
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21s-7.5-4.7-9.3-9A5.4 5.4 0 0112 5.6 5.4 5.4 0 0121.3 12c-1.8 4.3-9.3 9-9.3 9z"/>
                        </svg>
                    </span>
                    <div class="leading-tight">
                        <p class="text-lg font-bold tracking-tight">Med Alert</p>
                        <p class="text-xs text-white/75">Partner Programme</p>
                    </div>
                </div>
            </div>

            <div class="text-right">
                <p class="text-3xl font-extrabold uppercase tracking-[0.2em]">Invoice</p>
                <p class="mt-1 font-mono text-sm font-semibold text-white/90">{{ $invoice->number }}</p>
                <span class="invoice-status mt-2 inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-wider ring-1 ring-white/30">
                    {{ $invoice->statusLabel() }}
                </span>
            </div>
        </div>
    </div>

    {{-- Who and when --}}
    <div class="grid gap-6 border-b border-line px-6 py-6 sm:grid-cols-2 sm:px-10">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-muted">Billed from</p>
            <p class="mt-2 text-base font-bold text-ink">{{ $invoice->user->name }}</p>
            <p class="text-sm text-muted">{{ $invoice->user->email }}</p>
            <p class="mt-3 text-xs text-muted">Med Alert partner</p>
        </div>

        <div class="sm:text-right">
            <p class="text-[11px] font-bold uppercase tracking-wider text-muted">Billed to</p>
            <p class="mt-2 text-base font-bold text-ink">Med Alert</p>
            <p class="text-sm text-muted">Partner Commissions</p>

            <dl class="mt-3 space-y-1 text-xs">
                <div class="flex gap-3 sm:justify-end">
                    <dt class="w-24 text-muted sm:w-auto">Issued</dt>
                    <dd class="font-semibold text-ink sm:w-40">{{ $invoice->sentAtLabel() }}</dd>
                </div>
                @if ($invoice->coversPeriod())
                    <div class="flex gap-3 sm:justify-end">
                        <dt class="w-24 text-muted sm:w-auto">Period</dt>
                        <dd class="font-semibold text-ink sm:w-40">{{ $invoice->periodLabel() }}</dd>
                    </div>
                @endif
                @if ($invoice->statusChangedAtLabel())
                    <div class="flex gap-3 sm:justify-end">
                        <dt class="w-24 text-muted sm:w-auto">{{ $invoice->statusLabel() }}</dt>
                        <dd class="font-semibold text-ink sm:w-40">{{ $invoice->statusChangedAtLabel() }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    {{-- Lines --}}
    <div class="overflow-x-auto">
        <table class="invoice-lines w-full min-w-[680px] text-sm">
            <thead>
                <tr class="bg-[#0F172A] text-white">
                    <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider sm:pl-10">Order</th>
                    <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider">Customer</th>
                    <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider">Product</th>
                    <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider">Order Value</th>
                    <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider sm:pr-10">Commission</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoice->orders as $order)
                    <tr class="{{ $loop->even ? 'bg-[#F6F8FB]' : 'bg-white' }} border-b border-line">
                        <td class="px-4 py-3 sm:pl-10">
                            <p class="font-bold text-ink">#{{ $order->id }}</p>
                            <p class="text-xs text-muted">{{ $order->submittedAt()->format('M j, Y') }}</p>
                        </td>
                        <td class="px-4 py-3 font-medium text-ink">{{ $order->full_name }}</td>
                        <td class="px-4 py-3 text-muted">{{ $order->product?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right tabular-nums text-muted">${{ number_format($order->pivot->order_value, 2) }}</td>
                        <td class="px-4 py-3 text-right font-bold tabular-nums text-ink sm:pr-10">${{ number_format($order->pivot->commission, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-muted">This invoice carries no order lines.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Summary --}}
    <div class="flex justify-end bg-[#F6F8FB] px-6 py-6 sm:px-10">
        <div class="w-full max-w-sm">
            <div class="flex items-center justify-between border-b border-line pb-2 text-sm">
                <span class="text-muted">Orders billed</span>
                <span class="font-semibold text-ink">{{ $invoice->orders->count() }}</span>
            </div>
            <div class="flex items-center justify-between border-b border-line py-2 text-sm">
                <span class="text-muted">Total order value</span>
                <span class="font-semibold tabular-nums text-ink">
                    ${{ number_format($invoice->orders->sum(fn ($o) => (float) $o->pivot->order_value), 2) }}
                </span>
            </div>
            <div class="invoice-due mt-3 flex items-center justify-between rounded-xl bg-[#0F172A] px-4 py-3 text-white">
                <span class="text-sm font-semibold uppercase tracking-wider">Amount due</span>
                <span class="text-2xl font-extrabold tabular-nums">${{ number_format($invoice->amount, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Notes --}}
    @if ($invoice->note || $invoice->admin_note)
        <div class="space-y-3 border-t border-line px-6 py-6 sm:px-10">
            @if ($invoice->note)
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-muted">Note from {{ $invoice->user->name }}</p>
                    <p class="mt-1 text-sm text-ink">{{ $invoice->note }}</p>
                </div>
            @endif

            @if ($invoice->admin_note)
                <div class="rounded-xl border-l-4 border-brand bg-[#F6F8FB] px-4 py-3">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-muted">Reply from Med Alert</p>
                    <p class="mt-1 text-sm text-ink">{{ $invoice->admin_note }}</p>
                </div>
            @endif
        </div>
    @endif

    {{-- Footer strip --}}
    <div class="invoice-foot flex flex-wrap items-center justify-between gap-2 border-t-2 border-brand bg-[#0F172A] px-6 py-4 text-[11px] text-white/70 sm:px-10">
        <span>Commission is claimed on orders that reached <span class="font-semibold text-white">Sale</span> or <span class="font-semibold text-white">Active Account</span>.</span>
        <span class="font-mono font-semibold text-white">{{ $invoice->number }}</span>
    </div>
</div>
