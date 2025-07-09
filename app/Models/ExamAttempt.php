<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'user_id',
        'started_at',
        'finished_at',
        'score',
        'percentage',
        'answers',
        'is_completed',
        'attempt_number'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'answers' => 'array',
        'is_completed' => 'boolean',
        'score' => 'decimal:2',
        'percentage' => 'decimal:2'
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function getDurationAttribute()
    {
        if ($this->started_at && $this->finished_at) {
            return $this->started_at->diffInMinutes($this->finished_at);
        }
        return null;
    }
}
