<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Handbook extends Model
{
    use LogsActivity;

    protected $fillable = [
        'title',
        'content',
        'attachment',
        'file_path',
        'file_name',
        'file_size',
    ];

    protected $appends = [
        'has_file',
        'has_external_link',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function getHasFileAttribute(): bool
    {
        return filled($this->file_path);
    }

    public function getHasExternalLinkAttribute(): bool
    {
        return filled($this->attachment) && (
            str_starts_with((string) $this->attachment, 'http://')
            || str_starts_with((string) $this->attachment, 'https://')
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
