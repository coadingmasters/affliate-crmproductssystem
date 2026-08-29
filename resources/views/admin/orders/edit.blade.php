@extends('layouts.admin')

@section('title', "Edit Order #{$order->id} · Med Alert")
@section('heading', "Edit Order #{$order->id}")

@php
    $input = 'w-full rounded-xl border bg-elevated px-3.5 py-2.5 text-sm text-ink placeholder-muted transition focus:outline-none focus:ring-2 focus:ring-accent/30';
    $ok = 'border-line focus:border-accent';
@endphp

@section('content')
    <div class="rise mb-5">
        <a href="{{ route('admin.orders.show', $order) }}"
           class="group inline-flex items-center gap-1.5 text-sm font-medium text-muted transition hover:text-accent">
            <svg class="h-4 w-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to order
        </a>
    </div>

    <form method="POST" action="{{ route('admin.orders.details', $order) }}" class="max-w-4xl space-y-4">
        @csrf
        @method('PUT')

        {{-- Customer --}}
        <div class="rise rounded-2xl border border-line bg-card p-5 sm:p-6">
            <h2 class="mb-5 text-sm font-semibold text-ink">Customer Details</h2>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="full_name" class="mb-1.5 block text-sm font-medium text-ink">Full Name</label>
                    <input type="text" name="full_name" id="full_name" required
                           value="{{ old('full_name', $order->full_name) }}"
                           class="{{ $input }} {{ $errors->has('full_name') ? 'border-danger' : $ok }}">
                    @error('full_name')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-ink">Email</label>
                    <input type="email" name="email" id="email" required
                           value="{{ old('email', $order->email) }}"
                           class="{{ $input }} {{ $errors->has('email') ? 'border-danger' : $ok }}">
                    @error('email')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="phone" class="mb-1.5 block text-sm font-medium text-ink">Phone</label>
                    <input type="text" name="phone" id="phone" required
                           value="{{ old('phone', $order->phone) }}"
                           class="{{ $input }} {{ $errors->has('phone') ? 'border-danger' : $ok }}">
                    @error('phone')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="address" class="mb-1.5 block text-sm font-medium text-ink">Address</label>
                    <textarea name="address" id="address" rows="1" required
                              class="{{ $input }} {{ $errors->has('address') ? 'border-danger' : $ok }} resize-none">{{ old('address', $order->address) }}</textarea>
                    @error('address')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- What was ordered --}}
        <div class="rise rounded-2xl border border-line bg-card p-5 sm:p-6" style="--delay: 70ms">
            <h2 class="text-sm font-semibold text-ink">Order</h2>
            <p class="mb-5 mt-0.5 text-xs text-muted">
                Changing the package or quantity recalculates the total and both commissions.
            </p>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div>
                    <label for="product_id" class="mb-1.5 block text-sm font-medium text-ink">Product</label>
                    <select name="product_id" id="product_id" class="{{ $input }} {{ $errors->has('product_id') ? 'border-danger' : $ok }}">
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id', $order->product_id) == $product->id)>{{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('product_id')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="product_price_id" class="mb-1.5 block text-sm font-medium text-ink">Package</label>
                    <select name="product_price_id" id="product_price_id" class="{{ $input }} {{ $errors->has('product_price_id') ? 'border-danger' : $ok }}">
                        @foreach ($products as $product)
                            @foreach ($product->prices as $price)
                                <option value="{{ $price->id }}" data-product="{{ $product->id }}"
                                        data-price="{{ $price->price }}"
                                        data-user="{{ $price->user_commission }}"
                                        data-admin="{{ $price->admin_commission }}"
                                        @selected(old('product_price_id', $order->product_price_id) == $price->id)>
                                    {{ $price->label }} — ${{ number_format($price->price, 2) }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                    @error('product_price_id')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="quantity" class="mb-1.5 block text-sm font-medium text-ink">Quantity</label>
                    <input type="number" name="quantity" id="quantity" min="1" max="1000" required
                           value="{{ old('quantity', $order->quantity) }}"
                           class="{{ $input }} {{ $errors->has('quantity') ? 'border-danger' : $ok }}">
                    @error('quantity')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Live preview of what will be saved --}}
            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-accent/25 bg-accent/5 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted">New Total</p>
                    <p id="preview-total" class="mt-1 text-lg font-bold text-accent">${{ number_format($order->total_price, 2) }}</p>
                </div>
                <div class="rounded-xl border border-success/25 bg-success/5 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted">User Commission</p>
                    <p id="preview-user" class="mt-1 text-lg font-bold text-success">${{ number_format($order->user_commission_total, 2) }}</p>
                </div>
                <div class="rounded-xl border border-info/25 bg-info/5 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted">Admin Commission</p>
                    <p id="preview-admin" class="mt-1 text-lg font-bold text-info">${{ number_format($order->admin_commission_total, 2) }}</p>
                </div>
            </div>
        </div>

        {{-- Anything the admin added to the form --}}
        @php
            $editable = $fields->reject(fn ($f) => $f->is_system || $f->isSpecial() || $f->type === 'file');
        @endphp
        @if ($editable->isNotEmpty())
            <div class="rise rounded-2xl border border-line bg-card p-5 sm:p-6" style="--delay: 140ms">
                <h2 class="mb-5 text-sm font-semibold text-ink">Form Answers</h2>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    @foreach ($editable as $field)
                        <div class="{{ $field->width === 'full' ? 'sm:col-span-2' : '' }}">
                            <label for="fd_{{ $field->key }}" class="mb-1.5 block text-sm font-medium text-ink">{{ $field->label }}</label>

                            @if ($field->type === 'select' || $field->type === 'radio')
                                <select name="form_data[{{ $field->key }}]" id="fd_{{ $field->key }}" class="{{ $input }} {{ $ok }}">
                                    <option value="">Not answered</option>
                                    @foreach ($field->options ?? [] as $option)
                                        <option value="{{ $option }}" @selected(($order->form_data[$field->key] ?? null) === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                            @elseif ($field->type === 'textarea')
                                <textarea name="form_data[{{ $field->key }}]" id="fd_{{ $field->key }}" rows="2"
                                          class="{{ $input }} {{ $ok }} resize-none">{{ old('form_data.'.$field->key, $order->form_data[$field->key] ?? '') }}</textarea>
                            @else
                                <input type="{{ $field->type === 'date' ? 'date' : 'text' }}"
                                       name="form_data[{{ $field->key }}]" id="fd_{{ $field->key }}"
                                       value="{{ old('form_data.'.$field->key, $order->form_data[$field->key] ?? '') }}"
                                       class="{{ $input }} {{ $ok }}">
                            @endif

                            @error('form_data.'.$field->key)<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <button type="submit"
                    class="rounded-xl bg-gradient-to-r from-accent to-accent2 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-accent/25 transition hover:opacity-90">
                Save Changes
            </button>
            <a href="{{ route('admin.orders.show', $order) }}"
               class="rounded-xl border border-line px-5 py-2.5 text-center text-sm font-medium text-ink transition hover:bg-elevated">Cancel</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    (function () {
        const product = document.getElementById('product_id');
        const price = document.getElementById('product_price_id');
        const quantity = document.getElementById('quantity');
        const previews = {
            total: document.getElementById('preview-total'),
            user: document.getElementById('preview-user'),
            admin: document.getElementById('preview-admin'),
        };

        const allOptions = Array.from(price.options);

        function money(value) {
            return '$' + value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Only show packages belonging to the chosen product.
        function filterPackages(keepSelection) {
            const wanted = product.value;
            const previous = price.value;

            price.innerHTML = '';
            allOptions
                .filter(option => option.dataset.product === wanted)
                .forEach(option => price.appendChild(option));

            if (keepSelection && Array.from(price.options).some(o => o.value === previous)) {
                price.value = previous;
            }

            update();
        }

        function update() {
            const option = price.selectedOptions[0];
            const qty = parseInt(quantity.value, 10) || 0;

            if (!option) {
                return;
            }

            previews.total.textContent = money(parseFloat(option.dataset.price || 0) * qty);
            previews.user.textContent = money(parseFloat(option.dataset.user || 0) * qty);
            previews.admin.textContent = money(parseFloat(option.dataset.admin || 0) * qty);
        }

        product.addEventListener('change', () => filterPackages(false));
        price.addEventListener('change', update);
        quantity.addEventListener('input', update);

        filterPackages(true);
    })();
</script>
@endpush
