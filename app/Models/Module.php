<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'name',
        'code',
        'description',
        'order',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Relacionamento: Módulo pertence a uma disciplina
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Relacionamento: Módulo tem muitas questões
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Relacionamento: Curso através da disciplina
     */
    public function course()
    {
        return $this->hasOneThrough(Course::class, Subject::class, 'id', 'id', 'subject_id', 'course_id');
    }

    /**
     * Scope: Apenas módulos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Ordenados por ordem
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Método utilitário: Total de questões
     */
    public function getTotalQuestionsAttribute()
    {
        return $this->questions()->count();
    }
}
