<?php

namespace App\Http\Requests\Admin\Chapter;

use App\Models\Chapter;
use Illuminate\Validation\Rule;

class UpdateChapterRequest extends ChapterBaseRequest
{
    public function rules(): array
    {
        $id = $this->route('chapter')->id;
        return [
            'title' => 'required',
            'number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique(Chapter::class)->ignore($id)
                    ->where('article_id', $this->route('article')->id),
            ],
            'content' => 'required',
            'credit_cost' => ['nullable', 'integer', 'min:0'],
            'published_at' => ['nullable', 'string', function ($attr, $value, $fail) {
                if (trim((string) $value) !== '' && \App\Models\Chapter::parsePublishedAt($value) === null) {
                    $fail('Định dạng lịch đăng không hợp lệ. Dùng YYYY-MM-DD HH:MM (vd 2026-06-15 08:00).');
                }
            }],
        ];
    }
}
