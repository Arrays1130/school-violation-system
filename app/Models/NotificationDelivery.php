<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationDelivery extends Model
{
    protected $fillable = [
        'channel',
        'event',
        'recipient',
        'recipient_type',
        'status',
        'context',
        'last_error',
        'sent_at',
        'failed_at',
    ];

    protected $casts = [
        'context' => 'array',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];
}
