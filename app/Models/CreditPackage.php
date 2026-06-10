<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditPackage extends Model
{
    protected $fillable = [
        'name',
        'package_type',
        'coins',
        'subscription_days',
        'daily_credits',
        'price_vnd',
        'price_usd',
        'price_display',
        'icon',
        'is_featured',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active'   => 'boolean',
        'price_usd'   => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('coins');
    }

    public function scopeCredits($query)
    {
        return $query->where('package_type', 'credit');
    }

    public function scopeSubscriptions($query)
    {
        return $query->where('package_type', 'subscription');
    }

    public function isSubscription(): bool
    {
        return $this->package_type === 'subscription';
    }

    public function getPriceVndFormattedAttribute(): string
    {
        return number_format($this->price_vnd, 0, ',', '.') . 'đ';
    }

    public function getPriceUsdFormattedAttribute(): string
    {
        return '$' . number_format($this->price_usd, 2);
    }

    public function getDisplayPriceAttribute(): string
    {
        return $this->price_usd > 0 ? '$' . number_format($this->price_usd, 2) : $this->price_vnd_formatted;
    }
}
