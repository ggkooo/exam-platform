<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'name',
        'code',
        'description',
        'workload_hours',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'workload_hours' => 'integer',
    ];

    /**
     * Relacionamento: Disciplina pertence a um curso
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Relacionamento: Disciplina tem muitos módulos
     */
    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    /**
     * Relacionamento: Disciplina tem muitas questões
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Scope: Apenas disciplinas ativas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Método utilitário: Total de módulos
     */
    public function getTotalModulesAttribute()
    {
        return $this->modules()->count();
    }

    /**
     * Método utilitário: Total de questões
     */
    public function getTotalQuestionsAttribute()
    {
        return $this->questions()->count();
    }
}
