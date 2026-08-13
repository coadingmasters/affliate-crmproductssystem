<?php

namespace App\Http\Requests;

use App\Models\ProductPrice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    /**
     * The largest total the orders.total_price column can hold.
     */
    private const MAX_TOTAL = 999999.99;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:1000'],

            'product_id' => [
                'required',
                Rule::exists('products', 'id')->where('is_active', true),
            ],

            // The price option must belong to the product that was selected.
            'product_price_id' => [
                'required',
                Rule::exists('product_prices', 'id')->where('product_id', $this->input('product_id')),
            ],

            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Please select a product.',
            'product_id.exists' => 'The selected product is not available.',
            'product_price_id.required' => 'Please select a price option.',
            'product_price_id.exists' => 'The selected price option is not available for this product.',
        ];
    }

    /**
     * Run additional checks once the base rules have passed.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if ($this->total() > self::MAX_TOTAL) {
                    $validator->errors()->add('quantity', 'That quantity exceeds the maximum order total. Please contact us for bulk orders.');
                }
            },
        ];
    }

    /**
     * The selected price option.
     */
    public function productPrice(): ProductPrice
    {
        return ProductPrice::findOrFail($this->integer('product_price_id'));
    }

    /**
     * The authoritative order total, always recalculated server side.
     */
    public function total(): float
    {
        return round((float) $this->productPrice()->price * $this->integer('quantity'), 2);
    }
}
