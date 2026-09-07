@extends('layouts.customer')

@section('title', $invoice->number.' · Med Alert')
@section('heading', 'Invoice '.$invoice->number)

@section('content')

    @if (session('status'))
        <div data-print-hide class="rise mb-4 rounded-2xl border border-success/30 bg-success/10 px-4 py-3 text-sm font-medium text-success">
            {{ session('status') }}
        </div>
    @endif

    <div data-print-hide class="mb-4 flex flex-wrap items-center gap-3">
        <a href="{{ route('invoices.index') }}"
           class="inline-flex items-center gap-1.5 rounded-xl border border-line px-4 py-2 text-sm font-medium text-muted transition hover:border-brand hover:text-brand">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            All invoices
        </a>

        @if ($invoice->share_token)
            @php $link = route('invoices.shared', $invoice->share_token); @endphp

            <div class="flex flex-1 flex-wrap items-center gap-2">
                <input type="text" readonly value="{{ $link }}" id="share-link"
                       class="min-w-0 flex-1 rounded-xl border border-line bg-elevated px-3 py-2 text-xs text-muted sm:max-w-sm">
                <button type="button" data-copy="{{ $link }}"
                        class="rounded-xl border border-line px-3 py-2 text-sm font-medium text-muted transition hover:border-brand hover:text-brand">
                    Copy link
                </button>
                <form method="POST" action="{{ route('invoices.unshare', $invoice) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-line px-3 py-2 text-sm font-medium text-muted transition hover:border-danger hover:text-danger">
                        Stop sharing
                    </button>
                </form>
            </div>
        @else
            <form method="POST" action="{{ route('invoices.share', $invoice) }}" class="ml-auto">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl border border-line px-4 py-2 text-sm font-medium text-muted transition hover:border-brand hover:text-brand">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684zm0-12a3 3 0 105.368-2.684A3 3 0 0015.316 6.658z"/>
                    </svg>
                    Share link
                </button>
            </form>
        @endif

        <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand to-brand2 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:opacity-90">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Download PDF
        </button>
    </div>

    @include('partials.invoice-document')

@endsection

@push('scripts')
    @include('partials.invoice-print')
    <script>
        document.querySelectorAll('[data-copy]').forEach(function (button) {
            button.addEventListener('click', function () {
                const done = function () {
                    const was = button.textContent;
                    button.textContent = 'Copied';
                    setTimeout(function () { button.textContent = was; }, 1600);
                };

                // The clipboard API needs a secure origin, so fall back to
                // selecting the field when it is not available.
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(button.dataset.copy).then(done, done);
                } else {
                    document.getElementById('share-link').select();
                    done();
                }
            });
        });
    </script>
@endpush
