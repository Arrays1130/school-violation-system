<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiEmbedding extends Model
{
    protected $fillable = [
        'source_type',
        'source_id',
        'chunk_index',
        'title',
        'content',
        'embedding',
    ];

    protected $casts = [
        'embedding' => 'array',
        'chunk_index' => 'integer',
    ];
}
