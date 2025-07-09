@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="bi bi-collection"></i>
        Banco de Questões
    </h1>
    <a href="{{ route('question-bank.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i>
        Nova Questão
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('question-bank.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="course_id" class="form-label">Curso</label>
                <select class="form-select" id="course_id" name="course_id">
                    <option value="">Todos os cursos</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ $courseId == $course->id ? 'selected' : '' }}>
                            {{ $course->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="subject_id" class="form-label">Disciplina</label>
                <select class="form-select" id="subject_id" name="subject_id">
                    <option value="">Todas as disciplinas</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ $subjectId == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="module_id" class="form-label">Módulo</label>
                <select class="form-select" id="module_id" name="module_id">
                    <option value="">Todos os módulos</option>
                    @foreach($modules as $module)
                        <option value="{{ $module->id }}" {{ $moduleId == $module->id ? 'selected' : '' }}>
                            {{ $module->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="difficulty_level" class="form-label">Dificuldade</label>
                <select class="form-select" id="difficulty_level" name="difficulty_level">
                    <option value="">Todas as dificuldades</option>
                    <option value="easy" {{ $difficultyLevel == 'easy' ? 'selected' : '' }}>Fácil</option>
                    <option value="medium" {{ $difficultyLevel == 'medium' ? 'selected' : '' }}>Médio</option>
                    <option value="hard" {{ $difficultyLevel == 'hard' ? 'selected' : '' }}>Difícil</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="type" class="form-label">Tipo</label>
                <select class="form-select" id="type" name="type">
                    <option value="">Todos os tipos</option>
                    <option value="multiple_choice" {{ $type == 'multiple_choice' ? 'selected' : '' }}>Múltipla Escolha</option>
                    <option value="true_false" {{ $type == 'true_false' ? 'selected' : '' }}>Verdadeiro/Falso</option>
                    <option value="essay" {{ $type == 'essay' ? 'selected' : '' }}>Dissertativa</option>
                </select>
            </div>

            <div class="col-md-9 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary me-2">
                    <i class="bi bi-funnel"></i>
                    Filtrar
                </button>
                <a href="{{ route('question-bank.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i>
                    Limpar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Questões -->
<div class="row">
    @forelse($questions as $question)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="card-title mb-0">
                            {{ $question->title ?? 'Sem título' }}
                        </h6>
                        <span class="badge bg-{{ $question->difficulty_level == 'easy' ? 'success' : ($question->difficulty_level == 'medium' ? 'warning' : 'danger') }}">
                            {{ $question->difficulty_level == 'easy' ? 'Fácil' : ($question->difficulty_level == 'medium' ? 'Médio' : 'Difícil') }}
                        </span>
                    </div>

                    <p class="card-text small text-muted mb-2">
                        <i class="bi bi-diagram-3"></i>
                        {{ $question->hierarchy_path }}
                    </p>

                    <p class="card-text">
                        {{ Str::limit($question->question_text, 100) }}
                    </p>

                    <div class="row text-center small text-muted mb-3">
                        <div class="col-4">
                            <strong>Tipo</strong><br>
                            {{ $question->type == 'multiple_choice' ? 'Múltipla' : ($question->type == 'true_false' ? 'V/F' : 'Dissertativa') }}
                        </div>
                        <div class="col-4">
                            <strong>Pontos</strong><br>
                            {{ $question->points }}
                        </div>
                        <div class="col-4">
                            <strong>Status</strong><br>
                            <span class="badge bg-{{ $question->is_active ? 'success' : 'secondary' }}">
                                {{ $question->is_active ? 'Ativa' : 'Inativa' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-transparent">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('question-bank.show', $question) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i>
                            Ver
                        </a>
                        <div class="btn-group">
                            <a href="{{ route('question-bank.edit', $question) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('question-bank.destroy', $question) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('Tem certeza que deseja excluir esta questão?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="bi bi-collection display-1 text-muted"></i>
                <h4 class="mt-3">Nenhuma questão encontrada</h4>
                <p class="text-muted">Crie sua primeira questão ou ajuste os filtros de busca.</p>
                <a href="{{ route('question-bank.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    Criar Nova Questão
                </a>
            </div>
        </div>
    @endforelse
</div>

<!-- Paginação -->
@if($questions->hasPages())
    <div class="d-flex justify-content-center">
        {{ $questions->appends(request()->query())->links() }}
    </div>
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const courseSelect = document.getElementById('course_id');
    const subjectSelect = document.getElementById('subject_id');
    const moduleSelect = document.getElementById('module_id');

    // Quando o curso muda, atualiza as disciplinas
    courseSelect.addEventListener('change', function() {
        const courseId = this.value;
        
        // Limpa disciplinas e módulos
        subjectSelect.innerHTML = '<option value="">Todas as disciplinas</option>';
        moduleSelect.innerHTML = '<option value="">Todos os módulos</option>';
        
        if (courseId) {
            fetch(`/question-bank/subjects-by-course?course_id=${courseId}`)
                .then(response => response.json())
                .then(subjects => {
                    subjects.forEach(subject => {
                        subjectSelect.innerHTML += `<option value="${subject.id}">${subject.name}</option>`;
                    });
                });
        }
    });

    // Quando a disciplina muda, atualiza os módulos
    subjectSelect.addEventListener('change', function() {
        const subjectId = this.value;
        
        // Limpa módulos
        moduleSelect.innerHTML = '<option value="">Todos os módulos</option>';
        
        if (subjectId) {
            fetch(`/question-bank/modules-by-subject?subject_id=${subjectId}`)
                .then(response => response.json())
                .then(modules => {
                    modules.forEach(module => {
                        moduleSelect.innerHTML += `<option value="${module.id}">${module.name}</option>`;
                    });
                });
        }
    });
});
</script>
@endpush
