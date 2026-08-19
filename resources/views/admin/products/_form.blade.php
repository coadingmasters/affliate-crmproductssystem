@php
    // Prefer old input after a failed validation, otherwise the stored price rows.
    $priceRows = old('prices', $product->prices
        ->map(fn ($price) => ['id' => $price->id, 'label' => $price->label, 'price' => $price->price, 'user_commission' => $price->user_commission, 'admin_commission' => $price->admin_commission])
        ->all());

    // Old input is whatever was posted, so keep only well formed rows.
    $priceRows = is_array($priceRows) ? array_filter($priceRows, 'is_array') : [];

    if (empty($priceRows)) {
        $priceRows = [['id' => '', 'label' => '', 'price' => '', 'user_commission' => '', 'admin_commission' => '']];
    }

    $inputClasses = 'w-full rounded-xl border bg-elevated px-3.5 py-2.5 text-sm text-ink placeholder-muted transition focus:outline-none focus:ring-2 focus:ring-accent/30';
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    {{-- Details --}}
    <div class="rise rounded-2xl border border-line bg-card p-5 sm:p-6">
        <h2 class="mb-5 text-sm font-semibold text-ink">Product Details</h2>

        <div class="space-y-5">
            <div>
                <label for="name" class="mb-1.5 block text-sm font-medium text-ink">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                       class="{{ $inputClasses }} {{ $errors->has('name') ? 'border-danger' : 'border-line focus:border-accent' }}"
                       placeholder="Med Alert Pendant">
                @error('name')
                    <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="mb-1.5 block text-sm font-medium text-ink">Description</label>
                <textarea name="description" id="description" rows="4"
                          class="{{ $inputClasses }} {{ $errors->has('description') ? 'border-danger' : 'border-line focus:border-accent' }}"
                          placeholder="Short description of the product">{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="image" class="mb-1.5 block text-sm font-medium text-ink">Image <span class="font-normal text-muted">(optional)</span></label>

                @if ($product->image)
                    <div class="mb-3 flex items-center gap-3">
                        <img src="{{ Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}"
                             class="h-16 w-16 rounded-lg border border-line object-cover">
                        <p class="text-xs text-muted">Uploading a new image replaces this one.</p>
                    </div>
                @endif

                <input type="file" name="image" id="image" accept="image/*"
                       class="block w-full cursor-pointer rounded-lg border border-line bg-elevated text-sm text-muted file:mr-4 file:cursor-pointer file:rounded-l-lg file:border-0 file:bg-elevated file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-ink hover:file:bg-line">
                <p class="mt-1.5 text-xs text-muted">JPG, PNG or WEBP. Max 2 MB.</p>
                @error('image')
                    <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-3">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))
                       class="h-4 w-4 rounded border-line text-accent focus:ring-accent/30">
                <span class="text-sm font-medium text-ink">Active</span>
                <span class="text-sm text-muted">— shown on the public order form</span>
            </label>
        </div>
    </div>

    {{-- Price options --}}
    <div class="rise rounded-2xl border border-line bg-card p-5 sm:p-6" style="--delay: 90ms">
        <div class="mb-1 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-ink">Price Options</h2>
            <button type="button" id="add-price"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-accent px-3 py-1.5 text-xs font-semibold text-accent transition hover:bg-accent hover:text-white">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add Price
            </button>
        </div>
        <p class="mb-4 text-xs text-muted">For example: Small, Medium, Large, Pack of 10, Pack of 60. User Comm. is what the customer earns; Admin Comm. is what the business keeps.</p>

        @error('prices')
            <p class="mb-4 rounded-lg border border-danger/30 bg-danger/10 px-3 py-2 text-sm text-danger">{{ $message }}</p>
        @enderror

        <div class="mb-2 hidden grid-cols-12 gap-3 px-1 sm:grid">
            <span class="col-span-5 text-[11px] font-semibold uppercase tracking-wide text-muted">Label</span>
            <span class="col-span-3 text-[11px] font-semibold uppercase tracking-wide text-muted">Price</span>
            <span class="col-span-3 text-[11px] font-semibold uppercase tracking-wide text-muted">Commission</span>
            <span class="col-span-1"></span>
        </div>

        <div id="price-rows" class="space-y-3">
            @foreach ($priceRows as $index => $row)
                <div class="price-row grid grid-cols-12 items-start gap-3">
                    <input type="hidden" name="prices[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}">

                    <div class="col-span-12 sm:col-span-4">
                        <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-muted sm:hidden">Label</label>
                        <input type="text" name="prices[{{ $index }}][label]" value="{{ $row['label'] ?? '' }}"
                               placeholder="Label (e.g. Pack of 10)"
                               class="{{ $inputClasses }} {{ $errors->has("prices.$index.label") ? 'border-danger' : 'border-line focus:border-accent' }}">
                        @error("prices.$index.label")
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-span-4 sm:col-span-2">
                        <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-muted sm:hidden">Price</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-muted">$</span>
                            <input type="number" step="0.01" min="0" name="prices[{{ $index }}][price]" value="{{ $row['price'] ?? '' }}"
                                   placeholder="0.00"
                                   class="{{ $inputClasses }} pl-7 {{ $errors->has("prices.$index.price") ? 'border-danger' : 'border-line focus:border-accent' }}">
                        </div>
                        @error("prices.$index.price")
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-span-4 sm:col-span-2">
                        <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-muted sm:hidden">User Commission</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-success">$</span>
                            <input type="number" step="0.01" min="0" name="prices[{{ $index }}][user_commission]" value="{{ $row['user_commission'] ?? '' }}"
                                   placeholder="0.00"
                                   class="{{ $inputClasses }} pl-7 {{ $errors->has("prices.$index.user_commission") ? 'border-danger' : 'border-line focus:border-accent' }}">
                        </div>
                        @error("prices.$index.user_commission")
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-span-4 sm:col-span-3">
                        <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-muted sm:hidden">Admin Commission</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-info">$</span>
                            <input type="number" step="0.01" min="0" name="prices[{{ $index }}][admin_commission]" value="{{ $row['admin_commission'] ?? '' }}"
                                   placeholder="0.00"
                                   class="{{ $inputClasses }} pl-7 {{ $errors->has("prices.$index.admin_commission") ? 'border-danger' : 'border-line focus:border-accent' }}">
                        </div>
                        @error("prices.$index.admin_commission")
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-span-3 sm:col-span-1">
                        <button type="button"
                                class="remove-price flex h-[42px] w-full items-center justify-center rounded-lg border border-line text-muted transition hover:border-danger hover:bg-danger hover:text-white"
                                aria-label="Remove price option">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="rounded-xl bg-gradient-to-r from-accent to-accent2 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-accent/25 transition hover:opacity-90 hover:shadow-xl hover:shadow-accent/30">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('admin.products.index') }}"
           class="rounded-lg border border-line px-5 py-2.5 text-sm font-medium text-ink transition hover:bg-elevated">
            Cancel
        </a>
    </div>
</form>

{{-- Blueprint for JS-added rows; __INDEX__ is swapped for a running counter. --}}
<template id="price-row-template">
    <div class="price-row grid grid-cols-12 items-start gap-3">
        <input type="hidden" name="prices[__INDEX__][id]" value="">

        <div class="col-span-12 sm:col-span-4">
            <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-muted sm:hidden">Label</label>
            <input type="text" name="prices[__INDEX__][label]" placeholder="Label (e.g. Pack of 10)"
                   class="{{ $inputClasses }} border-line focus:border-accent">
        </div>

        <div class="col-span-4 sm:col-span-2">
            <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-muted sm:hidden">Price</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-muted">$</span>
                <input type="number" step="0.01" min="0" name="prices[__INDEX__][price]" placeholder="0.00"
                       class="{{ $inputClasses }} border-line pl-7 focus:border-accent">
            </div>
        </div>

        <div class="col-span-4 sm:col-span-2">
            <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-muted sm:hidden">User Commission</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-success">$</span>
                <input type="number" step="0.01" min="0" name="prices[__INDEX__][user_commission]" placeholder="0.00"
                       class="{{ $inputClasses }} border-line pl-7 focus:border-accent">
            </div>
        </div>

        <div class="col-span-4 sm:col-span-3">
            <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-muted sm:hidden">Admin Commission</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-info">$</span>
                <input type="number" step="0.01" min="0" name="prices[__INDEX__][admin_commission]" placeholder="0.00"
                       class="{{ $inputClasses }} border-line pl-7 focus:border-accent">
            </div>
        </div>

        <div class="col-span-3 sm:col-span-1">
            <button type="button"
                    class="remove-price flex h-[42px] w-full items-center justify-center rounded-lg border border-line text-muted transition hover:border-danger hover:bg-danger hover:text-white"
                    aria-label="Remove price option">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
</template>

@push('scripts')
<script>
    (function () {
        const container = document.getElementById('price-rows');
        const template = document.getElementById('price-row-template');
        let nextIndex = {{ count($priceRows) ? max(array_keys($priceRows)) + 1 : 0 }};

        document.getElementById('add-price').addEventListener('click', function () {
            const markup = template.innerHTML.replace(/__INDEX__/g, nextIndex++);
            container.insertAdjacentHTML('beforeend', markup);
            container.lastElementChild.querySelector('input[type="text"]').focus();
        });

        // Delegated so rows added later are covered too.
        container.addEventListener('click', function (event) {
            const button = event.target.closest('.remove-price');

            if (!button) {
                return;
            }

            if (container.querySelectorAll('.price-row').length === 1) {
                Modal.alert({ title: 'At least one price needed', message: 'A product must keep at least one price option. Add another before removing this one.' });
                return;
            }

            button.closest('.price-row').remove();
        });
    })();
</script>
@endpush
