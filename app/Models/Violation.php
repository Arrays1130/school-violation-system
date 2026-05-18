<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Violation extends Model
{
    use HasFactory;

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

    public function cases()
    {
        return $this->hasMany(StudentCase::class, 'violation_id');
    }
}
