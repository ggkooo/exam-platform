<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Question;
use App\Helpers\RoleHelper;
use App\Helpers\MoodleAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ExamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        $query = Exam::with('questions');
        
        // Se não é admin, só mostra exames criados pelo usuário
        if (!RoleHelper::canManageAllExams()) {
            $userId = Session::get('moodle_user_id');
            $query->where('created_by', $userId);
        }
        
        $exams = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('exams.index', compact('exams'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        return view('exams.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('ExamController::store chamado', $request->all());
        
        $redirect = MoodleAuth::require();
        if ($redirect) {
            \Log::info('Redirecionamento do MoodleAuth');
            return $redirect;
        }

        \Log::info('MoodleAuth passou');

        if (!RoleHelper::canCreateExams()) {
            \Log::info('Usuário sem permissão para criar exames');
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        \Log::info('Verificação de permissão passou');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:1',
            'total_points' => 'nullable|numeric|min:0',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'max_attempts' => 'nullable|integer|min:1',
        ]);

        \Log::info('Validação passou', $validated);

        // Aplica valores padrão para campos não preenchidos
        $validated['duration_minutes'] = $validated['duration_minutes'] ?? 60;
        $validated['total_points'] = $validated['total_points'] ?? 100;
        $validated['max_attempts'] = $validated['max_attempts'] ?? 1;

        // Converte os campos de data para o formato correto apenas se não estiverem vazios
        if (!empty($validated['start_time'])) {
            $validated['start_time'] = date('Y-m-d H:i:s', strtotime($validated['start_time']));
        } else {
            $validated['start_time'] = null;
        }
        
        if (!empty($validated['end_time'])) {
            $validated['end_time'] = date('Y-m-d H:i:s', strtotime($validated['end_time']));
        } else {
            $validated['end_time'] = null;
        }

        $userId = Session::get('moodle_user_id');
        \Log::info('User ID da sessão', ['user_id' => $userId]);
        
        if (!$userId) {
            \Log::error('Usuário sem ID na sessão');
            return redirect()->route('login')->withErrors(['error' => 'Sessão inválida. Faça login novamente.']);
        }
        
        $validated['created_by'] = $userId;
        $validated['is_active'] = $request->has('is_active');
        $validated['randomize_questions'] = $request->has('randomize_questions');
        $validated['show_results_immediately'] = $request->has('show_results_immediately');

        \Log::info('Dados validados para criar exame', $validated);

        try {
            $exam = Exam::create($validated);
            \Log::info('Exame criado com sucesso', ['exam_id' => $exam->id]);
            
            return redirect()->route('exams.show', $exam)
                            ->with('success', 'Prova criada com sucesso!');
        } catch (\Exception $e) {
            \Log::error('Erro ao criar exame', ['error' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'Erro ao criar prova: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Exam $exam)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        // Verifica se o usuário pode ver este exame
        if (!RoleHelper::canManageAllExams() && $exam->created_by !== Session::get('moodle_user_id')) {
            abort(403, 'Você não tem permissão para ver esta prova.');
        }

        $exam->load('questions');
        
        return view('exams.show', compact('exam'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Exam $exam)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        // Verifica se o usuário pode editar este exame
        if (!RoleHelper::canManageAllExams() && $exam->created_by !== Session::get('moodle_user_id')) {
            abort(403, 'Você não tem permissão para editar esta prova.');
        }

        return view('exams.edit', compact('exam'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Exam $exam)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        // Verifica se o usuário pode editar este exame
        if (!RoleHelper::canManageAllExams() && $exam->created_by !== Session::get('moodle_user_id')) {
            abort(403, 'Você não tem permissão para editar esta prova.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'total_points' => 'required|numeric|min:0',
            'start_time' => 'nullable|date_format:Y-m-d\TH:i',
            'end_time' => 'nullable|date_format:Y-m-d\TH:i',
            'max_attempts' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'randomize_questions' => 'boolean',
            'show_results_immediately' => 'boolean',
        ]);

        // Converte os campos de data para o formato correto apenas se não estiverem vazios
        if (!empty($validated['start_time'])) {
            $validated['start_time'] = date('Y-m-d H:i:s', strtotime($validated['start_time']));
        } else {
            $validated['start_time'] = null;
        }
        
        if (!empty($validated['end_time'])) {
            $validated['end_time'] = date('Y-m-d H:i:s', strtotime($validated['end_time']));
        } else {
            $validated['end_time'] = null;
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['randomize_questions'] = $request->has('randomize_questions');
        $validated['show_results_immediately'] = $request->has('show_results_immediately');

        $exam->update($validated);

        return redirect()->route('exams.show', $exam)
                        ->with('success', 'Prova atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exam $exam)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        // Verifica se o usuário pode deletar este exame
        if (!RoleHelper::canManageAllExams() && $exam->created_by !== Session::get('moodle_user_id')) {
            abort(403, 'Você não tem permissão para deletar esta prova.');
        }

        // Verifica se há tentativas de resposta
        if ($exam->attempts()->count() > 0) {
            return redirect()->route('exams.index')
                            ->with('error', 'Não é possível excluir uma prova que já possui tentativas de resposta.');
        }

        $exam->delete();

        return redirect()->route('exams.index')
                        ->with('success', 'Prova excluída com sucesso!');
    }
}
