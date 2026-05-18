<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id',
        'user_id',
        'action_type',
        'description',
        'endorsed_to_grievance',
    ];

    protected $casts = [
        'endorsed_to_grievance' => 'boolean',
    ];

    /**
     * Human-readable labels for action types.
     */
    public const ACTION_TYPES = [
        'letter_sent'        => 'Letter Sent to Student',
        'counseling'         => 'Counseling Session',
        'parent_conference'  => 'Parent/Guardian Conference',
        'verbal_warning'     => 'Verbal Warning',
        'written_warning'    => 'Written Warning',
        'endorsement'        => 'Endorsement to Grievance Committee',
        'other'              => 'Other Action',
    ];

    /**
     * Icon mapping for each action type (Lucide icons).
     */
    public const ACTION_ICONS = [
        'letter_sent'        => 'mail',
        'counseling'         => 'heart-handshake',
        'parent_conference'  => 'users',
        'verbal_warning'     => 'megaphone',
        'written_warning'    => 'file-text',
        'endorsement'        => 'send',
        'other'              => 'circle-dot',
    ];

    public function studentCase()
    {
        return $this->belongsTo(StudentCase::class, 'case_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get human-readable label for the action type.
     */
    public function getActionLabelAttribute(): string
    {
        return self::ACTION_TYPES[$this->action_type] ?? ucfirst(str_replace('_', ' ', $this->action_type));
    }

    /**
     * Get the icon name for the action type.
     */
    public function getActionIconAttribute(): string
    {
        return self::ACTION_ICONS[$this->action_type] ?? 'circle-dot';
    }
}
