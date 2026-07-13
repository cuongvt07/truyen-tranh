<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name', 'name_en', 'sort_order'];

    public function getDisplayNameAttribute(): string
    {
        // Thị trường không phải tiếng Anh: giữ tên bản địa.
        if (app()->getLocale() !== 'en') {
            return $this->name;
        }

        // Site tiếng Anh: ưu tiên name_en; nếu admin để trống thì fallback nhãn
        // tiếng Anh có sẵn (theo id) thay vì rơi về tên tiếng Việt.
        if (!empty($this->name_en)) {
            return $this->name_en;
        }

        return [
            1 => 'Chinese', 2 => 'Japanese', 3 => 'Korean',
            4 => 'Vietnamese', 5 => 'Western', 6 => 'Other',
        ][$this->id] ?? $this->name;
    }
}
