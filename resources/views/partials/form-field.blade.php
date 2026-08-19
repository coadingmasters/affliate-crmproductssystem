@php
    /** @var \App\Models\FormField $field */
    $name = $field->key;
    $hasError = $errors->has($name);
    $base = 'field w-full rounded-xl border bg-elevated px-3.5 py-2.5 text-sm text-ink placeholder-muted';
    $cls = $base.' '.($hasError ? 'border-danger' : 'border-line');
    $value = old($name, $prefill ?? '');
@endphp

<div class="{{ $field->width === 'full' ? 'sm:col-span-2' : '' }}">
    @unless ($field->type === 'checkbox')
        <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-ink">
            {{ $field->label }}
            @if ($field->is_required)<span class="text-danger">*</span>@endif
        </label>
    @endunless

    @switch($field->type)
        @case('textarea')
            <textarea name="{{ $name }}" id="{{ $name }}" rows="{{ $field->width === 'full' ? 3 : 1 }}"
                      @required($field->is_required)
                      placeholder="{{ $field->placeholder }}"
                      class="{{ $cls }} resize-none leading-relaxed">{{ $value }}</textarea>
            @break

        @case('select')
            <select name="{{ $name }}" id="{{ $name }}" @required($field->is_required) class="{{ $cls }}">
                <option value="">{{ $field->placeholder ?: 'Choose an option' }}</option>
                @foreach ($field->options ?? [] as $option)
                    <option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>
                @endforeach
            </select>
            @break

        @case('radio')
            <div class="space-y-2">
                @foreach ($field->options ?? [] as $i => $option)
                    <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border {{ $hasError ? 'border-danger' : 'border-line' }} bg-elevated px-3.5 py-2.5">
                        <input type="radio" name="{{ $name }}" value="{{ $option }}"
                               @checked($value === $option) @required($field->is_required && $i === 0)
                               class="h-4 w-4 border-line text-brand focus:ring-brand/30">
                        <span class="text-sm text-ink">{{ $option }}</span>
                    </label>
                @endforeach
            </div>
            @break

        @case('checkbox')
            <label class="flex cursor-pointer items-start gap-2.5 rounded-xl border {{ $hasError ? 'border-danger' : 'border-line' }} bg-elevated px-3.5 py-2.5">
                <input type="checkbox" name="{{ $name }}" value="1" @checked($value)
                       @required($field->is_required)
                       class="mt-0.5 h-4 w-4 rounded border-line text-brand focus:ring-brand/30">
                <span class="text-sm text-ink">
                    {{ $field->label }}
                    @if ($field->is_required)<span class="text-danger">*</span>@endif
                </span>
            </label>
            @break

        @case('file')
            <input type="file" name="{{ $name }}" id="{{ $name }}" @required($field->is_required)
                   class="block w-full cursor-pointer rounded-xl border {{ $hasError ? 'border-danger' : 'border-line' }} bg-elevated text-sm text-muted file:mr-4 file:cursor-pointer file:rounded-l-xl file:border-0 file:bg-brand/10 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-brand hover:file:bg-brand/20">
            @break

        @default
            <input type="{{ $field->type }}" name="{{ $name }}" id="{{ $name }}"
                   value="{{ $value }}" @required($field->is_required)
                   @if ($field->type === 'number') min="0" @endif
                   placeholder="{{ $field->placeholder }}"
                   class="{{ $cls }}">
    @endswitch

    @if ($field->help_text)
        <p class="mt-1.5 text-xs text-muted">{{ $field->help_text }}</p>
    @endif

    @error($name)
        <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
    @enderror
</div>
