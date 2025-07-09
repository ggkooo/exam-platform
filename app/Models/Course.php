<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relacionamento: Curso tem muitas disciplinas
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * Relacionamento: Curso tem muitas questões
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Scope: Apenas cursos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Método utilitário: Total de disciplinas
     */
    public function getTotalSubjectsAttribute()
    {
        return $this->subjects()->count();
    }

    /**
     * Método utilitário: Total de questões
     */
    public function getTotalQuestionsAttribute()
    {
        return $this->questions()->count();
    }
}
