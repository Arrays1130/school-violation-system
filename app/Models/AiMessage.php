<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMessage extends Model
{
    protected $fillable = [
        'ai_conversation_id',
        'role',
        'content',
        'sources',
        'mode',
        'ai_usage_log_id',
    ];

    protected $casts = [
        'sources' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'ai_conversation_id');
    }

    public function usageLog(): BelongsTo
    {
        return $this->belongsTo(AiUsageLog::class, 'ai_usage_log_id');
    }
}
