<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'title',
        'content',
        'meta_data',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'meta_data' => 'array',
        'is_active' => 'boolean',
    ];

    public static function getByKey($key)
    {
        return static::where('key', $key)->where('is_active', true)->first();
    }

    public static function getAllActive()
    {
        return static::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('key');
    }
}