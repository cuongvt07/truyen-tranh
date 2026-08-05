<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class HomeBlock extends Model
{
    use HasFactory;

    /** Khóa cache payload trang chủ; đổi hậu tố khi đổi cấu trúc dữ liệu. */
    public const CACHE_KEY = 'home:index:alphanovel:v6';

    /** Nguồn truyện cho một khối. Key lưu DB, value là nhãn hiển thị ở admin. */
    public const SOURCES = [
        'trending'   => 'Đang thịnh hành',
        'hot'        => 'Nổi bật (admin tick Hot)',
        'new_update' => 'Mới cập nhật',
        'latest'     => 'Truyện mới đăng',
        'exclusive'  => 'Độc quyền (user đăng)',
        'genre'      => 'Theo thể loại',
    ];

    public const VARIANTS = [
        'rail'     => 'Hàng ngang (rail)',
        'trending' => 'Lưới có số thứ tự',
    ];

    protected $fillable = [
        'title', 'source', 'genre_id', 'limit',
        'variant', 'show_see_all', 'url', 'order', 'is_active',
    ];

    protected $casts = [
        'show_see_all' => 'boolean',
        'is_active'    => 'boolean',
        'limit'        => 'integer',
        'order'        => 'integer',
    ];

    protected static function booted(): void
    {
        // Sửa khối trong admin phải thấy ngay ngoài trang chủ, không đợi TTL cache.
        static::saved(fn () => self::flushCache());
        static::deleted(fn () => self::flushCache());
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    public function getSourceLabelAttribute(): string
    {
        return self::SOURCES[$this->source] ?? $this->source;
    }
}
