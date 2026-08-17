@extends('layouts.admin')

@section('title', 'Products · Med Alert')
@section('heading', 'Products')

@section('content')
    <div class="rise mb-5 flex items-center justify-between gap-4">
        <p class="text-sm text-muted">{{ $products->total() }} {{ Str::plural('product', $products->total()) }} total</p>

        <a href="{{ route('admin.products.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-accent to-accent2 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-accent/25 transition hover:opacity-90 hover:shadow-xl hover:shadow-accent/30">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add Product
        </a>
    </div>

    <div class="rise overflow-hidden rounded-2xl border border-line bg-card" style="--delay: 80ms">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-elevated text-xs uppercase tracking-wider text-muted">
                    <tr>
                        <th class="px-5 py-3.5 font-medium">Product</th>
                        <th class="px-5 py-3.5 font-medium">Prices</th>
                        <th class="px-5 py-3.5 font-medium">Status</th>
                        <th class="px-5 py-3.5 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($products as $i => $product)
                        <tr class="row-hover hover:bg-elevated">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($product->image)
                                        <img src="{{ Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}"
                                             class="h-11 w-11 rounded-xl border border-line object-cover">
                                    @else
                                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-accent/15 to-accent2/15 text-xs font-bold text-accent">
                                            {{ Str::upper(Str::substr($product->name, 0, 2)) }}
                                        </span>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="font-medium text-ink">{{ $product->name }}</p>
                                        <p class="max-w-md truncate text-xs text-muted">{{ $product->description }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-accent/10 px-2.5 py-1 text-xs font-medium text-accent">
                                    {{ $product->prices_count }} {{ Str::plural('option', $product->prices_count) }}
                                </span>
                                @if ($product->prices_max_commission > 0)
                                    <span class="mt-1 block text-xs text-success">
                                        up to ${{ number_format($product->prices_max_commission, 2) }} commission
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if ($product->is_active)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-success/10 px-2.5 py-1 text-xs font-medium text-success">
                                        <span class="h-1.5 w-1.5 rounded-full bg-success"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-muted/10 px-2.5 py-1 text-xs font-medium text-muted">
                                        <span class="h-1.5 w-1.5 rounded-full bg-muted"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.products.edit', $product) }}"
                                       class="rounded-lg border border-line px-3 py-1.5 text-xs font-medium text-ink transition hover:border-accent/40 hover:bg-accent/10 hover:text-accent">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                          data-confirm-title="Delete product"
                                          data-confirm="{{ $product->name }} will be removed permanently. This cannot be undone."
                                          data-confirm-text="Delete product">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-lg border border-danger/30 px-3 py-1.5 text-xs font-medium text-danger transition hover:bg-danger hover:text-white">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-14 text-center">
                                <p class="text-muted">No products yet.</p>
                                <a href="{{ route('admin.products.create') }}" class="mt-2 inline-block text-sm font-medium text-accent hover:underline">
                                    Add your first product
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($products->hasPages())
        <div class="mt-5">{{ $products->links() }}</div>
    @endif
@endsection
