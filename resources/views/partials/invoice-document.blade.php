    {{-- Shared by the partner's copy and the admin's. --}}
    <div class="overflow-hidden rounded-2xl border border-line bg-card shadow-sm print:border-0 print:shadow-none">

        {{-- Masthead --}}
        <div class="flex flex-wrap items-start justify-between gap-6 border-b border-line p-6 sm:p-8">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-brand to-brand2 text-white">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21s-7.5-4.7-9.3-9A5.4 5.4 0 0112 5.6 5.4 5.4 0 0121.3 12c-1.8 4.3-9.3 9-9.3 9z"/>
                        </svg>
                    </span>
                    <div class="leading-tight">
                        <p class="text-base font-bold text-ink">Med Alert</p>
                        <p class="text-xs text-muted">Partner Programme</p>
                    </div>
                </div>

                <div class="mt-5">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Billed from</p>
                    <p class="mt-1 text-sm font-bold text-ink">{{ $invoice->user->name }}</p>
                    <p class="text-xs text-muted">{{ $invoice->user->email }}</p>
                </div>
            </div>

            <div class="text-right">
                <p class="text-2xl font-extrabold tracking-tight text-ink">INVOICE</p>
                <p class="mt-1 font-mono text-sm font-bold text-brand">{{ $invoice->number }}</p>

                <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $invoice->statusClasses() }}">
                    {{ $invoice->statusLabel() }}
                </span>

                <dl class="mt-5 space-y-1 text-xs">
                    <div class="flex justify-end gap-3">
                        <dt class="text-muted">Issued</dt>
                        <dd class="w-32 font-semibold text-ink">{{ $invoice->sentAtLabel() }}</dd>
                    </div>
                    @if ($invoice->coversPeriod())
                        <div class="flex justify-end gap-3">
                            <dt class="text-muted">Period</dt>
                            <dd class="w-32 font-semibold text-ink">{{ $invoice->periodLabel() }}</dd>
                        </div>
                    @endif
                    @if ($invoice->statusChangedAtLabel())
                        <div class="flex justify-end gap-3">
                            <dt class="text-muted">{{ $invoice->statusLabel() }}</dt>
                            <dd class="w-32 font-semibold text-ink">{{ $invoice->statusChangedAtLabel() }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Lines --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-sm">
                <thead>
                    <tr class="border-b border-line bg-elevated text-[11px] uppercase tracking-wider text-muted">
                        <th class="px-4 py-3 text-left font-semibold sm:px-6">Order</th>
                        <th class="px-4 py-3 text-left font-semibold">Customer</th>
                        <th class="px-4 py-3 text-left font-semibold">Product</th>
                        <th class="px-4 py-3 text-right font-semibold">Order Value</th>
                        <th class="px-4 py-3 text-right font-semibold sm:px-6">Commission</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($invoice->orders as $order)
                        <tr>
                            <td class="px-4 py-3 sm:px-6">
                                <p class="font-semibold text-ink">#{{ $order->id }}</p>
                                <p class="text-xs text-muted">{{ $order->submittedAt()->format('M j, Y') }}</p>
                            </td>
                            <td class="px-4 py-3 text-ink">{{ $order->full_name }}</td>
                            <td class="px-4 py-3 text-muted">{{ $order->product?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-muted">
                                ${{ number_format($order->pivot->order_value, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold tabular-nums text-ink sm:px-6">
                                ${{ number_format($order->pivot->commission, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-6 text-center text-sm text-muted">
                                This invoice carries no order lines.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Total --}}
        <div class="flex justify-end border-t border-line bg-elevated px-4 py-5 sm:px-8">
            <div class="w-full max-w-xs space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted">Orders billed</span>
                    <span class="font-semibold text-ink">{{ $invoice->orders->count() }}</span>
                </div>
                <div class="flex items-center justify-between border-t border-line pt-2">
                    <span class="text-sm font-semibold text-ink">Amount due</span>
                    <span class="text-2xl font-extrabold tracking-tight text-brand">
                        ${{ number_format($invoice->amount, 2) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Notes --}}
        @if ($invoice->note || $invoice->admin_note)
            <div class="space-y-3 border-t border-line p-6 sm:p-8">
                @if ($invoice->note)
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Your note</p>
                        <p class="mt-1 text-sm text-ink">{{ $invoice->note }}</p>
                    </div>
                @endif

                @if ($invoice->admin_note)
                    <div class="rounded-xl border border-line bg-elevated px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Reply from Med Alert</p>
                        <p class="mt-1 text-sm text-ink">{{ $invoice->admin_note }}</p>
                    </div>
                @endif
            </div>
        @endif

        <p class="border-t border-line px-6 py-4 text-center text-[11px] text-muted sm:px-8">
            Commission is claimed on orders that reached Sale or Active Account.
            Raised through the Med Alert partner portal &middot; {{ $invoice->number }}
        </p>
    </div>
