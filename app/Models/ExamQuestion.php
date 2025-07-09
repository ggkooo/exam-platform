<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'question_id',
        'order',
        'points',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'points' => 'decimal:2'
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the effective points for this question in this exam
     */
    public function getEffectivePointsAttribute()
    {
        return $this->points ?? $this->question->points;
    }
}
