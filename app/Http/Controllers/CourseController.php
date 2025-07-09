<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Helpers\RoleHelper;
use App\Helpers\MoodleAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CourseController extends Controller
{
    /**
     * Display a listing of courses
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

        $query = Course::with(['subjects']);
        
        // Se não é admin, só mostra cursos criados pelo usuário
        if (!RoleHelper::canManageAllExams()) {
            $userId = Session::get('moodle_user_id');
            $query->where('created_by', $userId);
        }
        
        $courses = $query->orderBy('name')->paginate(10);
        
        return view('courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new course
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

        return view('courses.create');
    }

    /**
     * Store a newly created course
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
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:courses,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = Session::get('moodle_user_id');
        $validated['is_active'] = $request->has('is_active');

        $course = Course::create($validated);

        return redirect()->route('courses.show', $course)
                        ->with('success', 'Curso criado com sucesso!');
    }

    /**
     * Display the specified course
     */
    public function show(Course $course)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        // Verifica se o usuário pode ver este curso
        if (!RoleHelper::canManageAllExams() && $course->created_by !== Session::get('moodle_user_id')) {
            abort(403, 'Você não tem permissão para ver este curso.');
        }

        $course->load(['subjects.modules', 'questions']);
        
        return view('courses.show', compact('course'));
    }

    /**
     * Show the form for editing the specified course
     */
    public function edit(Course $course)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        // Verifica se o usuário pode editar este curso
        if (!RoleHelper::canManageAllExams() && $course->created_by !== Session::get('moodle_user_id')) {
            abort(403, 'Você não tem permissão para editar este curso.');
        }

        return view('courses.edit', compact('course'));
    }

    /**
     * Update the specified course
     */
    public function update(Request $request, Course $course)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        // Verifica se o usuário pode editar este curso
        if (!RoleHelper::canManageAllExams() && $course->created_by !== Session::get('moodle_user_id')) {
            abort(403, 'Você não tem permissão para editar este curso.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:courses,code,' . $course->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $course->update($validated);

        return redirect()->route('courses.show', $course)
                        ->with('success', 'Curso atualizado com sucesso!');
    }

    /**
     * Remove the specified course
     */
    public function destroy(Course $course)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        // Verifica se o usuário pode excluir este curso
        if (!RoleHelper::canManageAllExams() && $course->created_by !== Session::get('moodle_user_id')) {
            abort(403, 'Você não tem permissão para excluir este curso.');
        }

        // Verifica se há disciplinas vinculadas
        if ($course->subjects()->count() > 0) {
            return back()->withErrors(['error' => 'Não é possível excluir o curso pois há disciplinas vinculadas a ele.']);
        }

        $course->delete();

        return redirect()->route('courses.index')
                        ->with('success', 'Curso excluído com sucesso!');
    }

    /**
     * Get courses as JSON (for AJAX requests)
     */
    public function getCoursesJson()
    {
        $courses = Course::active()->orderBy('name')->get(['id', 'name']);
        return response()->json($courses);
    }
}
