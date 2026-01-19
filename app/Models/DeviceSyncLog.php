<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceSyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_identifier',
        'device_name',
        'sync_started_at',
        'sync_completed_at',
        'records_fetched',
        'records_synced',
        'records_failed',
        'records_skipped',
        'status',
        'error_log',
        'sync_details',
    ];

    protected $casts = [
        'sync_started_at' => 'datetime',
        'sync_completed_at' => 'datetime',
        'records_fetched' => 'integer',
        'records_synced' => 'integer',
        'records_failed' => 'integer',
        'records_skipped' => 'integer',
        'sync_details' => 'array',
    ];

    // Status constants
    const STATUS_RUNNING = 'running';
    const STATUS_SUCCESS = 'success';
    const STATUS_PARTIAL = 'partial';
    const STATUS_FAILED = 'failed';

    // ========================
    // Scopes
    // ========================
    public function scopeForDevice($query, string $deviceIdentifier)
    {
        return $query->where('device_identifier', $deviceIdentifier);
    }

    public function scopeRunning($query)
    {
        return $query->where('status', self::STATUS_RUNNING);
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    // ========================
    // Methods
    // ========================
    public function markSuccess(): void
    {
        $this->update([
            'status' => self::STATUS_SUCCESS,
            'sync_completed_at' => now(),
        ]);
    }

    public function markFailed(string $error): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'sync_completed_at' => now(),
            'error_log' => $error,
        ]);
    }

    public function markPartial(string $error = null): void
    {
        $this->update([
            'status' => self::STATUS_PARTIAL,
            'sync_completed_at' => now(),
            'error_log' => $error,
        ]);
    }

    public function incrementSynced(int $count = 1): void
    {
        $this->increment('records_synced', $count);
    }

    public function incrementFailed(int $count = 1): void
    {
        $this->increment('records_failed', $count);
    }

    public function incrementSkipped(int $count = 1): void
    {
        $this->increment('records_skipped', $count);
    }

    // ========================
    // Accessors
    // ========================
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_RUNNING => 'info',
            self::STATUS_SUCCESS => 'success',
            self::STATUS_PARTIAL => 'warning',
            self::STATUS_FAILED => 'error',
            default => 'neutral',
        };
    }

    public function getSyncDurationAttribute(): ?string
    {
        if (!$this->sync_completed_at) {
            return null;
        }

        return $this->sync_started_at->diffForHumans($this->sync_completed_at, true);
    }

    public function getSuccessRateAttribute(): ?float
    {
        if ($this->records_fetched === 0) {
            return null;
        }

        return round(($this->records_synced / $this->records_fetched) * 100, 2);
    }
}
