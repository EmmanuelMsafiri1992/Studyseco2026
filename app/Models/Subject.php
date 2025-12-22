<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'department',
        'grade_level',
        'credits',
        'teacher_name',
        'cover_image',
        'is_active',
        'is_compulsory',
        'subject_type',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_compulsory' => 'boolean',
    ];

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class)->orderBy('order_index');
    }

    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, Topic::class);
    }

    public function publishedLessons()
    {
        return $this->lessons()->where('is_published', true)->orderBy('order_index');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    public function chatGroups(): HasMany
    {
        return $this->hasMany(ChatGroup::class);
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
