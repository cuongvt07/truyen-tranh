<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id', 'parent_id', 'label', 'label_key',
        'url', 'icon', 'target', 'order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order'     => 'integer',
    ];

    public function menu(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    public function activeChildren(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->children()->where('is_active', true);
    }

    /** Nhãn hiển thị: ưu tiên khóa dịch nếu tồn tại, fallback về label text */
    public function getDisplayLabelAttribute(): string
    {
        if ($this->label_key && Lang::has($this->label_key)) {
            return __($this->label_key);
        }
        return (string) $this->label;
    }

    /** URL render: path tương đối ('/x') hoặc URL tuyệt đối ('http...') đều dùng trực tiếp */
    public function getHrefAttribute(): string
    {
        $url = trim((string) $this->url);
        return $url === '' ? '#' : $url;
    }
}
