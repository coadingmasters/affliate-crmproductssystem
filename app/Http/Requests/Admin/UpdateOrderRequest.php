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
        $rules = [
            'status' => ['required', Rule::in(Order::statuses())],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];

        // Post Date, Sale and Going to Return each carry their own date,
        // required when that status is the one being set.
        foreach (Order::STATUS_DATES as $status => $meta) {
            $rules[$meta['column']] = [
                Rule::requiredIf(fn () => $this->input('status') === $status),
                'nullable',
                'date',
            ];
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
            'post_date.required' => 'Enter the date the customer will pay.',
            'sale_date.required' => 'Enter the date the sale was made.',
            'return_date.required' => 'Enter the date it is going back.',
        ];
    }
}
