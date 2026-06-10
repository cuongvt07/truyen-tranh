<?php

namespace App\Http\Requests\Faq;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:10000'],
            'parent_id' => ['nullable', 'exists:faq_comments,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => __('Comment content is required'),
            'content.max' => __('Comment cannot exceed 10,000 characters'),
            'parent_id.exists' => __('The parent comment does not exist'),
        ];
    }
}
