<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Question;
use App\Models\ExamQuestion;
use App\Helpers\RoleHelper;
use App\Helpers\MoodleAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Exam $exam)
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
            abort(403, 'Você não tem permissão para ver as questões desta prova.');
        }

        $examQuestions = $exam->examQuestions()
                            ->with('question')
                            ->orderBy('order')
                            ->paginate(10);
        
        return view('questions.index', compact('exam', 'examQuestions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Exam $exam)
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
            abort(403, 'Você não tem permissão para adicionar questões a esta prova.');
        }

        return view('questions.create', compact('exam'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Exam $exam)
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
            abort(403, 'Você não tem permissão para adicionar questões a esta prova.');
        }

        // Verifica se é para adicionar questões existentes do banco
        if ($request->has('question_ids')) {
            return $this->addExistingQuestions($request, $exam);
        }

        // Criação de nova questão
        $validated = $request->validate([
            'type' => 'required|in:multiple_choice,true_false,essay',
            'question_text' => 'required|string',
            'options' => 'nullable|array',
            'correct_answer' => 'required|string',
            'points' => 'required|numeric|min:0',
            'explanation' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['created_by'] = Session::get('moodle_user_id');
        
        // Para questões de múltipla escolha, converte as opções para JSON
        if ($validated['type'] === 'multiple_choice' && isset($validated['options'])) {
            $validated['options'] = json_encode($validated['options']);
        }

        // Cria a questão no banco de questões (sem exam_id)
        $question = Question::create($validated);

        // Adiciona a questão ao exame através da tabela pivot
        $nextOrder = $exam->examQuestions()->max('order') + 1;
        
        ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_id' => $question->id,
            'order' => $nextOrder,
            'points' => $validated['points'],
            'is_active' => $validated['is_active']
        ]);

        // Atualiza o total de questões no exame
        $exam->update(['total_questions' => $exam->examQuestions()->count()]);

        return redirect()->route('exams.questions.index', $exam)
                        ->with('success', 'Questão criada e adicionada à prova com sucesso!');
    }

    /**
     * Add existing questions from question bank to exam
     */
    private function addExistingQuestions(Request $request, Exam $exam)
    {
        $questionIds = json_decode($request->get('question_ids'), true);
        
        if (empty($questionIds)) {
            return redirect()->back()->withErrors(['error' => 'Nenhuma questão foi selecionada.']);
        }

        $addedCount = 0;
        $errors = [];

        foreach ($questionIds as $questionId) {
            try {
                // Busca a questão no banco
                $question = Question::find($questionId);

                if (!$question) {
                    $errors[] = "Questão ID {$questionId} não encontrada no banco de questões.";
                    continue;
                }

                // Verifica se já existe na prova
                $existsInExam = ExamQuestion::where('exam_id', $exam->id)
                                          ->where('question_id', $question->id)
                                          ->exists();

                if ($existsInExam) {
                    $errors[] = "A questão '{$question->title}' já existe nesta prova.";
                    continue;
                }

                // Calcula a próxima ordem
                $nextOrder = $exam->examQuestions()->max('order') + 1;

                // Adiciona a questão ao exame através da tabela pivot
                ExamQuestion::create([
                    'exam_id' => $exam->id,
                    'question_id' => $question->id,
                    'order' => $nextOrder,
                    'points' => $question->points, // Usa os pontos originais da questão
                    'is_active' => true
                ]);

                $addedCount++;

            } catch (\Exception $e) {
                $errors[] = "Erro ao adicionar questão ID {$questionId}: " . $e->getMessage();
            }
        }

        // Atualiza o total de questões no exame
        $exam->update(['total_questions' => $exam->examQuestions()->count()]);

        $message = "✅ {$addedCount} questão(ões) adicionada(s) com sucesso!";
        
        if (!empty($errors)) {
            $message .= "\n\n⚠️ Alguns problemas ocorreram:\n" . implode("\n", $errors);
        }

        return redirect()->route('exams.questions.index', $exam)
                        ->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(Exam $exam, Question $question)
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
            abort(403, 'Você não tem permissão para ver esta questão.');
        }

        // Busca a relação ExamQuestion
        $examQuestion = ExamQuestion::where('exam_id', $exam->id)
                                   ->where('question_id', $question->id)
                                   ->first();

        if (!$examQuestion) {
            abort(404, 'Questão não encontrada nesta prova.');
        }

        return view('questions.show', compact('exam', 'question', 'examQuestion'));
    }

    /**
     * Show the form for editing the specified resource.
     * @deprecated Edição agora deve ser feita no banco de questões
     */
    public function edit(Exam $exam, Question $question)
    {
        // Redireciona para o banco de questões
        return redirect()->route('question-bank.edit', $question)
                        ->with('info', 'Edição de questões deve ser feita no banco de questões.');
    }

    /**
     * Update the specified resource in storage.
     * @deprecated Edição agora deve ser feita no banco de questões
     */
    public function update(Request $request, Exam $exam, Question $question)
    {
        // Redireciona para o banco de questões
        return redirect()->route('question-bank.edit', $question)
                        ->with('info', 'Edição de questões deve ser feita no banco de questões.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exam $exam, Question $question)
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
            abort(403, 'Você não tem permissão para excluir esta questão.');
        }

        // Busca a relação ExamQuestion
        $examQuestion = ExamQuestion::where('exam_id', $exam->id)
                                   ->where('question_id', $question->id)
                                   ->first();

        if (!$examQuestion) {
            abort(404, 'Questão não encontrada nesta prova.');
        }

        // Remove apenas a relação exam-question, não a questão em si
        $examQuestion->delete();

        // Verifica se a questão não está sendo usada em outros exames
        $otherUsages = ExamQuestion::where('question_id', $question->id)->count();
        
        if ($otherUsages === 0) {
            // Se não está sendo usada em nenhum exame, pergunta se quer excluir a questão do banco
            // Por agora, vamos apenas remover da prova
            \Log::info("Questão ID {$question->id} removida do exame {$exam->id}, mas mantida no banco de questões");
        }

        // Atualiza o total de questões no exame
        $exam->update(['total_questions' => $exam->examQuestions()->count()]);

        return redirect()->route('exams.questions.index', $exam)
                        ->with('success', 'Questão removida da prova com sucesso!');
    }

    /**
     * Reorder questions in an exam
     */
    public function reorder(Request $request, Exam $exam)
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
            abort(403, 'Você não tem permissão para reordenar as questões desta prova.');
        }

        $validated = $request->validate([
            'questions' => 'required|array',
            'questions.*' => 'exists:questions,id'
        ]);

        foreach ($validated['questions'] as $order => $questionId) {
            ExamQuestion::where('exam_id', $exam->id)
                        ->where('question_id', $questionId)
                        ->update(['order' => $order + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Update exam-specific properties of a question
     */
    public function updateExamSettings(Request $request, Exam $exam, Question $question)
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
            abort(403, 'Você não tem permissão para editar esta questão.');
        }

        // Busca a relação ExamQuestion
        $examQuestion = ExamQuestion::where('exam_id', $exam->id)
                                   ->where('question_id', $question->id)
                                   ->first();

        if (!$examQuestion) {
            abort(404, 'Questão não encontrada nesta prova.');
        }

        $validated = $request->validate([
            'points' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $examQuestion->update([
            'points' => $validated['points'],
            'is_active' => $request->has('is_active')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Configurações da questão atualizadas com sucesso!'
        ]);
    }
}
