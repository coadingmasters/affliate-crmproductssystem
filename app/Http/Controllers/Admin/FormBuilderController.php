<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FormBuilderController extends Controller
{
    /**
     * Show the drag and drop builder.
     */
    public function index(): View
    {
        return view('admin.form-builder.index', [
            'fields' => FormField::orderBy('sort_order')->get(),
            'types' => FormField::TYPES,
        ]);
    }

    /**
     * Render the live form exactly as a customer sees it.
     *
     * The storefront itself redirects admins away, so preview needs its own
     * route inside the admin area.
     */
    public function preview(): View
    {
        return view('admin.form-builder.preview', [
            'fields' => FormField::visible()->get(),
            'products' => \App\Models\Product::active()->orderBy('name')->get(),
        ]);
    }

    /**
     * Save the whole layout in one go.
     *
     * The builder posts the complete field list, so this replaces the saved
     * layout rather than patching it field by field.
     */
    public function save(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.id' => ['nullable', 'integer', 'exists:form_fields,id'],
            'fields.*.key' => ['nullable', 'string', 'max:64'],
            'fields.*.type' => ['required', Rule::in(array_keys(FormField::TYPES))],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.placeholder' => ['nullable', 'string', 'max:255'],
            'fields.*.help_text' => ['nullable', 'string', 'max:255'],
            'fields.*.is_required' => ['required', 'boolean'],
            'fields.*.width' => ['required', Rule::in(['half', 'full'])],
            'fields.*.options' => ['nullable', 'array'],
            'fields.*.options.*' => ['nullable', 'string', 'max:120'],
        ]);

        // Every system field must survive, since order columns depend on them.
        $submittedIds = collect($data['fields'])->pluck('id')->filter()->all();
        $missing = FormField::where('is_system', true)->whereNotIn('id', $submittedIds)->pluck('label');

        if ($missing->isNotEmpty()) {
            return response()->json([
                'message' => 'These built in fields cannot be removed: '.$missing->join(', ').'.',
            ], 422);
        }

        DB::transaction(function () use ($data) {
            $keptIds = [];

            foreach ($data['fields'] as $order => $row) {
                $existing = ! empty($row['id']) ? FormField::find($row['id']) : null;

                $attributes = [
                    'type' => $existing?->is_system ? $existing->type : $row['type'],
                    'label' => $row['label'],
                    'placeholder' => $row['placeholder'] ?? null,
                    'help_text' => $row['help_text'] ?? null,
                    'is_required' => $row['is_required'],
                    'width' => $row['width'],
                    'options' => $this->cleanOptions($row['options'] ?? null),
                    'sort_order' => $order,
                    'is_active' => true,
                ];

                if ($existing) {
                    $existing->update($attributes);
                    $keptIds[] = $existing->id;

                    continue;
                }

                $field = FormField::create([
                    ...$attributes,
                    'key' => $this->uniqueKey($row['label']),
                    'is_system' => false,
                ]);

                $keptIds[] = $field->id;
            }

            // Anything the admin dragged out is removed; system fields are
            // already guaranteed to be present by the check above.
            FormField::whereNotIn('id', $keptIds)->where('is_system', false)->delete();
        });

        return response()->json([
            'message' => 'Form saved.',
            'fields' => FormField::orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Tidy the options list, dropping blanks.
     *
     * @param  array<int, string|null>|null  $options
     * @return array<int, string>|null
     */
    private function cleanOptions(?array $options): ?array
    {
        if (! $options) {
            return null;
        }

        $clean = collect($options)
            ->map(fn ($option) => trim((string) $option))
            ->filter()
            ->values()
            ->all();

        return $clean ?: null;
    }

    /**
     * Build a stable, unique machine name from the label.
     */
    private function uniqueKey(string $label): string
    {
        $base = Str::slug($label, '_') ?: 'field';
        $base = Str::limit($base, 50, '');
        $key = $base;
        $suffix = 2;

        while (FormField::where('key', $key)->exists()) {
            $key = $base.'_'.$suffix++;
        }

        return $key;
    }
}
