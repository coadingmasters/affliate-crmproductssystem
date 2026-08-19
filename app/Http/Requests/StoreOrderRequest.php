<?php

namespace App\Http\Requests;

use App\Models\FormField;
use App\Models\ProductPrice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    /**
     * The largest total the orders.total_price column can hold.
     */
    private const MAX_TOTAL = 999999.99;

    /**
     * The admin-built form, loaded once per request.
     */
    private ?Collection $fields = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The fields the admin has published.
     */
    public function fields(): Collection
    {
        return $this->fields ??= FormField::visible()->get();
    }

    /**
     * Rules come from the saved form, so adding a field in the builder
     * validates it here without a code change.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'product_id' => ['required', Rule::exists('products', 'id')->where('is_active', true)],
            'product_price_id' => [
                'required',
                Rule::exists('product_prices', 'id')->where('product_id', $this->input('product_id')),
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ];

        foreach ($this->fields() as $field) {
            if ($field->isSpecial()) {
                continue;
            }

            $rules[$field->key] = $field->rules();
        }

        return $rules;
    }

    /**
     * Use the admin's own labels in the error messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->fields()
            ->reject(fn (FormField $field) => $field->isSpecial())
            ->mapWithKeys(fn (FormField $field) => [$field->key => strtolower($field->label)])
            ->all();
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
     * The answers that belong in their own order columns.
     *
     * @return array<string, mixed>
     */
    public function columnAnswers(): array
    {
        return $this->fields()
            ->filter(fn (FormField $field) => $field->isColumn())
            ->mapWithKeys(fn (FormField $field) => [$field->key => $this->input($field->key)])
            ->all();
    }

    /**
     * Answers to admin-built fields, keyed by field key.
     *
     * @return array<string, mixed>
     */
    public function customAnswers(): array
    {
        $answers = [];

        foreach ($this->fields() as $field) {
            if ($field->is_system) {
                continue;
            }

            if ($field->type === 'file') {
                $answers[$field->key] = $this->file($field->key)
                    ? $this->file($field->key)->store('form-uploads', 'public')
                    : null;

                continue;
            }

            $answers[$field->key] = $field->type === 'checkbox'
                ? $this->boolean($field->key)
                : $this->input($field->key);
        }

        return $answers;
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

    /**
     * What the customer earns on this order, recalculated server side.
     */
    public function userCommission(): float
    {
        return round((float) $this->productPrice()->user_commission * $this->integer('quantity'), 2);
    }

    /**
     * What the business earns on this order, recalculated server side.
     */
    public function adminCommission(): float
    {
        return round((float) $this->productPrice()->admin_commission * $this->integer('quantity'), 2);
    }
}
