<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Every account created here is a customer. The role is never accepted
     * from the form, so it cannot be escalated by tampering with the request.
     */
    protected function prepareForValidation(): void
    {
        $this->request->remove('role');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        // The super admin's own record is password-only.
        if ($user?->isAdmin()) {
            return [
                'password' => ['required', Password::min(8)->letters()->numbers()],
            ];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'password' => [
                $user ? 'nullable' : 'required',
                Password::min(8)->letters()->numbers(),
            ],
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
            'email.unique' => 'Another account already uses this email.',
            'password.required' => 'Set a password so you can share it with the user.',
        ];
    }
}
