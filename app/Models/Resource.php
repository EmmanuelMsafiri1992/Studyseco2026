<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'category',
        'subject_id',
        'grade_level',
        'exam_board',
        'year',
        'file_path',
        'file_type',
        'file_size',
        'thumbnail',
        'access_permissions',
        'is_active',
        'is_protected',
        'protection_settings',
        'view_count',
        'download_attempts',
    ];

    protected $casts = [
        'access_permissions' => 'array',
        'is_active' => 'boolean',
        'is_protected' => 'boolean',
        'protection_settings' => 'array',
    ];

    /**
     * Get the subject that owns the resource.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Increment view count
     */
    public function incrementView()
    {
        $this->increment('view_count');
    }

    /**
     * Increment download attempts (for security tracking)
     */
    public function incrementDownloadAttempt()
    {
        $this->increment('download_attempts');
    }

    /**
     * Check if user can access this resource
     */
    public function canBeAccessedBy(User $user): bool
    {
        if (!$this->is_active) return false;

        $permissions = $this->access_permissions ?? [];
        
        // If no specific permissions set, allow all authenticated users
        if (empty($permissions)) return true;

        // Check user role (simple role field instead of relationships)
        $userRole = $user->role ?? 'guest';
        if (in_array($userRole, $permissions['roles'] ?? [])) {
            return true;
        }

        // Check specific user IDs
        if (in_array($user->id, $permissions['users'] ?? [])) {
            return true;
        }

        return false;
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSizeHumanAttribute(): string
    {
        if (!$this->file_size) return 'Unknown';
        
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Scope for active resources
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for specific subject
     */
    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope for specific grade level
     */
    public function scopeForGrade($query, $gradeLevel)
    {
        return $query->where('grade_level', $gradeLevel);
    }

    /**
     * Get resources by category
     */
    public static function getByCategory($category = null)
    {
        $query = self::active()->with('subject');
        
        if ($category) {
            $query->where('category', $category);
        }
        
        return $query->orderBy('title')->get();
    }

    /**
     * Get past papers for a specific year and subject
     */
    public static function getPastPapers($year = null, $subjectId = null, $examBoard = null)
    {
        $query = self::active()->ofType('past_paper')->with('subject');
        
        if ($year) {
            $query->where('year', $year);
        }
        
        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }
        
        if ($examBoard) {
            $query->where('exam_board', $examBoard);
        }
        
        return $query->orderBy('year', 'desc')->orderBy('title')->get();
    }
}