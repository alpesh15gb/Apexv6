<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'year',
        'opening_balance',
        'accrued',
        'used',
        'pending',
        'carry_forward',
        'adjustment',
    ];

    protected $casts = [
        'year' => 'integer',
        'opening_balance' => 'decimal:1',
        'accrued' => 'decimal:1',
        'used' => 'decimal:1',
        'pending' => 'decimal:1',
        'carry_forward' => 'decimal:1',
        'adjustment' => 'decimal:1',
    ];

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

    // ========================
    // Scopes
    // ========================
    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ========================
    // Accessors
    // ========================
    public function getAvailableBalanceAttribute(): float
    {
        return $this->opening_balance
            + $this->accrued
            + $this->carry_forward
            + $this->adjustment
            - $this->used
            - $this->pending;
    }

    public function getTotalEntitlementAttribute(): float
    {
        return $this->opening_balance
            + $this->accrued
            + $this->carry_forward
            + $this->adjustment;
    }

    // ========================
    // Methods
    // ========================
    public function canApply(float $days): bool
    {
        return $this->available_balance >= $days;
    }

    public function deduct(float $days): void
    {
        $this->used += $days;
        $this->save();
    }

    public function addPending(float $days): void
    {
        $this->pending += $days;
        $this->save();
    }

    public function removePending(float $days): void
    {
        $this->pending = max(0, $this->pending - $days);
        $this->save();
    }

    public function convertPendingToUsed(float $days): void
    {
        $this->pending = max(0, $this->pending - $days);
        $this->used += $days;
        $this->save();
    }
}
