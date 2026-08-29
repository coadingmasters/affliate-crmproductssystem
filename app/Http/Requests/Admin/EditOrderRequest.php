<?php

namespace App\Http\Requests\Admin;

use App\Models\FormField;
use App\Models\ProductPrice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class EditOrderRequest extends FormRequest
{
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
     * The fields the customer form is made of.
     */
    public function fields(): Collection
    {
        return $this->fields ??= FormField::visible()->get();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:1000'],

            'product_id' => ['required', Rule::exists('products', 'id')],
            'product_price_id' => [
                'required',
                Rule::exists('product_prices', 'id')->where('product_id', $this->input('product_id')),
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ];

        // Whatever the admin built onto the form stays editable here too.
        foreach ($this->fields() as $field) {
            if ($field->is_system || $field->isSpecial() || $field->type === 'file') {
                continue;
            }

            $rules['form_data.'.$field->key] = ['nullable', 'string', 'max:2000'];
        }

        return $rules;
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_price_id.exists' => 'That package does not belong to the selected product.',
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
     * Money is always recalculated from the package, never trusted from the form.
     *
     * @return array<string, float>
     */
    public function money(): array
    {
        $price = $this->productPrice();
        $quantity = $this->integer('quantity');

        return [
            'total_price' => round((float) $price->price * $quantity, 2),
            'user_commission_total' => round((float) $price->user_commission * $quantity, 2),
            'admin_commission_total' => round((float) $price->admin_commission * $quantity, 2),
        ];
    }
}
