<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'duration_minutes',
        'total_questions',
        'total_points',
        'is_active',
        'randomize_questions',
        'show_results_immediately',
        'start_time',
        'end_time',
        'max_attempts',
        'created_by'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
        'randomize_questions' => 'boolean',
        'show_results_immediately' => 'boolean',
    ];

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'exam_questions')
                    ->withPivot('order', 'points', 'is_active')
                    ->withTimestamps()
                    ->orderBy('exam_questions.order');
    }

    /**
     * Relacionamento direto com ExamQuestion para manipulação mais fácil
     */
    public function examQuestions()
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('order');
    }

    /**
     * Compatibilidade: manter método antigo por enquanto
     * @deprecated Use questions() com o novo relacionamento
     */
    public function questionsOld()
    {
        return $this->hasMany(Question::class, 'exam_id')->orderBy('order');
    }

    public function attempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function isAvailable()
    {
        $now = now();
        return $this->is_active && 
               ($this->start_time === null || $this->start_time <= $now) &&
               ($this->end_time === null || $this->end_time >= $now);
    }

    public function canUserTakeExam($userId)
    {
        if (!$this->isAvailable()) {
            return false;
        }

        $attemptsCount = $this->attempts()
            ->where('user_id', $userId)
            ->where('is_completed', true)
            ->count();

        return $attemptsCount < $this->max_attempts;
    }
}
