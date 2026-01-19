<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'latitude',
        'longitude',
        'geo_fence_radius',
        'timezone',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'geo_fence_radius' => 'integer',
        'is_active' => 'boolean',
    ];

    // ========================
    // Relationships
    // ========================
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function holidays(): HasMany
    {
        return $this->hasMany(Holiday::class);
    }

    // ========================
    // Scopes
    // ========================
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ========================
    // Methods
    // ========================
    public function isWithinGeofence(float $lat, float $lng): bool
    {
        if (!$this->latitude || !$this->longitude) {
            return true; // No geofence set
        }

        $distance = $this->calculateDistance($lat, $lng);
        return $distance <= $this->geo_fence_radius;
    }

    /**
     * Calculate distance in meters using Haversine formula
     */
    protected function calculateDistance(float $lat, float $lng): float
    {
        $earthRadius = 6371000; // meters

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($lat);
        $lonTo = deg2rad($lng);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($latFrom) * cos($latTo) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address,
            $this->city,
            $this->state,
            $this->pincode,
            $this->country,
        ])->filter()->implode(', ');
    }
}
