<?php

namespace App\Http\Requests\Admin\Genre;

use App\Models\Genre;
use Illuminate\Validation\Rule;

class StoreGenreRequest extends GenreBaseRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                Rule::unique(Genre::class),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('slugs', 'slug')->where(fn ($query) => $query->where('type', 'genre')),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }
}
