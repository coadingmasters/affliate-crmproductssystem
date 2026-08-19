<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalise the checkbox before validation runs.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['required', 'boolean'],

            'prices' => ['required', 'array', 'min:1'],
            'prices.*.id' => [
                'nullable',
                'integer',
                $product
                    ? Rule::exists('product_prices', 'id')->where('product_id', $product->id)
                    : 'prohibited',
            ],
            'prices.*.label' => ['required', 'string', 'max:255'],
            'prices.*.price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'prices.*.user_commission' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'prices.*.admin_commission' => ['required', 'numeric', 'min:0', 'max:999999.99'],
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
            'prices.required' => 'Add at least one price option.',
            'prices.min' => 'Add at least one price option.',
            'prices.*.label.required' => 'The price label is required.',
            'prices.*.price.required' => 'The price is required.',
            'prices.*.price.numeric' => 'The price must be a number.',
            'prices.*.user_commission.required' => 'Set the user commission (0 is allowed).',
            'prices.*.admin_commission.required' => 'Set the admin commission (0 is allowed).',
            'prices.*.user_commission.numeric' => 'The user commission must be a number.',
            'prices.*.admin_commission.numeric' => 'The admin commission must be a number.',
        ];
    }
}
