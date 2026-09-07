{{--
 | The invoice as a downloadable file.
 |
 | Rendered by dompdf, which runs no JavaScript and knows nothing of flexbox
 | or grid, so this is its own template: tables for layout, plain CSS, solid
 | colours instead of gradients. It mirrors the on-screen document rather
 | than sharing it.
--}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->number }}</title>
    <style>
        @page { margin: 0; }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5px;
            color: #0F1521;
        }

        .pad { padding: 0 34px; }

        /* Brand band */
        .band {
            width: 100%;
            background-color: #C2410C;
            color: #FFFFFF;
            padding: 26px 34px;
        }
        .band td { vertical-align: top; }
        .brand-name { font-size: 17px; font-weight: bold; }
        .brand-sub { font-size: 9.5px; color: #F6D9C8; }
        .word { font-size: 27px; font-weight: bold; letter-spacing: 5px; }
        .num { font-family: DejaVu Sans Mono, monospace; font-size: 11px; margin-top: 4px; }
        .chip {
            display: inline-block;
            margin-top: 7px;
            padding: 4px 12px;
            background-color: #FFFFFF;
            color: #C2410C;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 1.2px;
            border-radius: 9px;
        }

        /* Parties */
        .parties { width: 100%; margin-top: 22px; }
        .parties td { vertical-align: top; }
        .label { font-size: 8.5px; font-weight: bold; letter-spacing: 1px; color: #647085; }
        .party { font-size: 13px; font-weight: bold; margin-top: 5px; }
        .party-sub { color: #647085; margin-top: 2px; }
        .meta { width: 100%; margin-top: 8px; font-size: 9.5px; }
        .meta td { padding: 1px 0; }
        .meta .k { color: #647085; text-align: right; padding-right: 10px; }
        .meta .v { font-weight: bold; text-align: right; width: 118px; }

        /* Lines */
        .lines { width: 100%; border-collapse: collapse; margin-top: 24px; }
        .lines thead th {
            background-color: #0F172A;
            color: #FFFFFF;
            font-size: 8.5px;
            font-weight: bold;
            letter-spacing: 1px;
            text-align: left;
            padding: 9px 8px;
        }
        .lines thead th.r { text-align: right; }
        .lines thead th.first { padding-left: 34px; }
        .lines thead th.last { padding-right: 34px; }
        .lines td { padding: 9px 8px; border-bottom: 1px solid #E4E9F2; }
        .lines td.first { padding-left: 34px; }
        .lines td.last { padding-right: 34px; text-align: right; font-weight: bold; }
        .lines td.r { text-align: right; color: #647085; }
        .zebra { background-color: #F6F8FB; }
        .oid { font-weight: bold; }
        .odate { font-size: 8.5px; color: #647085; }
        .prod { color: #647085; }

        /* Summary */
        .sumwrap { background-color: #F6F8FB; padding: 18px 34px; }
        .sum { width: 250px; float: right; }
        .sum td { padding: 5px 0; }
        .sum .k { color: #647085; }
        .sum .v { text-align: right; font-weight: bold; }
        .rule td { border-bottom: 1px solid #DFE4EE; padding: 0; height: 1px; }
        .due {
            width: 250px;
            float: right;
            margin-top: 10px;
            background-color: #0F172A;
            color: #FFFFFF;
            border-radius: 8px;
        }
        .due td { padding: 11px 14px; }
        .due .k { font-size: 9.5px; font-weight: bold; letter-spacing: 1px; }
        .due .v { text-align: right; font-size: 19px; font-weight: bold; }
        .clear { clear: both; }

        /* Notes */
        .notes { padding: 18px 34px 22px 34px; }
        .note-body { margin-top: 4px; }
        .reply {
            margin-top: 12px;
            background-color: #F6F8FB;
            border-left: 3px solid #C2410C;
            padding: 9px 12px;
        }

        /* Footer */
        .foot {
            width: 100%;
            background-color: #0F172A;
            color: #A9B4C6;
            font-size: 8.5px;
            padding: 12px 34px;
            border-top: 3px solid #C2410C;
        }
        .foot .r { text-align: right; font-family: DejaVu Sans Mono, monospace; color: #FFFFFF; font-weight: bold; }
    </style>
</head>
<body>

    <table class="band">
        <tr>
            <td>
                <div class="brand-name">Med Alert</div>
                <div class="brand-sub">Partner Programme</div>
            </td>
            <td align="right">
                <div class="word">INVOICE</div>
                <div class="num">{{ $invoice->number }}</div>
                <div><span class="chip">{{ strtoupper($invoice->statusLabel()) }}</span></div>
            </td>
        </tr>
    </table>

    <div class="pad">
        <table class="parties">
            <tr>
                <td width="50%">
                    <div class="label">BILLED FROM</div>
                    <div class="party">{{ $invoice->user->name }}</div>
                    <div class="party-sub">{{ $invoice->user->email }}</div>
                    <div class="party-sub">Med Alert partner</div>
                </td>
                <td width="50%" align="right">
                    <div class="label">BILLED TO</div>
                    <div class="party">Med Alert</div>
                    <div class="party-sub">Partner Commissions</div>

                    <table class="meta">
                        <tr>
                            <td class="k">Issued</td>
                            <td class="v">{{ $invoice->sentAtLabel() }}</td>
                        </tr>
                        @if ($invoice->coversPeriod())
                            <tr>
                                <td class="k">Period</td>
                                <td class="v">{{ $invoice->periodLabel() }}</td>
                            </tr>
                        @endif
                        @if ($invoice->statusChangedAtLabel())
                            <tr>
                                <td class="k">{{ $invoice->statusLabel() }}</td>
                                <td class="v">{{ $invoice->statusChangedAtLabel() }}</td>
                            </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <table class="lines">
        <thead>
            <tr>
                <th class="first">ORDER</th>
                <th>CUSTOMER</th>
                <th>PRODUCT</th>
                <th class="r">ORDER VALUE</th>
                <th class="r last">COMMISSION</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($invoice->orders as $order)
                <tr class="{{ $loop->even ? 'zebra' : '' }}">
                    <td class="first">
                        <div class="oid">#{{ $order->id }}</div>
                        <div class="odate">{{ $order->submittedAt()->format('M j, Y') }}</div>
                    </td>
                    <td>{{ $order->full_name }}</td>
                    <td class="prod">{{ $order->product?->name ?? '—' }}</td>
                    <td class="r">${{ number_format($order->pivot->order_value, 2) }}</td>
                    <td class="last">${{ number_format($order->pivot->commission, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="first">This invoice carries no order lines.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="sumwrap">
        <table class="sum">
            <tr>
                <td class="k">Orders billed</td>
                <td class="v">{{ $invoice->orders->count() }}</td>
            </tr>
            <tr class="rule"><td colspan="2"></td></tr>
            <tr>
                <td class="k">Total order value</td>
                <td class="v">${{ number_format($invoice->orders->sum(fn ($o) => (float) $o->pivot->order_value), 2) }}</td>
            </tr>
        </table>
        <div class="clear"></div>

        <table class="due">
            <tr>
                <td class="k">AMOUNT DUE</td>
                <td class="v">${{ number_format($invoice->amount, 2) }}</td>
            </tr>
        </table>
        <div class="clear"></div>
    </div>

    @if ($invoice->note || $invoice->admin_note)
        <div class="notes">
            @if ($invoice->note)
                <div class="label">NOTE FROM {{ strtoupper($invoice->user->name) }}</div>
                <div class="note-body">{{ $invoice->note }}</div>
            @endif

            @if ($invoice->admin_note)
                <div class="reply">
                    <div class="label">REPLY FROM MED ALERT</div>
                    <div class="note-body">{{ $invoice->admin_note }}</div>
                </div>
            @endif
        </div>
    @endif

    <table class="foot">
        <tr>
            <td>Commission is claimed on orders that reached Sale or Active Account.</td>
            <td class="r">{{ $invoice->number }}</td>
        </tr>
    </table>

</body>
</html>
