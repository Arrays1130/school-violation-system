<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DtrEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'sanction_assignment_id',
        'logged_by',
        'time_in',
        'time_out',
        'hours_served',
        'notes',
    ];

    protected $casts = [
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'hours_served' => 'float',
    ];

    public function sanctionAssignment(): BelongsTo
    {
        return $this->belongsTo(SanctionAssignment::class);
    }

    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'time_in' => $this->time_in?->toIso8601String(),
            'time_out' => $this->time_out?->toIso8601String(),
            'hours_served' => $this->hours_served !== null ? (float) $this->hours_served : null,
            'notes' => $this->notes,
            'logged_by' => $this->loggedBy?->only(['id', 'name']),
            'is_open' => $this->time_out === null,
        ];
    }
}
