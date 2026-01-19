<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'department_id',
        'designation_id',
        'location_id',
        'shift_id',
        'manager_id',
        'date_of_birth',
        'joining_date',
        'phone',
        'address',
        'profile_photo',
        'gender',
        'role',
        'is_active',
        'device_employee_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'joining_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    // ========================
    // Role Check Methods
    // ========================
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isHRAdmin(): bool
    {
        return $this->role === 'hr_admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function hasAdminAccess(): bool
    {
        return in_array($this->role, ['super_admin', 'hr_admin']);
    }

    // ========================
    // Relationships
    // ========================
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function overtimeRequests(): HasMany
    {
        return $this->hasMany(OvertimeRequest::class);
    }

    // ========================
    // Scopes
    // ========================
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeByLocation($query, int $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    public function scopeByDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    // ========================
    // Accessors
    // ========================
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    public function getRoleDisplayAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'hr_admin' => 'HR Admin',
            'manager' => 'Manager',
            'employee' => 'Employee',
            default => 'Unknown',
        };
    }
}
