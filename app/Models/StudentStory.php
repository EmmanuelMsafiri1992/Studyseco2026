<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentStory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'country_flag',
        'current_status',
        'story',
        'achievements',
        'msce_credits',
        'avatar_color_from',
        'avatar_color_to',
        'achievement_icon',
        'is_featured',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'achievements' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getInitialsAttribute()
    {
        $names = explode(' ', $this->name);
        $initials = '';
        foreach (array_slice($names, 0, 2) as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
        }
        return $initials;
    }

    public static function getFeatured()
    {
        return static::where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->get();
    }
}