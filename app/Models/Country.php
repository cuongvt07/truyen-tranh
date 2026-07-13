<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name', 'name_en', 'sort_order'];

    public function getDisplayNameAttribute(): string
    {
        // Chỉ thị trường tiếng Việt dùng tên tiếng Việt (cột name).
        if (app()->getLocale() === 'vi') {
            return $this->name;
        }

        // Các thị trường khác (fr, en, ...) chưa có bản dịch riêng -> dùng name_en
        // (tiếng Anh); nếu admin để trống thì fallback nhãn tiếng Anh có sẵn theo id.
        if (!empty($this->name_en)) {
            return $this->name_en;
        }

        return [
            1 => 'Chinese', 2 => 'Japanese', 3 => 'Korean',
            4 => 'Vietnamese', 5 => 'Western', 6 => 'Other',
        ][$this->id] ?? $this->name;
    }
}
