<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SecondarySchool extends Model
{
    protected $fillable = [
        'name',
        'code',
        'region',
        'district',
        'address',
        'phone',
        'email',
        'capacity',
        'facilities',
        'is_active',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'facilities' => 'array',
        'is_active' => 'boolean',
        'capacity' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function studentSelections(): HasMany
    {
        return $this->hasMany(StudentSchoolSelection::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getAvailableSlotsAttribute()
    {
        $confirmed = $this->studentSelections()->where('status', 'confirmed')->count();
        return max(0, $this->capacity - $confirmed);
    }

    public function getFormattedAddressAttribute()
    {
        return "{$this->address}, {$this->district}, {$this->region}";
    }
}
