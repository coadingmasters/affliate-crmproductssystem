<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'key', 'type', 'label', 'placeholder', 'help_text',
    'is_required', 'is_active', 'is_system', 'width', 'options', 'sort_order',
])]
class FormField extends Model
{
    /**
     * The field types an admin can drag onto the form.
     *
     * @var array<string, array{label: string, icon: string, hint: string, has_options: bool}>
     */
    public const TYPES = [
        'text' => ['label' => 'Text', 'icon' => 'M4 6h16M4 12h10', 'hint' => 'Single line, e.g. a name', 'has_options' => false],
        'textarea' => ['label' => 'Paragraph', 'icon' => 'M4 6h16M4 10h16M4 14h12M4 18h8', 'hint' => 'Multi line, e.g. an address', 'has_options' => false],
        'email' => ['label' => 'Email', 'icon' => 'M3 8l9 6 9-6M3 8v8a2 2 0 002 2h14a2 2 0 002-2V8M3 8a2 2 0 012-2h14a2 2 0 012 2', 'hint' => 'Validated email address', 'has_options' => false],
        'tel' => ['label' => 'Phone', 'icon' => 'M3 5a2 2 0 012-2h2.6a1 1 0 01.97.75l1 3.5a1 1 0 01-.3 1L8 10a13 13 0 006 6l1.7-1.3a1 1 0 011-.2l3.5 1a1 1 0 01.8 1V19a2 2 0 01-2 2A16 16 0 013 5z', 'hint' => 'Phone number', 'has_options' => false],
        'number' => ['label' => 'Number', 'icon' => 'M7 4v16M17 4v16M4 8h16M4 16h16', 'hint' => 'Numeric only', 'has_options' => false],
        'date' => ['label' => 'Date', 'icon' => 'M8 7V3m8 4V3M4 11h16M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'hint' => 'Date picker, e.g. date of birth', 'has_options' => false],
        'select' => ['label' => 'Dropdown', 'icon' => 'M8 9l4 4 4-4M4 5h16v14H4z', 'hint' => 'Pick one from a list', 'has_options' => true],
        'radio' => ['label' => 'Radio', 'icon' => 'M12 12m-3 0a3 3 0 106 0a3 3 0 10-6 0M12 21a9 9 0 100-18 9 9 0 000 18z', 'hint' => 'Pick one, all shown', 'has_options' => true],
        'checkbox' => ['label' => 'Checkbox', 'icon' => 'M9 12l2 2 4-4M4 5h16v14H4z', 'hint' => 'Single yes/no tick', 'has_options' => false],
        'file' => ['label' => 'File upload', 'icon' => 'M7 16a4 4 0 01-.9-7.9 5 5 0 019.7-1.7A4.5 4.5 0 1117 16H7zm5-6v6m0-6l-2 2m2-2l2 2', 'hint' => 'Image or document', 'has_options' => false],
    ];

    /**
     * Fields that map to real order columns and cannot be removed.
     *
     * @var array<int, string>
     */
    public const SYSTEM_KEYS = ['full_name', 'email', 'phone', 'address', 'product', 'package', 'quantity'];

    /**
     * Keys rendered by their own dedicated markup rather than a plain input.
     *
     * @var array<int, string>
     */
    public const SPECIAL_KEYS = ['product', 'package', 'quantity'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'is_system' => 'boolean',
            'options' => 'array',
        ];
    }

    /**
     * Only the fields that should appear on the form, in order.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Whether this field is one of the product/package/quantity blocks.
     */
    public function isSpecial(): bool
    {
        return in_array($this->key, self::SPECIAL_KEYS, true);
    }

    /**
     * Whether the answer lives in its own order column.
     */
    public function isColumn(): bool
    {
        return $this->is_system && ! $this->isSpecial();
    }

    /**
     * The validation rules this field contributes.
     *
     * @return array<int, string>
     */
    public function rules(): array
    {
        $rules = [$this->is_required ? 'required' : 'nullable'];

        $rules[] = match ($this->type) {
            'email' => 'email',
            'number' => 'numeric',
            'date' => 'date',
            'file' => 'file',
            'checkbox' => 'boolean',
            default => 'string',
        };

        if ($this->type === 'file') {
            $rules[] = 'max:4096';
        } elseif ($this->type === 'textarea') {
            $rules[] = 'max:2000';
        } elseif (! in_array($this->type, ['number', 'date', 'checkbox'], true)) {
            $rules[] = 'max:255';
        }

        return $rules;
    }
}
