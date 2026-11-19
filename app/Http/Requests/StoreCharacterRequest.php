<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCharacterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $characterId = $this->route('character')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', Rule::unique('characters', 'slug')->ignore($characterId)],
            'photo' => ['nullable', 'image', 'max:2048'],
            'description' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->name),
        ]);
    }
}
