<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'image_path', 'image_url', 'script_code', 'link',
        'display_mode', 'placement', 'pages',
        'frequency', 'frequency_value', 'delay_seconds',
        'after_click', 'cooldown_seconds',
        'chapter_start', 'chapter_interval', 'chapter_inline_count',
        'chapter_inline_first_after', 'chapter_inline_every',
        'hide_for_vip', 'require_click',
        'priority', 'is_active', 'start_at', 'end_at',
    ];

    protected $casts = [
        'pages'            => 'array',
        'is_active'        => 'boolean',
        'hide_for_vip'     => 'boolean',
        'require_click'    => 'boolean',
        'frequency_value'  => 'integer',
        'delay_seconds'    => 'integer',
        'cooldown_seconds' => 'integer',
        'chapter_start'    => 'integer',
        'chapter_interval' => 'integer',
        'chapter_inline_count' => 'integer',
        'chapter_inline_first_after' => 'integer',
        'chapter_inline_every' => 'integer',
        'priority'         => 'integer',
        'start_at'         => 'datetime',
        'end_at'           => 'datetime',
    ];

    /** Các dạng chạy hỗ trợ */
    public const MODES = [
        'banner'         => 'Banner cố định',
        'click_anywhere' => 'Click bất kỳ đâu',
        'popup'          => 'Popup / che màn hình',
        'chapter'        => 'Chèn khi đọc chapter',
        'footer'         => 'Footer script (cuối trang)',
        'head'           => 'Head script (trong <head>)',
    ];

    /** Vị trí slot cho banner */
    public const PLACEMENTS = [
        'top'         => 'Đầu trang (thanh trên)',
        'bottom'      => 'Cuối trang (thanh dưới)',
        'float_left'  => 'Nổi góc trái',
        'float_right' => 'Nổi góc phải',
        'in_content'  => 'Giữa nội dung',
        'sidebar'     => 'Cột bên (sidebar)',
    ];

    /** Các loại trang có thể chèn */
    public const PAGES = [
        'all'     => 'Toàn site',
        'home'    => 'Trang chủ',
        'article' => 'Chi tiết truyện',
        'chapter' => 'Đọc chapter',
        'genre'   => 'Thể loại',
        'catalog' => 'Catalog / tìm kiếm',
    ];

    /** Tần suất lặp lại */
    public const FREQUENCIES = [
        'every_load'    => 'Mỗi lần tải trang',
        'once_session'  => '1 lần / phiên',
        'every_n_views' => 'Mỗi N lượt xem trang',
    ];

    /** Hành vi sau khi user đã click & link đã chạy */
    public const AFTER_CLICKS = [
        'none'         => 'Không đổi (theo tần suất)',
        'stop_session' => 'Ẩn hết trong phiên này',
        'cooldown'     => 'Chờ N giây rồi chạy lại',
    ];

    /** Ảnh hiệu lực: ưu tiên URL ngoài, sau đó ảnh upload */
    public function getImageAttribute(): ?string
    {
        if (!empty($this->attributes['image_url'])) {
            return $this->attributes['image_url'];
        }
        if (!empty($this->attributes['image_path'])) {
            return asset('storage/' . $this->attributes['image_path']);
        }
        return null;
    }

    public function items(): HasMany
    {
        return $this->hasMany(AdItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function activeItems(): HasMany
    {
        return $this->items()->where('is_active', true);
    }

    /** Còn trong khung thời gian lên lịch không */
    public function isLive(): bool
    {
        $now = now();
        if ($this->start_at && $this->start_at->gt($now)) {
            return false;
        }
        if ($this->end_at && $this->end_at->lt($now)) {
            return false;
        }
        return true;
    }

    /** Có áp dụng cho loại trang này không */
    public function matchesPage(?string $page): bool
    {
        $pages = $this->pages ?: [];
        if (in_array('all', $pages, true)) {
            return true;
        }
        return $page !== null && in_array($page, $pages, true);
    }

    /** Tất cả quảng cáo đang bật (cache, xoá khi sửa) */
    public static function activeAll(): Collection
    {
        return Cache::rememberForever('ads_active_all', function () {
            return static::query()
                ->with('items')
                ->where('is_active', true)
                ->orderBy('priority')
                ->orderBy('id')
                ->get();
        });
    }

    /**
     * Quảng cáo cho 1 loại trang, gom theo dạng chạy.
     * Lọc thời gian + trang ở PHP để lịch hẹn luôn chính xác (không phụ thuộc cache).
     */
    public static function forPageGrouped(?string $page): Collection
    {
        return static::activeAll()
            ->filter(fn (self $ad) => $ad->isLive() && $ad->matchesPage($page))
            ->groupBy('display_mode');
    }

    public static function flushCache(): void
    {
        Cache::forget('ads_active_all');
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }
}
