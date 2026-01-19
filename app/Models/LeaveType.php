<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'total_days_per_year',
        'carry_forward',
        'max_carry_forward_days',
        'max_consecutive_days',
        'requires_attachment',
        'attachment_required_after_days',
        'is_paid',
        'is_active',
    ];

    protected $casts = [
        'total_days_per_year' => 'integer',
        'carry_forward' => 'boolean',
        'max_carry_forward_days' => 'integer',
        'max_consecutive_days' => 'integer',
        'requires_attachment' => 'boolean',
        'attachment_required_after_days' => 'integer',
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ========================
    // Relationships
    // ========================
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    // ========================
    // Scopes
    // ========================
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    // ========================
    // Methods
    // ========================
    public function requiresAttachment(float $days): bool
    {
        if ($this->requires_attachment) {
            if ($this->attachment_required_after_days) {
                return $days > $this->attachment_required_after_days;
            }
            return true;
        }
        return false;
    }
}
