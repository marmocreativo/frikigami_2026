<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreAnimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $animeId = $this->route('anime')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', Rule::unique('animes', 'slug')->ignore($animeId)],
            'synopsis' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', Rule::in(['upcoming', 'ongoing', 'finished'])],
            'season_id' => ['nullable', 'exists:seasons,id'],
            'genres' => ['nullable', 'array'],
            'genres.*' => ['exists:genres,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->title),
        ]);
    }
}