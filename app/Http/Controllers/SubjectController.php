<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Subject;
use App\Helpers\RoleHelper;
use App\Helpers\MoodleAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects
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

        $courseId = $request->get('course_id');
        
        $query = Subject::with(['course', 'modules']);
        
        if ($courseId) {
            $query->where('course_id', $courseId);
        }
        
        // Se não é admin, só mostra disciplinas criadas pelo usuário
        if (!RoleHelper::canManageAllExams()) {
            $userId = Session::get('moodle_user_id');
            $query->where('created_by', $userId);
        }
        
        $subjects = $query->orderBy('name')->paginate(10);
        $courses = Course::active()->orderBy('name')->get();
        
        return view('subjects.index', compact('subjects', 'courses', 'courseId'));
    }

    /**
     * Show the form for creating a new subject
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

        $courseId = $request->get('course_id');
        $courses = Course::active()->orderBy('name')->get();

        return view('subjects.create', compact('courses', 'courseId'));
    }

    /**
     * Store a newly created subject
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
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'workload_hours' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = Session::get('moodle_user_id');
        $validated['is_active'] = $request->has('is_active');

        $subject = Subject::create($validated);

        return redirect()->route('subjects.show', $subject)
                        ->with('success', 'Disciplina criada com sucesso!');
    }

    /**
     * Display the specified subject
     */
    public function show(Subject $subject)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        // Verifica se o usuário pode ver esta disciplina
        if (!RoleHelper::canManageAllExams() && $subject->created_by !== Session::get('moodle_user_id')) {
            abort(403, 'Você não tem permissão para ver esta disciplina.');
        }

        $subject->load(['course', 'modules', 'questions']);
        
        return view('subjects.show', compact('subject'));
    }

    /**
     * Show the form for editing the specified subject
     */
    public function edit(Subject $subject)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        // Verifica se o usuário pode editar esta disciplina
        if (!RoleHelper::canManageAllExams() && $subject->created_by !== Session::get('moodle_user_id')) {
            abort(403, 'Você não tem permissão para editar esta disciplina.');
        }

        $courses = Course::active()->orderBy('name')->get();

        return view('subjects.edit', compact('subject', 'courses'));
    }

    /**
     * Update the specified subject
     */
    public function update(Request $request, Subject $subject)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        // Verifica se o usuário pode editar esta disciplina
        if (!RoleHelper::canManageAllExams() && $subject->created_by !== Session::get('moodle_user_id')) {
            abort(403, 'Você não tem permissão para editar esta disciplina.');
        }

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'workload_hours' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $subject->update($validated);

        return redirect()->route('subjects.show', $subject)
                        ->with('success', 'Disciplina atualizada com sucesso!');
    }

    /**
     * Remove the specified subject
     */
    public function destroy(Subject $subject)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        // Verifica se o usuário pode excluir esta disciplina
        if (!RoleHelper::canManageAllExams() && $subject->created_by !== Session::get('moodle_user_id')) {
            abort(403, 'Você não tem permissão para excluir esta disciplina.');
        }

        // Verifica se há módulos vinculados
        if ($subject->modules()->count() > 0) {
            return back()->withErrors(['error' => 'Não é possível excluir a disciplina pois há módulos vinculados a ela.']);
        }

        $subject->delete();

        return redirect()->route('subjects.index')
                        ->with('success', 'Disciplina excluída com sucesso!');
    }
}
