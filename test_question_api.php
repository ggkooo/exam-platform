<?php

require_once 'vendor/autoload.php';

use App\Models\Question;
use App\Models\Course;
use App\Models\Subject;
use App\Models\Module;

// Simular uma requisição para testar a API
echo "=== Teste da API de Questões ===\n\n";

// Listar questões do banco (sem exam_id)
$questions = Question::whereNull('exam_id')
    ->with(['course', 'subject', 'module'])
    ->get();

echo "Questões no banco de questões: " . $questions->count() . "\n\n";

foreach ($questions as $question) {
    echo "ID: " . $question->id . "\n";
    echo "Título: " . ($question->title ?? 'Sem título') . "\n";
    echo "Tipo: " . $question->type . "\n";
    echo "Pontos: " . $question->points . "\n";
    echo "Dificuldade: " . ($question->difficulty_level ?? 'Não definida') . "\n";
    
    // Hierarquia
    $hierarchy = [];
    if ($question->course) $hierarchy[] = $question->course->name;
    if ($question->subject) $hierarchy[] = $question->subject->name;
    if ($question->module) $hierarchy[] = $question->module->name;
    
    echo "Hierarquia: " . (empty($hierarchy) ? 'Sem organização' : implode(' > ', $hierarchy)) . "\n";
    echo "Texto: " . substr($question->question_text, 0, 100) . "...\n";
    echo "---\n\n";
}

// Simular dados JSON como retornado pela API
$jsonData = [
    'questions' => $questions->map(function($question) {
        return [
            'id' => $question->id,
            'title' => $question->title,
            'question_text' => $question->question_text,
            'type' => $question->type,
            'points' => $question->points,
            'difficulty_level' => $question->difficulty_level,
            'course' => $question->course ? ['id' => $question->course->id, 'name' => $question->course->name] : null,
            'subject' => $question->subject ? ['id' => $question->subject->id, 'name' => $question->subject->name] : null,
            'module' => $question->module ? ['id' => $question->module->id, 'name' => $question->module->name] : null,
        ];
    }),
    'pagination' => [
        'current_page' => 1,
        'last_page' => 1,
        'per_page' => 20,
        'total' => $questions->count(),
        'has_more' => false
    ]
];

echo "JSON da API:\n";
echo json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
