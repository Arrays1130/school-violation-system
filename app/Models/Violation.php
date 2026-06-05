<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Violation extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'code',
        'title',
        'category',
        'severity',
        'default_description',
        'first_offense',
        'second_offense',
        'third_offense',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function cases()
    {
        return $this->hasMany(StudentCase::class, 'violation_id');
    }
}
