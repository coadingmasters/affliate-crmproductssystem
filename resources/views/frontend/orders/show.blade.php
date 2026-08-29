@extends('layouts.customer')

@section('title', "Order #{$order->id} · Med Alert")
@section('heading', "Order #{$order->id}")

@section('content')
    <div class="rise mb-4">
        <a href="{{ route('order.list') }}"
           class="group inline-flex items-center gap-1.5 text-sm font-medium text-muted transition hover:text-brand">
            <svg class="h-4 w-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to all orders
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        <div class="space-y-4 lg:col-span-2">

            {{-- Summary --}}
            <div class="rise rounded-2xl border border-line bg-card p-5 shadow-sm sm:p-6" style="--delay: 60ms">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-ink">Order Summary</h2>
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $order->statusClasses() }}">
                        {{ $order->customerStatusLabel() }}
                    </span>
                </div>

                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">Product</dt>
                        <dd class="mt-1 text-sm font-medium text-ink">{{ $order->product?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">Package</dt>
                        <dd class="mt-1 text-sm font-medium text-ink">{{ $order->productPrice?->label ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">Quantity</dt>
                        <dd class="mt-1 text-sm font-medium text-ink">{{ $order->quantity }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">Total</dt>
                        <dd class="mt-1 text-lg font-extrabold tracking-tight text-brand">${{ number_format($order->total_price, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-muted">Submitted</dt>
                        <dd class="mt-1 text-sm font-medium text-ink">{{ $order->submittedAtLabel() }}</dd>
                    </div>
                    @if ($order->post_date)
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-muted">Payment Date</dt>
                            <dd class="mt-1 text-sm font-medium text-ink">{{ $order->postDateLabel() }}</dd>
                        </div>
                    @endif
                    <div class="sm:col-span-2">
                        <dt class="text-xs uppercase tracking-wider text-muted">Delivery Address</dt>
                        <dd class="mt-1 whitespace-pre-line rounded-xl border border-line bg-elevated p-3 text-sm font-medium text-ink">{{ $order->address }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Anything answered on the order form --}}
            @if (! empty($order->form_data))
                <div class="rise rounded-2xl border border-line bg-card p-5 shadow-sm sm:p-6" style="--delay: 100ms">
                    <h2 class="mb-5 text-sm font-semibold text-ink">Your Details</h2>

                    <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        @foreach ($order->form_data as $key => $value)
                            @php $field = $customFields[$key] ?? null; @endphp
                            <div class="{{ $field && $field->width === 'full' ? 'sm:col-span-2' : '' }}">
                                <dt class="text-xs uppercase tracking-wider text-muted">{{ $field?->label ?? Str::headline($key) }}</dt>
                                <dd class="mt-1 text-sm font-medium text-ink">
                                    @if ($value === null || $value === '')
                                        <span class="text-muted">Not answered</span>
                                    @elseif ($field?->type === 'file')
                                        <a href="{{ Storage::disk('public')->url($value) }}" target="_blank" rel="noopener" class="text-brand hover:underline">View upload</a>
                                    @elseif ($field?->type === 'checkbox')
                                        {{ $value ? 'Yes' : 'No' }}
                                    @else
                                        <span class="whitespace-pre-line">{{ $value }}</span>
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif
        </div>

        {{-- Voice note --}}
        <div class="lg:col-span-1">
            <div class="rise rounded-2xl border border-line bg-card p-5 shadow-sm sm:p-6" style="--delay: 140ms">
                <div class="mb-1 flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand/10 text-brand">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-14 0m7 7v3m0-6a4 4 0 01-4-4V6a4 4 0 118 0v5a4 4 0 01-4 4z"/>
                        </svg>
                    </span>
                    <h2 class="text-sm font-semibold text-ink">Voice Note</h2>
                </div>
                <p class="mb-4 text-xs text-muted">
                    Attach a recording for our team. You can replace it whenever you like.
                </p>

                @if ($order->hasVoiceNote())
                    <div class="mb-4 rounded-xl border border-line bg-elevated p-3">
                        @if ($order->voiceNoteIsVideoContainer())
                            <video controls preload="metadata" class="w-full rounded-lg" style="max-height: 180px">
                                <source src="{{ $order->voiceNoteUrl() }}">
                            </video>
                        @else
                            <audio controls preload="metadata" class="w-full">
                                <source src="{{ $order->voiceNoteUrl() }}">
                            </audio>
                        @endif

                        <p class="mt-2 truncate text-xs font-medium text-ink">{{ $order->voice_note_name }}</p>
                        <p class="text-[11px] text-muted">
                            Added {{ $order->voice_note_uploaded_at?->timezone(config('app.display_timezone'))->diffForHumans() }}
                        </p>

                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <a href="{{ $order->voiceNoteUrl() }}" download
                               class="rounded-lg border border-line px-3 py-1.5 text-xs font-medium text-muted transition hover:border-brand hover:text-brand">
                                Download
                            </a>

                            <form method="POST" action="{{ route('order.voice-note.destroy', $order) }}"
                                  data-confirm-title="Remove voice note"
                                  data-confirm="This recording will be deleted from your order. You can upload a new one afterwards."
                                  data-confirm-text="Remove">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="rounded-lg border border-danger/30 px-3 py-1.5 text-xs font-medium text-danger transition hover:bg-danger hover:text-white">
                                    Remove
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('order.voice-note.store', $order) }}"
                      enctype="multipart/form-data" id="voice-form" class="space-y-3">
                    @csrf

                    <label for="voice_note" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-muted">
                        {{ $order->hasVoiceNote() ? 'Replace recording' : 'Upload a recording' }}
                    </label>

                    <input type="file" name="voice_note" id="voice_note" required
                           accept="audio/*,video/*,.mp3,.mp4,.m4a,.wav,.ogg,.opus,.webm,.aac,.amr,.3gp,.flac,.caf"
                           class="block w-full cursor-pointer rounded-xl border {{ $errors->has('voice_note') ? 'border-danger' : 'border-line' }} bg-elevated text-sm text-muted file:mr-3 file:cursor-pointer file:rounded-l-xl file:border-0 file:bg-brand/10 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-brand hover:file:bg-brand/20">

                    @error('voice_note')
                        <p class="text-xs font-medium text-danger">{{ $message }}</p>
                    @enderror

                    <p class="text-[11px] text-muted">
                        Up to {{ round($maxUploadKb / 1024, 1) }} MB.
                        Accepts {{ strtoupper(implode(', ', array_slice($allowedExtensions, 0, 6))) }} and more.
                    </p>

                    <button type="submit" id="voice-submit"
                            class="cta w-full rounded-xl bg-gradient-to-r from-brand to-brand2 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand/25">
                        <span id="voice-label">{{ $order->hasVoiceNote() ? 'Replace Voice Note' : 'Upload Voice Note' }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('voice-form');
        const input = document.getElementById('voice_note');
        const button = document.getElementById('voice-submit');
        const label = document.getElementById('voice-label');
        const maxKb = @json($maxUploadKb);

        if (!form) {
            return;
        }

        // Catch oversized files before the upload starts, since PHP discards
        // the whole request when it exceeds post_max_size.
        form.addEventListener('submit', function (event) {
            const file = input.files[0];

            if (file && file.size / 1024 > maxKb) {
                event.preventDefault();
                Modal.alert({
                    title: 'File is too large',
                    message: 'That recording is ' + (file.size / 1048576).toFixed(1)
                        + ' MB. The limit is ' + (maxKb / 1024).toFixed(1) + ' MB. Please record a shorter note.',
                });
                return;
            }

            button.disabled = true;
            label.textContent = 'Uploading…';
        });
    })();
</script>
@endpush
