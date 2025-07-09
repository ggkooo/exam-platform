<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Subject;
use App\Models\Module;
use App\Helpers\RoleHelper;
use App\Helpers\MoodleAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ModuleController extends Controller
{
    /**
     * Display a listing of modules
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

        $subjectId = $request->get('subject_id');
        
        $query = Module::with(['subject.course']);
        
        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }
        
        // Se não é admin, só mostra módulos criados pelo usuário
        if (!RoleHelper::canManageAllExams()) {
            $userId = Session::get('moodle_user_id');
            $query->where('created_by', $userId);
        }
        
        $modules = $query->ordered()->paginate(10);
        $subjects = Subject::active()->with('course')->orderBy('name')->get();
        
        return view('modules.index', compact('modules', 'subjects', 'subjectId'));
    }

    /**
     * Show the form for creating a new module
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

        $subjectId = $request->get('subject_id');
        $subjects = Subject::active()->with('course')->orderBy('name')->get();

        return view('modules.create', compact('subjects', 'subjectId'));
    }

    /**
     * Store a newly created module
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
            'subject_id' => 'required|exists:subjects,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        // Se não foi especificada uma ordem, calcula a próxima
        if (!isset($validated['order'])) {
            $validated['order'] = Module::where('subject_id', $validated['subject_id'])->max('order') + 1;
        }

        $validated['created_by'] = Session::get('moodle_user_id');
        $validated['is_active'] = $request->has('is_active');

        $module = Module::create($validated);

        return redirect()->route('modules.show', $module)
                        ->with('success', 'Módulo criado com sucesso!');
    }

    /**
     * Display the specified module
     */
    public function show(Module $module)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        // Verifica se o usuário pode ver este módulo
        if (!RoleHelper::canManageAllExams() && $module->created_by !== Session::get('moodle_user_id')) {
            abort(403, 'Você não tem permissão para ver este módulo.');
        }

        $module->load(['subject.course', 'questions']);
        
        return view('modules.show', compact('module'));
    }

    /**
     * Show the form for editing the specified module
     */
    public function edit(Module $module)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        // Verifica se o usuário pode editar este módulo
        if (!RoleHelper::canManageAllExams() && $module->created_by !== Session::get('moodle_user_id')) {
            abort(403, 'Você não tem permissão para editar este módulo.');
        }

        $subjects = Subject::active()->with('course')->orderBy('name')->get();

        return view('modules.edit', compact('module', 'subjects'));
    }

    /**
     * Update the specified module
     */
    public function update(Request $request, Module $module)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        // Verifica se o usuário pode editar este módulo
        if (!RoleHelper::canManageAllExams() && $module->created_by !== Session::get('moodle_user_id')) {
            abort(403, 'Você não tem permissão para editar este módulo.');
        }

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $module->update($validated);

        return redirect()->route('modules.show', $module)
                        ->with('success', 'Módulo atualizado com sucesso!');
    }

    /**
     * Remove the specified module
     */
    public function destroy(Module $module)
    {
        $redirect = MoodleAuth::require();
        if ($redirect) {
            return $redirect;
        }

        if (!RoleHelper::canCreateExams()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        // Verifica se o usuário pode excluir este módulo
        if (!RoleHelper::canManageAllExams() && $module->created_by !== Session::get('moodle_user_id')) {
            abort(403, 'Você não tem permissão para excluir este módulo.');
        }

        // Verifica se há questões vinculadas
        if ($module->questions()->count() > 0) {
            return back()->withErrors(['error' => 'Não é possível excluir o módulo pois há questões vinculadas a ele.']);
        }

        $module->delete();

        return redirect()->route('modules.index')
                        ->with('success', 'Módulo excluído com sucesso!');
    }
}
