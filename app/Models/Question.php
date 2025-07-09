<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'subject_id', 
        'module_id',
        'title',
        'type',
        'question_text',
        'options',
        'correct_answer',
        'points',
        'explanation',
        'difficulty_level',
        'tags',
        'created_by',
        'is_active'
    ];

    protected $casts = [
        'options' => 'array',
        'tags' => 'array',
        'is_active' => 'boolean',
        'points' => 'decimal:2'
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Relacionamento: Questão pode estar em vários exames
     */
    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_questions')
                    ->withPivot('order', 'points', 'is_active')
                    ->withTimestamps()
                    ->orderBy('exam_questions.order');
    }

    /**
     * Relacionamento: ExamQuestions direto
     */
    public function examQuestions()
    {
        return $this->hasMany(ExamQuestion::class);
    }

    /**
     * Relacionamento: Questão pertence a um curso
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Relacionamento: Questão pertence a uma disciplina
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Relacionamento: Questão pertence a um módulo
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Scope: Questões por curso
     */
    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Scope: Questões por disciplina
     */
    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope: Questões por módulo
     */
    public function scopeByModule($query, $moduleId)
    {
        return $query->where('module_id', $moduleId);
    }

    /**
     * Scope: Questões por nível de dificuldade
     */
    public function scopeByDifficulty($query, $level)
    {
        return $query->where('difficulty_level', $level);
    }

    /**
     * Método utilitário: Caminho hierárquico completo
     */
    public function getHierarchyPathAttribute()
    {
        $path = [];
        
        if ($this->course) {
            $path[] = $this->course->name;
        }
        
        if ($this->subject) {
            $path[] = $this->subject->name;
        }
        
        if ($this->module) {
            $path[] = $this->module->name;
        }
        
        return implode(' > ', $path);
    }

    public function getFormattedOptionsAttribute()
    {
        if ($this->type === 'multiple_choice' && $this->options) {
            // Se options já é um array, usa diretamente
            if (is_array($this->options)) {
                return $this->options;
            }
            // Se é string JSON, decodifica
            return json_decode($this->options, true) ?: [];
        }
        return [];
    }
}
