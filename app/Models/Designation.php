<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Designation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'level',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer',
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

    public function scopeByLevel($query, int $level)
    {
        return $query->where('level', $level);
    }
}
