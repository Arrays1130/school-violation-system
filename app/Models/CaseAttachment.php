<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseAttachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'case_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'label',
    ];

    public function case()
    {
        return $this->belongsTo(StudentCase::class, 'case_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes > 1024; $i++) $bytes /= 1024;
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFileIconAttribute()
    {
        $ext = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
        return match($ext) {
            'pdf' => 'file-text',
            'doc', 'docx' => 'file-text',
            'jpg', 'jpeg', 'png' => 'image',
            default => 'file'
        };
    }
}
