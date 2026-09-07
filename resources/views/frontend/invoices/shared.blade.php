<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-surface font-sans text-ink antialiased">

    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 sm:py-10">

        <div data-print-hide class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <p class="text-xs text-muted">
                Shared invoice from <span class="font-semibold text-ink">{{ $invoice->user->name }}</span>
            </p>
            <a href="{{ route('invoices.shared.download', $invoice->share_token) }}"
               class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand to-brand2 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:opacity-90">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download PDF
            </a>
        </div>

        @include('partials.invoice-document')

        <p data-print-hide class="mt-4 text-center text-[11px] text-muted">
            &copy; {{ date('Y') }} Med Alert. This invoice was shared by its sender.
        </p>
    </div>

    @include('partials.invoice-print')
</body>
</html>
