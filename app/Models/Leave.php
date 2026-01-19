<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'from_date',
        'to_date',
        'total_days',
        'is_half_day',
        'half_day_type',
        'reason',
        'attachment',
        'status',
        'applied_at',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'total_days' => 'decimal:1',
        'is_half_day' => 'boolean',
        'applied_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    // ========================
    // Relationships
    // ========================
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ========================
    // Scopes
    // ========================
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeForDateRange($query, Carbon $from, Carbon $to)
    {
        return $query->where(function ($q) use ($from, $to) {
            $q->whereBetween('from_date', [$from, $to])
                ->orWhereBetween('to_date', [$from, $to])
                ->orWhere(function ($q2) use ($from, $to) {
                    $q2->where('from_date', '<=', $from)
                        ->where('to_date', '>=', $to);
                });
        });
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ========================
    // Methods
    // ========================
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function canCancel(): bool
    {
        return $this->isPending() && $this->from_date->isFuture();
    }

    public function approve(int $approverId): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);
    }

    public function reject(int $approverId, string $reason): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'approved_by' => $approverId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    // ========================
    // Accessors
    // ========================
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'error',
            self::STATUS_CANCELLED => 'neutral',
            default => 'neutral',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Unknown',
        };
    }

    public function getDateRangeAttribute(): string
    {
        if ($this->from_date->equalTo($this->to_date)) {
            return $this->from_date->format('M d, Y');
        }
        return $this->from_date->format('M d') . ' - ' . $this->to_date->format('M d, Y');
    }
}
