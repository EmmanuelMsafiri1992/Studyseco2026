<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'name',
        'description',
        'order_index',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order_index');
    }

    public function publishedLessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->where('is_published', true)->orderBy('order_index');
    }

    public function getTotalDurationAttribute()
    {
        return $this->lessons()->sum('duration_minutes');
    }

    public function getLessonCountAttribute()
    {
        return $this->lessons()->count();
    }
}
