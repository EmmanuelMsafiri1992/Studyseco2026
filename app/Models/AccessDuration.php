<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessDuration extends Model
{
    protected $fillable = [
        'name',
        'description',
        'days',
        'price',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'days' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Scope for active durations
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    // Get formatted price
    public function getFormattedPriceAttribute()
    {
        return 'MWK ' . number_format($this->price, 2);
    }

    // Get duration display
    public function getDurationDisplayAttribute()
    {
        if ($this->days >= 365) {
            $years = intval($this->days / 365);
            return $years === 1 ? '1 Year' : $years . ' Years';
        } elseif ($this->days >= 30) {
            $months = intval($this->days / 30);
            return $months === 1 ? '1 Month' : $months . ' Months';
        } else {
            return $this->days === 1 ? '1 Day' : $this->days . ' Days';
        }
    }

    // Get months (calculated from days for backward compatibility)
    public function getMonthsAttribute()
    {
        return intval($this->days / 30);
    }
}
