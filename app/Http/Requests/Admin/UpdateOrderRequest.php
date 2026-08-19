<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
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
            'status' => ['required', Rule::in(Order::statuses())],

            // Only meaningful for the Post Date status, and required there.
            'post_date' => [
                Rule::requiredIf(fn () => $this->input('status') === 'post_date'),
                'nullable',
                'date',
            ],

            'notes' => ['nullable', 'string', 'max:5000'],
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
            'post_date.required' => 'Enter the date the customer will pay.',
        ];
    }
}
