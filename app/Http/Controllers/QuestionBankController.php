<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Subject;
use App\Models\Module;
use App\Models\Question;
use App\Helpers\RoleHelper;
use App\Helpers\MoodleAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class QuestionBankController extends Controller
{
    /**
     * Display a listing of questions with hierarchy filters
     */
    public function index(Request $request)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        // Pega os filtros
        $courseId = $request->get('course_id');
        $subjectId = $request->get('subject_id');
        $moduleId = $request->get('module_id');
        $difficultyLevel = $request->get('difficulty_level');
        $type = $request->get('type');

        // Query das questões
        $query = Question::with(['course', 'subject', 'module'])
                        ->whereNull('exam_id'); // Apenas questões do banco (não vinculadas a provas)

        // Se não é admin, só mostra questões criadas pelo usuário
        if (!RoleHelper::canManageAllExams()) {
            $userId = Session::get('moodle_user_id');
            $query->where('created_by', $userId);
        }

        // Aplica os filtros
        if ($courseId) {
            $query->where('course_id', $courseId);
        }
        
        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }
        
        if ($moduleId) {
            $query->where('module_id', $moduleId);
        }
        
        if ($difficultyLevel) {
            $query->where('difficulty_level', $difficultyLevel);
        }
        
        if ($type) {
            $query->where('type', $type);
        }

        $questions = $query->orderBy('created_at', 'desc')->paginate(10);

        // Dados para os filtros
        $courses = Course::active()->orderBy('name')->get();
        $subjects = $courseId ? Subject::where('course_id', $courseId)->active()->orderBy('name')->get() : collect();
        $modules = $subjectId ? Module::where('subject_id', $subjectId)->active()->ordered()->get() : collect();

        return view('question-bank.index', compact(
            'questions', 
            'courses', 
            'subjects', 
            'modules',
            'courseId',
            'subjectId', 
            'moduleId',
            'difficultyLevel',
            'type'
        ));
    }

    /**
     * Show the form for creating a new question
     */
    public function create(Request $request)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        $courses = Course::active()->orderBy('name')->get();
        
        // Se já tem filtros na URL, carrega dados relacionados
        $courseId = $request->get('course_id');
        $subjectId = $request->get('subject_id');
        
        $subjects = $courseId ? Subject::where('course_id', $courseId)->active()->orderBy('name')->get() : collect();
        $modules = $subjectId ? Module::where('subject_id', $subjectId)->active()->ordered()->get() : collect();

        return view('question-bank.create', compact('courses', 'subjects', 'modules', 'courseId', 'subjectId'));
    }

    /**
     * Store a newly created question
     */
    public function store(Request $request)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'subject_id' => 'required|exists:subjects,id',
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:multiple_choice,true_false,essay',
            'question_text' => 'required|string',
            'options' => 'nullable|array',
            'options.*' => 'nullable|string',
            'correct_answer' => 'required|string',
            'points' => 'required|numeric|min:0',
            'difficulty_level' => 'required|in:easy,medium,hard',
            'explanation' => 'nullable|string',
            'tags' => 'nullable|string',
        ]);

        // Processa as tags
        if ($validated['tags']) {
            $validated['tags'] = array_map('trim', explode(',', $validated['tags']));
        }

        $validated['created_by'] = Session::get('moodle_user_id');
        $validated['is_active'] = $request->has('is_active');
        $validated['exam_id'] = null; // Questões do banco não pertencem a uma prova específica

        // Para questões de múltipla escolha, converte as opções para JSON
        if ($validated['type'] === 'multiple_choice' && isset($validated['options'])) {
            $validated['options'] = array_filter($validated['options']); // Remove opções vazias
            if (empty($validated['options'])) {
                $validated['options'] = null; // Se não há opções válidas, define como null
            }
        }

        try {
            $question = Question::create($validated);
            
            return redirect()->route('question-bank.show', $question)
                            ->with('success', 'Questão criada com sucesso!');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Erro ao criar questão: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified question
     */
    public function show(Question $question)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        // Verifica se o usuário pode ver esta questão
        if (!RoleHelper::canManageAllExams() && $question->created_by !== Session::get('moodle_user_id')) {
            abort(403, 'Você não tem permissão para ver esta questão.');
        }

        // Verifica se é uma questão do banco (sem exam_id)
        if ($question->exam_id !== null) {
            abort(404, 'Esta questão pertence a uma prova específica.');
        }

        return view('question-bank.show', compact('question'));
    }

    /**
     * Show the form for editing the specified question
     */
    public function edit(Question $question)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        // Verifica se o usuário pode editar esta questão
        if (!RoleHelper::canManageAllExams() && $question->created_by !== Session::get('moodle_user_id')) {
            abort(403, 'Você não tem permissão para editar esta questão.');
        }

        // Verifica se é uma questão do banco (sem exam_id)
        if ($question->exam_id !== null) {
            abort(404, 'Esta questão pertence a uma prova específica.');
        }

        $courses = Course::active()->orderBy('name')->get();
        $subjects = $question->course_id ? Subject::where('course_id', $question->course_id)->active()->orderBy('name')->get() : collect();
        $modules = $question->subject_id ? Module::where('subject_id', $question->subject_id)->active()->ordered()->get() : collect();

        return view('question-bank.edit', compact('question', 'courses', 'subjects', 'modules'));
    }

    /**
     * Update the specified question
     */
    public function update(Request $request, Question $question)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        // Verifica se o usuário pode editar esta questão
        if (!RoleHelper::canManageAllExams() && $question->created_by !== Session::get('moodle_user_id')) {
            abort(403, 'Você não tem permissão para editar esta questão.');
        }

        // Verifica se é uma questão do banco (sem exam_id)
        if ($question->exam_id !== null) {
            abort(404, 'Esta questão pertence a uma prova específica.');
        }

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'subject_id' => 'required|exists:subjects,id',
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:multiple_choice,true_false,essay',
            'question_text' => 'required|string',
            'options' => 'nullable|array',
            'options.*' => 'nullable|string',
            'correct_answer' => 'required|string',
            'points' => 'required|numeric|min:0',
            'difficulty_level' => 'required|in:easy,medium,hard',
            'explanation' => 'nullable|string',
            'tags' => 'nullable|string',
        ]);

        // Processa as tags
        if ($validated['tags']) {
            $validated['tags'] = array_map('trim', explode(',', $validated['tags']));
        }

        $validated['is_active'] = $request->has('is_active');

        // Para questões de múltipla escolha, converte as opções para JSON
        if ($validated['type'] === 'multiple_choice' && isset($validated['options'])) {
            $validated['options'] = array_filter($validated['options']); // Remove opções vazias
            if (empty($validated['options'])) {
                $validated['options'] = null; // Se não há opções válidas, define como null
            }
        }

        $question->update($validated);

        return redirect()->route('question-bank.show', $question)
                        ->with('success', 'Questão atualizada com sucesso!');
    }

    /**
     * Remove the specified question
     */
    public function destroy(Question $question)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        // Verifica se o usuário pode excluir esta questão
        if (!RoleHelper::canManageAllExams() && $question->created_by !== Session::get('moodle_user_id')) {
            abort(403, 'Você não tem permissão para excluir esta questão.');
        }

        // Verifica se é uma questão do banco (sem exam_id)
        if ($question->exam_id !== null) {
            abort(404, 'Esta questão pertence a uma prova específica.');
        }

        $question->delete();

        return redirect()->route('question-bank.index')
                        ->with('success', 'Questão excluída com sucesso!');
    }

    /**
     * Get subjects by course (AJAX)
     */
    public function getSubjectsByCourse(Request $request)
    {
        $courseId = $request->get('course_id');
        $subjects = Subject::where('course_id', $courseId)->active()->orderBy('name')->get(['id', 'name']);
        
        return response()->json($subjects);
    }

    /**
     * Get modules by subject (AJAX)
     */
    public function getModulesBySubject(Request $request)
    {
        $subjectId = $request->get('subject_id');
        $modules = Module::where('subject_id', $subjectId)->active()->ordered()->get(['id', 'name']);
        
        return response()->json($modules);
    }

    /**
     * Get questions in JSON format for AJAX requests
     */
    public function getQuestionsJson(Request $request)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        if (!RoleHelper::canCreateExams()) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $query = Question::whereNull('exam_id') // Apenas questões do banco
                        ->where('is_active', true);

        // Se não é admin, só mostra questões criadas pelo usuário
        if (!RoleHelper::canManageAllExams()) {
            $userId = Session::get('moodle_user_id');
            $query->where('created_by', $userId);
        }

        // Aplicar filtros se fornecidos
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('module_id')) {
            $query->where('module_id', $request->module_id);
        }

        if ($request->filled('difficulty_level')) {
            $query->where('difficulty_level', $request->difficulty_level);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('question_text', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%");
            });
        }

        $questions = $query->with(['course', 'subject', 'module'])
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

        return response()->json([
            'questions' => $questions->items(),
            'pagination' => [
                'current_page' => $questions->currentPage(),
                'last_page' => $questions->lastPage(),
                'per_page' => $questions->perPage(),
                'total' => $questions->total(),
                'has_more' => $questions->hasMorePages()
            ]
        ]);
    }
}
