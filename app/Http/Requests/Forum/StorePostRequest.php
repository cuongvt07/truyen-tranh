<?php

namespace App\Http\Requests\Forum;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:forum_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:100000'],
            'title_vi' => ['nullable', 'string', 'max:255'],
            'content_vi' => ['nullable', 'string', 'max:100000'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => __('Please select a category'),
            'category_id.exists' => __('The selected category is invalid'),
            'title.required' => __('Title is required'),
            'title.max' => __('Title cannot exceed 255 characters'),
            'content.required' => __('Content is required'),
            'content.max' => __('Content cannot exceed 100,000 characters'),
        ];
    }
}
