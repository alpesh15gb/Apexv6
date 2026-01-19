<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'date',
        'punch_in_time',
        'punch_in_latitude',
        'punch_in_longitude',
        'punch_in_photo',
        'punch_in_device',
        'punch_in_ip',
        'punch_out_time',
        'punch_out_latitude',
        'punch_out_longitude',
        'punch_out_photo',
        'punch_out_device',
        'punch_out_ip',
        'total_hours',
        'overtime_hours',
        'break_duration_minutes',
        'late_minutes',
        'early_departure_minutes',
        'status',
        'remarks',
        'approved_by',
        'approved_at',
        'synced_from_device',
        'raw_punch_data',
    ];

    protected $casts = [
        'date' => 'date',
        'punch_in_time' => 'datetime:H:i:s',
        'punch_out_time' => 'datetime:H:i:s',
        'punch_in_latitude' => 'decimal:8',
        'punch_in_longitude' => 'decimal:8',
        'punch_out_latitude' => 'decimal:8',
        'punch_out_longitude' => 'decimal:8',
        'total_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'break_duration_minutes' => 'integer',
        'late_minutes' => 'integer',
        'early_departure_minutes' => 'integer',
        'approved_at' => 'datetime',
        'synced_from_device' => 'boolean',
    ];

    // Status constants
    const STATUS_PRESENT = 'present';
    const STATUS_ABSENT = 'absent';
    const STATUS_HALF_DAY = 'half_day';
    const STATUS_LATE = 'late';
    const STATUS_LEAVE = 'leave';
    const STATUS_HOLIDAY = 'holiday';
    const STATUS_WEEK_OFF = 'week_off';
    const STATUS_ON_DUTY = 'on_duty';

    // ========================
    // Relationships
    // ========================
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ========================
    // Scopes
    // ========================
    public function scopeForDate($query, Carbon $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForDateRange($query, Carbon $from, Carbon $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    public function scopePresent($query)
    {
        return $query->where('status', self::STATUS_PRESENT);
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', self::STATUS_ABSENT);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSyncedFromDevice($query)
    {
        return $query->where('synced_from_device', true);
    }

    // ========================
    // Methods
    // ========================
    public function hasPunchedIn(): bool
    {
        return !is_null($this->punch_in_time);
    }

    public function hasPunchedOut(): bool
    {
        return !is_null($this->punch_out_time);
    }

    public function calculateTotalHours(): ?float
    {
        if (!$this->punch_in_time || !$this->punch_out_time) {
            return null;
        }

        $punchIn = Carbon::parse($this->punch_in_time);
        $punchOut = Carbon::parse($this->punch_out_time);

        $totalMinutes = $punchIn->diffInMinutes($punchOut);
        $workingMinutes = $totalMinutes - $this->break_duration_minutes;

        return max(0, round($workingMinutes / 60, 2));
    }

    public function updateStatus(): void
    {
        if (!$this->user || !$this->user->shift) {
            return;
        }

        $shift = $this->user->shift;

        if (!$this->hasPunchedIn()) {
            $this->status = self::STATUS_ABSENT;
        } elseif ($shift->isHalfDay(Carbon::parse($this->punch_in_time), $this->date)) {
            $this->status = self::STATUS_HALF_DAY;
        } elseif ($shift->isLate(Carbon::parse($this->punch_in_time), $this->date)) {
            $this->status = self::STATUS_LATE;
        } else {
            $this->status = self::STATUS_PRESENT;
        }

        $this->save();
    }

    // ========================
    // Accessors
    // ========================
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PRESENT => 'success',
            self::STATUS_ABSENT => 'error',
            self::STATUS_HALF_DAY => 'warning',
            self::STATUS_LATE => 'warning',
            self::STATUS_LEAVE => 'info',
            self::STATUS_HOLIDAY => 'primary',
            self::STATUS_WEEK_OFF => 'neutral',
            self::STATUS_ON_DUTY => 'accent',
            default => 'neutral',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PRESENT => 'Present',
            self::STATUS_ABSENT => 'Absent',
            self::STATUS_HALF_DAY => 'Half Day',
            self::STATUS_LATE => 'Late',
            self::STATUS_LEAVE => 'On Leave',
            self::STATUS_HOLIDAY => 'Holiday',
            self::STATUS_WEEK_OFF => 'Week Off',
            self::STATUS_ON_DUTY => 'On Duty',
            default => 'Unknown',
        };
    }

    public function getFormattedPunchInAttribute(): ?string
    {
        return $this->punch_in_time
            ? Carbon::parse($this->punch_in_time)->format('h:i A')
            : null;
    }

    public function getFormattedPunchOutAttribute(): ?string
    {
        return $this->punch_out_time
            ? Carbon::parse($this->punch_out_time)->format('h:i A')
            : null;
    }
}
