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
            <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand to-brand2 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:opacity-90">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Download PDF
            </button>
        </div>

        @include('partials.invoice-document')

        <p data-print-hide class="mt-4 text-center text-[11px] text-muted">
            &copy; {{ date('Y') }} Med Alert. This invoice was shared by its sender.
        </p>
    </div>

    @include('partials.invoice-print')
</body>
</html>
