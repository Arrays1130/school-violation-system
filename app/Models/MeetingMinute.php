<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeetingMinute extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'case_id',
        'title',
        'content',
        'meeting_date',
        'venue',
        'created_by',
    ];

    protected $casts = [
        'meeting_date' => 'datetime',
    ];

    public function case()
    {
        return $this->belongsTo(StudentCase::class, 'case_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
