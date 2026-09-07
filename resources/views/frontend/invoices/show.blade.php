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

        <button type="button" onclick="window.print()"
                class="ml-auto inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand to-brand2 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:opacity-90">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print / Save PDF
        </button>
    </div>

    @include('partials.invoice-document')

@endsection

@push('scripts')
    @include('partials.invoice-print')
@endpush
