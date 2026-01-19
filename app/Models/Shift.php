<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'grace_period_minutes',
        'break_duration_minutes',
        'late_mark_after_minutes',
        'half_day_after_minutes',
        'min_working_hours',
        'min_half_day_hours',
        'is_flexible',
        'is_night_shift',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'grace_period_minutes' => 'integer',
        'break_duration_minutes' => 'integer',
        'late_mark_after_minutes' => 'integer',
        'half_day_after_minutes' => 'integer',
        'min_working_hours' => 'integer',
        'min_half_day_hours' => 'integer',
        'is_flexible' => 'boolean',
        'is_night_shift' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ========================
    // Relationships
    // ========================
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
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
    public function getShiftStartTimeForDate(Carbon $date): Carbon
    {
        return $date->copy()->setTimeFromTimeString($this->start_time);
    }

    public function getShiftEndTimeForDate(Carbon $date): Carbon
    {
        $endTime = $date->copy()->setTimeFromTimeString($this->end_time);

        // For night shift, end time is next day
        if ($this->is_night_shift && $endTime->lessThan($this->getShiftStartTimeForDate($date))) {
            $endTime->addDay();
        }

        return $endTime;
    }

    public function isLate(Carbon $punchInTime, Carbon $date): bool
    {
        if ($this->is_flexible) {
            return false;
        }

        $shiftStart = $this->getShiftStartTimeForDate($date);
        $graceEnd = $shiftStart->copy()->addMinutes($this->grace_period_minutes);
        $lateThreshold = $graceEnd->copy()->addMinutes($this->late_mark_after_minutes);

        return $punchInTime->greaterThan($lateThreshold);
    }

    public function isHalfDay(Carbon $punchInTime, Carbon $date): bool
    {
        if ($this->is_flexible) {
            return false;
        }

        $shiftStart = $this->getShiftStartTimeForDate($date);
        $halfDayThreshold = $shiftStart->copy()->addMinutes($this->half_day_after_minutes);

        return $punchInTime->greaterThan($halfDayThreshold);
    }

    public function calculateLateMinutes(Carbon $punchInTime, Carbon $date): int
    {
        $shiftStart = $this->getShiftStartTimeForDate($date);
        $graceEnd = $shiftStart->copy()->addMinutes($this->grace_period_minutes);

        if ($punchInTime->lessThanOrEqualTo($graceEnd)) {
            return 0;
        }

        return $punchInTime->diffInMinutes($graceEnd);
    }

    public function calculateWorkingHours(Carbon $punchIn, Carbon $punchOut): float
    {
        $totalMinutes = $punchIn->diffInMinutes($punchOut);
        $workingMinutes = $totalMinutes - $this->break_duration_minutes;

        return max(0, round($workingMinutes / 60, 2));
    }

    public function getShiftDurationHoursAttribute(): float
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        if ($this->is_night_shift && $end->lessThan($start)) {
            $end->addDay();
        }

        return round($start->diffInMinutes($end) / 60, 2);
    }
}
