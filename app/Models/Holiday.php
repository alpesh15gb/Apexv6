<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'type',
        'location_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];

    // Type constants
    const TYPE_PUBLIC = 'public';
    const TYPE_OPTIONAL = 'optional';
    const TYPE_RESTRICTED = 'restricted';

    // ========================
    // Relationships
    // ========================
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    // ========================
    // Scopes
    // ========================
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('type', self::TYPE_PUBLIC);
    }

    public function scopeOptional($query)
    {
        return $query->where('type', self::TYPE_OPTIONAL);
    }

    public function scopeForLocation($query, ?int $locationId)
    {
        return $query->where(function ($q) use ($locationId) {
            $q->whereNull('location_id');
            if ($locationId) {
                $q->orWhere('location_id', $locationId);
            }
        });
    }

    public function scopeForDate($query, Carbon $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('date', $year);
    }

    public function scopeUpcoming($query, int $days = 30)
    {
        return $query->whereBetween('date', [now(), now()->addDays($days)]);
    }

    // ========================
    // Methods
    // ========================
    public static function isHoliday(Carbon $date, ?int $locationId = null): bool
    {
        return static::active()
            ->forLocation($locationId)
            ->forDate($date)
            ->exists();
    }

    public function isGlobal(): bool
    {
        return is_null($this->location_id);
    }

    // ========================
    // Accessors
    // ========================
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_PUBLIC => 'Public',
            self::TYPE_OPTIONAL => 'Optional',
            self::TYPE_RESTRICTED => 'Restricted',
            default => 'Unknown',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_PUBLIC => 'success',
            self::TYPE_OPTIONAL => 'info',
            self::TYPE_RESTRICTED => 'warning',
            default => 'neutral',
        };
    }
}
