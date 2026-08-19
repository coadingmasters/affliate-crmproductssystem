<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * List all products.
     */
    public function index(): View
    {
        $products = Product::withCount('prices')
            ->withMax('prices', 'user_commission')
            ->latest()
            ->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        return view('admin.products.create', [
            'product' => new Product(['is_active' => true]),
        ]);
    }

    /**
     * Store a new product and its price options.
     */
    public function store(ProductRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $product = Product::create([
                'name' => $request->validated('name'),
                'description' => $request->validated('description'),
                'is_active' => $request->validated('is_active'),
                'image' => $request->hasFile('image')
                    ? $request->file('image')->store('products', 'public')
                    : null,
            ]);

            $this->syncPrices($product, $request->validated('prices'));
        });

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product created.');
    }

    /**
     * Show the edit form.
     */
    public function edit(Product $product): View
    {
        $product->load('prices');

        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update a product and its price options.
     */
    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        DB::transaction(function () use ($request, $product) {
            $attributes = [
                'name' => $request->validated('name'),
                'description' => $request->validated('description'),
                'is_active' => $request->validated('is_active'),
            ];

            $oldImage = $product->image;

            if ($request->hasFile('image')) {
                $attributes['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($attributes);
            $this->syncPrices($product, $request->validated('prices'));

            if ($request->hasFile('image') && $oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
        });

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product updated.');
    }

    /**
     * Delete a product that has no orders against it.
     */
    public function destroy(Product $product): RedirectResponse
    {
        if ($product->orders()->exists()) {
            return back()->with('error', 'This product has orders and cannot be deleted. Switch it to inactive instead.');
        }

        $image = $product->image;

        $product->delete();

        if ($image) {
            Storage::disk('public')->delete($image);
        }

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product deleted.');
    }

    /**
     * Create, update and remove price rows so they match what was submitted.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncPrices(Product $product, array $rows): void
    {
        $keptIds = [];

        foreach ($rows as $row) {
            $attributes = [
                'label' => $row['label'],
                'price' => $row['price'],
                'user_commission' => $row['user_commission'] ?? 0,
                'admin_commission' => $row['admin_commission'] ?? 0,
            ];

            if (! empty($row['id'])) {
                $price = $product->prices()->findOrFail($row['id']);
                $price->update($attributes);
            } else {
                $price = $product->prices()->create($attributes);
            }

            $keptIds[] = $price->id;
        }

        $removed = $product->prices()->whereNotIn('id', $keptIds)->get();

        // Orders reference a price option, so a used one can never be removed.
        $inUse = $removed->filter(fn ($price) => $price->orders()->exists());

        if ($inUse->isNotEmpty()) {
            throw ValidationException::withMessages([
                'prices' => sprintf(
                    'Existing orders use the "%s" price %s, so %s cannot be removed.',
                    $inUse->pluck('label')->join('", "'),
                    $inUse->count() === 1 ? 'option' : 'options',
                    $inUse->count() === 1 ? 'it' : 'they',
                ),
            ]);
        }

        $product->prices()->whereKey($removed->modelKeys())->delete();
    }
}
