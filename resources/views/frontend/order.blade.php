@extends('layouts.app')

@section('title', 'Place Your Order · Med Alert')

@php
    $base = 'field w-full rounded-xl border bg-elevated px-3.5 py-2.5 text-sm text-ink placeholder-muted';
    $ok = 'border-line';
    $bad = 'border-danger';

    // Small helper so every field styles its error state the same way.
    $cls = fn (string $field) => $base.' '.($errors->has($field) ? $bad : $ok);
@endphp

@section('content')

    @include('partials.account-bar')

    {{-- Brand header --}}
    <div class="rise mb-5 flex items-center justify-center gap-3">
        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-brand to-brand2 text-lg font-bold text-white shadow-lg shadow-brand/30">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
        </span>
        <div class="text-left">
            <h1 class="text-2xl font-extrabold tracking-tight text-ink sm:text-3xl">Med Alert</h1>
            <p class="text-xs text-muted sm:text-sm">Place Your Order Below</p>
        </div>
    </div>

    @if (session('status'))
        <div class="pop mb-4 flex items-start gap-3 rounded-2xl border border-success/30 bg-success/10 p-4 backdrop-blur">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-success" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm font-medium text-success">{{ session('status') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="pop mb-4 flex items-start gap-3 rounded-2xl border border-danger/30 bg-danger/10 p-4">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-danger" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.71-3L13.71 4a2 2 0 00-3.42 0L3.36 16a2 2 0 001.71 3z"/>
            </svg>
            <p class="text-sm font-medium text-danger">Please check the highlighted fields below.</p>
        </div>
    @endif

    <div class="rise rounded-2xl border border-line bg-card/90 p-5 shadow-2xl shadow-black/5 backdrop-blur-xl sm:p-7" style="--delay: 90ms">
        @if ($products->isEmpty())
            <div class="py-10 text-center">
                <p class="text-sm font-medium text-ink">No products are available right now.</p>
                <p class="mt-1 text-sm text-muted">Please check back soon.</p>
            </div>
        @else
            <form method="POST" action="{{ route('order.store') }}" id="order-form" class="space-y-5">
                @csrf

                {{-- ---------------- Contact ---------------- --}}
                <div class="rise" style="--delay: 150ms">
                    <div class="mb-3 flex items-center gap-2.5">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-brand/10 text-[11px] font-bold text-brand">1</span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-muted">Your Details</h2>
                        <span class="h-px flex-1 bg-line"></span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="full_name" class="mb-1.5 block text-sm font-medium text-ink">Full Name</label>
                            <input type="text" name="full_name" id="full_name"
                                   value="{{ old('full_name', auth()->user()->name) }}" required
                                   autocomplete="name" placeholder="John Smith" class="{{ $cls('full_name') }}">
                            @error('full_name')
                                <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="mb-1.5 block text-sm font-medium text-ink">Email</label>
                            <input type="email" name="email" id="email"
                                   value="{{ old('email', auth()->user()->email) }}" required
                                   autocomplete="email" placeholder="john@example.com" class="{{ $cls('email') }}">
                            @error('email')
                                <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="mb-1.5 block text-sm font-medium text-ink">Phone</label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required
                                   autocomplete="tel" placeholder="(555) 123 4567" class="{{ $cls('phone') }}">
                            @error('phone')
                                <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="address" class="mb-1.5 block text-sm font-medium text-ink">Address</label>
                            <textarea name="address" id="address" rows="1" required autocomplete="street-address"
                                      placeholder="1234 MAIN ST APT 5, LOS ANGELES CA 90001"
                                      class="{{ $cls('address') }} resize-none leading-relaxed">{{ old('address') }}</textarea>
                            @error('address')
                                <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- ---------------- Order ---------------- --}}
                <div class="rise" style="--delay: 230ms">
                    <div class="mb-3 flex items-center gap-2.5">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-brand/10 text-[11px] font-bold text-brand">2</span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-muted">Your Order</h2>
                        <span class="h-px flex-1 bg-line"></span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="product_id" class="mb-1.5 block text-sm font-medium text-ink">Select Product</label>
                            <select name="product_id" id="product_id" required class="{{ $cls('product_id') }}">
                                <option value="">Choose a product</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->name }}</option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="product_price_id" class="mb-1.5 block text-sm font-medium text-ink">Select Price / Package</label>
                            <select name="product_price_id" id="product_price_id" required disabled
                                    class="{{ $cls('product_price_id') }}">
                                <option value="">Select a product first</option>
                            </select>
                            @error('product_price_id')
                                <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="quantity" class="mb-1.5 block text-sm font-medium text-ink">Quantity</label>
                            <div class="flex items-stretch gap-2">
                                <button type="button" id="qty-minus" aria-label="Decrease quantity"
                                        class="field flex w-11 shrink-0 items-center justify-center rounded-xl border border-line bg-elevated text-muted hover:border-brand hover:text-brand">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" d="M5 12h14"/>
                                    </svg>
                                </button>
                                <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 1) }}"
                                       min="1" max="1000" required class="{{ $cls('quantity') }} text-center">
                                <button type="button" id="qty-plus" aria-label="Increase quantity"
                                        class="field flex w-11 shrink-0 items-center justify-center rounded-xl border border-line bg-elevated text-muted hover:border-brand hover:text-brand">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
                                    </svg>
                                </button>
                            </div>
                            @error('quantity')
                                <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="total_display" class="mb-1.5 block text-sm font-medium text-ink">Total Price</label>
                            <div class="flex h-[42px] items-center justify-between rounded-xl border border-brand/25 bg-gradient-to-r from-brand/10 to-brand2/10 px-3.5">
                                <span class="text-xs font-medium text-muted">USD</span>
                                <span id="total_display" class="text-lg font-extrabold tracking-tight text-brand">$0.00</span>
                            </div>
                            <p class="mt-1.5 text-xs text-muted">Confirmed when we contact you.</p>
                        </div>
                    </div>
                </div>

                <button type="submit" id="submit-btn"
                        class="cta rise flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand to-brand2 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-brand/30"
                        style="--delay: 310ms">
                    <span id="submit-label">Submit Order</span>
                    <svg id="submit-arrow" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    <svg id="submit-spinner" class="spin hidden h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"/>
                        <path d="M12 2a10 10 0 0110 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                </button>
            </form>
        @endif
    </div>

    <div class="rise mt-4 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-xs text-muted" style="--delay: 380ms">
        <span class="flex items-center gap-1.5">
            <svg class="h-3.5 w-3.5 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Secure checkout
        </span>
        <span class="flex items-center gap-1.5">
            <svg class="h-3.5 w-3.5 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            24/7 support
        </span>
        <span>&copy; {{ date('Y') }} Med Alert</span>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('order-form');

        if (!form) {
            return;
        }

        const productSelect = document.getElementById('product_id');
        const priceSelect = document.getElementById('product_price_id');
        const quantityInput = document.getElementById('quantity');
        const totalDisplay = document.getElementById('total_display');
        const submitButton = document.getElementById('submit-btn');

        // Route template; the placeholder is swapped for the chosen product id.
        const pricesUrl = @json(route('products.prices', ['product' => '__PRODUCT__']));
        const oldPriceId = @json(old('product_price_id'));

        function money(value) {
            return '$' + value.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        }

        function updateTotal() {
            const option = priceSelect.selectedOptions[0];
            const unitPrice = option && option.dataset.price ? parseFloat(option.dataset.price) : 0;
            const quantity = parseInt(quantityInput.value, 10);
            const next = money(unitPrice * (quantity > 0 ? quantity : 0));

            if (next !== totalDisplay.textContent) {
                totalDisplay.textContent = next;
                totalDisplay.classList.remove('flash');
                void totalDisplay.offsetWidth; // restart the animation
                totalDisplay.classList.add('flash');
            }
        }

        function setPlaceholder(text, disabled) {
            priceSelect.innerHTML = '';
            const option = document.createElement('option');
            option.value = '';
            option.textContent = text;
            priceSelect.appendChild(option);
            priceSelect.disabled = disabled;
        }

        async function loadPrices(productId, preselectId) {
            if (!productId) {
                setPlaceholder('Select a product first', true);
                updateTotal();
                return;
            }

            setPlaceholder('Loading…', true);

            try {
                const response = await fetch(pricesUrl.replace('__PRODUCT__', encodeURIComponent(productId)), {
                    headers: { 'Accept': 'application/json' },
                });

                if (!response.ok) {
                    throw new Error('Request failed with status ' + response.status);
                }

                const prices = await response.json();

                if (!prices.length) {
                    setPlaceholder('No packages available', true);
                    updateTotal();
                    return;
                }

                setPlaceholder('Choose a package', false);

                prices.forEach(function (price) {
                    const option = document.createElement('option');
                    option.value = price.id;
                    option.textContent = price.label + ' — ' + money(price.price);
                    option.dataset.price = price.price;
                    option.selected = String(price.id) === String(preselectId);
                    priceSelect.appendChild(option);
                });
            } catch (error) {
                setPlaceholder('Could not load packages — please try again', true);
            }

            updateTotal();
        }

        function nudgeQuantity(delta) {
            const current = parseInt(quantityInput.value, 10) || 1;
            const next = Math.min(1000, Math.max(1, current + delta));
            quantityInput.value = next;
            updateTotal();
        }

        // Keep the address box the height of one input until it actually needs more.
        const address = document.getElementById('address');

        function autoGrow() {
            address.style.height = 'auto';
            address.style.height = Math.min(address.scrollHeight, 120) + 'px';
        }

        address.addEventListener('input', autoGrow);
        autoGrow();

        productSelect.addEventListener('change', function () {
            loadPrices(productSelect.value, null);
        });

        priceSelect.addEventListener('change', updateTotal);
        quantityInput.addEventListener('input', updateTotal);
        document.getElementById('qty-minus').addEventListener('click', () => nudgeQuantity(-1));
        document.getElementById('qty-plus').addEventListener('click', () => nudgeQuantity(1));

        // Guard against double submission.
        form.addEventListener('submit', function () {
            submitButton.disabled = true;
            document.getElementById('submit-label').textContent = 'Submitting…';
            document.getElementById('submit-arrow').classList.add('hidden');
            document.getElementById('submit-spinner').classList.remove('hidden');
        });

        // Restore the dependent dropdown when the form comes back with errors.
        loadPrices(productSelect.value, oldPriceId);
    })();
</script>
@endpush
