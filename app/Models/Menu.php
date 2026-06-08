<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = ['location', 'name'];

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('order');
    }

    /** Các mục gốc (không cha), đã active, kèm con active — dùng để render */
    public function rootItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MenuItem::class)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->with('activeChildren');
    }

    /** Cây menu theo location (cache vĩnh viễn, xoá khi sửa) */
    public static function tree(string $location)
    {
        return Cache::rememberForever("menu_tree_{$location}", function () use ($location) {
            $menu = static::where('location', $location)->first();
            return $menu ? $menu->rootItems()->get() : collect();
        });
    }

    public static function flushCache(?string $location = null): void
    {
        if ($location) {
            Cache::forget("menu_tree_{$location}");
            return;
        }
        foreach (static::pluck('location') as $loc) {
            Cache::forget("menu_tree_{$loc}");
        }
    }
}
