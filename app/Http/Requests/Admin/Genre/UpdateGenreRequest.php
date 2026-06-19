<?php

namespace App\Http\Requests\Admin\Genre;

use App\Models\Genre;
use Illuminate\Validation\Rule;

class UpdateGenreRequest extends GenreBaseRequest
{
    public function rules(): array
    {
        $genre = $this->route('genre');
        $id = $genre instanceof Genre ? $genre->id : $genre;
        $slugId = $genre instanceof Genre ? optional($genre->slug)->id : null;

        return [
            'name' => [
                'required',
                Rule::unique(Genre::class)->ignore($id),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('slugs', 'slug')
                    ->where(fn ($query) => $query->where('type', 'genre'))
                    ->ignore($slugId),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'cover_image_remove' => ['nullable', 'boolean'],
        ];
    }
}
