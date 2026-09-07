@extends('layouts.admin')

@section('title', $invoice->number.' · Med Alert Admin')
@section('heading', 'Invoice '.$invoice->number)

@section('content')

    @if (session('status'))
        <div data-print-hide class="mb-4 rounded-2xl border border-success/30 bg-success/10 px-4 py-3 text-sm font-medium text-success">
            {{ session('status') }}
        </div>
    @endif

    <div data-print-hide class="mb-4 flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.users.show', $invoice->user_id) }}"
           class="inline-flex items-center gap-1.5 rounded-xl border border-line px-4 py-2 text-sm font-medium text-muted transition hover:border-accent hover:text-accent">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ $invoice->user->name }}
        </a>

        {{-- Settle it without leaving the document --}}
        <form method="POST" action="{{ route('admin.invoices.status', $invoice) }}"
              class="ml-auto flex flex-wrap items-center gap-2">
            @csrf
            @method('PATCH')

            <input type="text" name="admin_note" value="{{ $invoice->admin_note }}" maxlength="1000"
                   placeholder="Reply to the partner (optional)"
                   class="w-full rounded-xl border border-line bg-elevated px-3.5 py-2 text-sm text-ink placeholder-muted transition focus:border-accent focus:outline-none sm:w-72">

            <select name="status"
                    class="rounded-xl border border-line bg-elevated px-3 py-2 text-sm font-medium text-ink focus:border-accent focus:outline-none">
                @foreach (\App\Models\Invoice::STATUS_META as $value => $meta)
                    <option value="{{ $value }}" @selected($invoice->status === $value)>{{ $meta['label'] }}</option>
                @endforeach
            </select>

            <button type="submit"
                    class="rounded-xl bg-gradient-to-r from-accent to-accent2 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-accent/25 transition hover:opacity-90">
                Save
            </button>
        </form>

        <a href="{{ route('admin.invoices.download', $invoice) }}"
           class="inline-flex items-center gap-2 rounded-xl border border-line px-4 py-2 text-sm font-medium text-muted transition hover:border-accent hover:text-accent">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Download PDF
        </a>
    </div>

    @include('partials.invoice-document')
@endsection

@push('scripts')
    @include('partials.invoice-print')
@endpush
