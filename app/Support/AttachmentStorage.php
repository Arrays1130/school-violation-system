<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class AttachmentStorage
{
    public static function disk(): Filesystem
    {
        return Storage::disk((string) config('filesystems.attachments_disk', 'local'));
    }
}
