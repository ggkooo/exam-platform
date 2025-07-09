<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RoleManagementController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuestionBankController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\ModuleController;
use \App\Helpers\MoodleAuth;

// Rota de teste para update sem CSRF - DEVE FICAR NO TOPO
Route::post('/test-edit/{exam}/{question}', function(\Illuminate\Http\Request $request, $examId, $questionId) {
    $exam = \App\Models\Exam::find($examId);
    $question = \App\Models\Question::find($questionId);
    
    if (!$exam || !$question) {
        return 'Prova ou questão não encontrada.';
    }
    
    // Log dos dados recebidos
    \Log::info('Test update - Request data:', $request->all());
    
    try {
        $validated = $request->validate([
            'type' => 'required|in:multiple_choice,true_false,essay',
            'question_text' => 'required|string',
            'options' => 'nullable|array',
            'correct_answer' => 'nullable|string',
            'correct_option' => 'nullable|string',
            'tf_answer' => 'nullable|string',
            'essay_criteria' => 'nullable|string',
            'points' => 'required|numeric|min:0',
            'explanation' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        $validated['is_active'] = $request->has('is_active');
        
        // Processa dados específicos por tipo
        if ($validated['type'] === 'multiple_choice') {
            if (isset($validated['options'])) {
                $validated['options'] = array_filter($validated['options'], function($option) {
                    return !empty(trim($option));
                });
                $validated['options'] = json_encode(array_values($validated['options']));
            }
        } elseif ($validated['type'] === 'true_false') {
            if ($request->has('tf_answer')) {
                $validated['correct_answer'] = $request->input('tf_answer') === 'true' ? 'Verdadeiro' : 'Falso';
            }
        } elseif ($validated['type'] === 'essay') {
            if ($request->has('essay_criteria') && !empty(trim($request->input('essay_criteria')))) {
                $validated['correct_answer'] = $request->input('essay_criteria');
            } else {
                $validated['correct_answer'] = 'Resposta dissertativa';
            }
        }
        
        unset($validated['correct_option'], $validated['tf_answer'], $validated['essay_criteria']);
        
        \Log::info('Test update - Final data:', $validated);
        
        $question->update($validated);
        
        return response()->json(['success' => true, 'message' => 'Questão atualizada com sucesso!', 'data' => $validated]);
    } catch (\Exception $e) {
        \Log::error('Test update error:', ['error' => $e->getMessage()]);
        return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
    }
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/dashboard', function () {
    $redirect = MoodleAuth::require();
    if ($redirect) {
        return $redirect;
    }
    return view('dashboard');
})->name('dashboard');

Route::post('/session/refresh-roles', [SessionController::class, 'refreshRoles'])->name('session.refresh.roles');

// Exam Management Routes
Route::resource('exams', ExamController::class);
Route::resource('exams.questions', QuestionController::class)->except(['index']);
Route::get('exams/{exam}/questions', [QuestionController::class, 'index'])->name('exams.questions.index');
Route::post('exams/{exam}/questions/reorder', [QuestionController::class, 'reorder'])->name('exams.questions.reorder');
Route::patch('exams/{exam}/questions/{question}/exam-settings', [QuestionController::class, 'updateExamSettings'])->name('exams.questions.update-exam-settings');

// Question Bank Routes
Route::get('/question-bank/questions-json', [QuestionBankController::class, 'getQuestionsJson'])->name('question-bank.questions-json');
Route::get('/question-bank/subjects-by-course', [QuestionBankController::class, 'getSubjectsByCourse'])->name('question-bank.subjects-by-course');
Route::get('/question-bank/modules-by-subject', [QuestionBankController::class, 'getModulesBySubject'])->name('question-bank.modules-by-subject');
Route::resource('question-bank', QuestionBankController::class, [
    'names' => 'question-bank',
    'parameters' => ['question-bank' => 'question']
]);

// Organization Management Routes (Courses, Subjects, Modules)
Route::get('/courses/json', [CourseController::class, 'getCoursesJson'])->name('courses.json');
Route::resource('courses', CourseController::class);
Route::resource('subjects', SubjectController::class);
Route::resource('modules', ModuleController::class);

Route::middleware(['web'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/roles', [RoleManagementController::class, 'index'])->name('roles');
    Route::post('/roles/assign', [RoleManagementController::class, 'assignRole'])->name('roles.assign');
    Route::post('/roles/remove', [RoleManagementController::class, 'removeRole'])->name('roles.remove');
    
    Route::get('/users', [RoleManagementController::class, 'users'])->name('users');
    Route::get('/users/{id}/roles', [RoleManagementController::class, 'userRoles'])->name('user.roles');
    Route::post('/users/roles/assign', [RoleManagementController::class, 'assignRoleToUser'])->name('user.roles.assign');
    Route::post('/users/roles/remove', [RoleManagementController::class, 'removeRoleFromUser'])->name('user.roles.remove');
});

// Rota temporária de teste
Route::get('/test-views', function() {
    $exam = \App\Models\Exam::first();
    $question = \App\Models\Question::first();
    
    if (!$exam || !$question) {
        return 'Nenhuma prova ou questão encontrada. Crie dados de teste primeiro.';
    }
    
    return view('questions.show', compact('exam', 'question'));
});

// Rota de teste para edição sem autenticação
Route::get('/test-edit/{exam}/{question}', function($examId, $questionId) {
    $exam = \App\Models\Exam::find($examId);
    $question = \App\Models\Question::find($questionId);
    
    if (!$exam || !$question) {
        return 'Prova ou questão não encontrada.';
    }
    
    return view('questions.edit', compact('exam', 'question'));
});
