<?php

namespace App\Http\Requests\Forum;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');
        return $post && $post->canBeEditedBy(auth()->user());
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:100000'],
            'title_vi' => ['nullable', 'string', 'max:255'],
            'content_vi' => ['nullable', 'string', 'max:100000'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => __('Title is required'),
            'title.max' => __('Title cannot exceed 255 characters'),
            'content.required' => __('Content is required'),
            'content.max' => __('Content cannot exceed 100,000 characters'),
        ];
    }
}
