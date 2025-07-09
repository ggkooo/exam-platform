@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="bi bi-plus-circle"></i>
        Nova Questão
    </h1>
    <a href="{{ route('question-bank.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
        Voltar
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('question-bank.store') }}" method="POST">
            @csrf
            
            <!-- Seleção Hierárquica -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-diagram-3"></i>
                        Organização
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="course_id" class="form-label">Curso *</label>
                            <select class="form-select @error('course_id') is-invalid @enderror" 
                                    id="course_id" 
                                    name="course_id" 
                                    required>
                                <option value="">Selecione um curso</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ old('course_id', $courseId) == $course->id ? 'selected' : '' }}>
                                        {{ $course->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="subject_id" class="form-label">Disciplina *</label>
                            <select class="form-select @error('subject_id') is-invalid @enderror" 
                                    id="subject_id" 
                                    name="subject_id" 
                                    required>
                                <option value="">Selecione uma disciplina</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id', $subjectId) == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="module_id" class="form-label">Módulo *</label>
                            <select class="form-select @error('module_id') is-invalid @enderror" 
                                    id="module_id" 
                                    name="module_id" 
                                    required>
                                <option value="">Selecione um módulo</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module->id }}" {{ old('module_id') == $module->id ? 'selected' : '' }}>
                                        {{ $module->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('module_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dados da Questão -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-question-circle"></i>
                        Dados da Questão
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="title" class="form-label">Título da Questão *</label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="type" class="form-label">Tipo de Questão *</label>
                            <select class="form-select @error('type') is-invalid @enderror" 
                                    id="type" 
                                    name="type" 
                                    required>
                                <option value="">Selecione o tipo</option>
                                <option value="multiple_choice" {{ old('type') == 'multiple_choice' ? 'selected' : '' }}>
                                    Múltipla Escolha
                                </option>
                                <option value="true_false" {{ old('type') == 'true_false' ? 'selected' : '' }}>
                                    Verdadeiro/Falso
                                </option>
                                <option value="essay" {{ old('type') == 'essay' ? 'selected' : '' }}>
                                    Dissertativa
                                </option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="question_text" class="form-label">Texto da Questão *</label>
                            <textarea class="form-control @error('question_text') is-invalid @enderror" 
                                      id="question_text" 
                                      name="question_text" 
                                      rows="4" 
                                      required>{{ old('question_text') }}</textarea>
                            @error('question_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Opções (para múltipla escolha) -->
                        <div id="options-container" class="col-md-12 mb-3" style="display: none;">
                            <label class="form-label">Opções de Resposta</label>
                            <div id="options-list">
                                @for($i = 0; $i < 5; $i++)
                                    <div class="input-group mb-2">
                                        <span class="input-group-text">{{ chr(65 + $i) }}</span>
                                        <input type="text" 
                                               class="form-control" 
                                               name="options[]" 
                                               placeholder="Digite a opção {{ chr(65 + $i) }}"
                                               value="{{ old('options.' . $i) }}">
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="correct_answer" class="form-label">Resposta Correta *</label>
                            <input type="text" 
                                   class="form-control @error('correct_answer') is-invalid @enderror" 
                                   id="correct_answer" 
                                   name="correct_answer" 
                                   value="{{ old('correct_answer') }}" 
                                   required>
                            @error('correct_answer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted" id="correct_answer_help">
                                Para múltipla escolha, digite a letra (A, B, C...). Para V/F, digite "Verdadeiro" ou "Falso".
                            </small>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="points" class="form-label">Pontos *</label>
                            <input type="number" 
                                   class="form-control @error('points') is-invalid @enderror" 
                                   id="points" 
                                   name="points" 
                                   value="{{ old('points', 1) }}" 
                                   min="0" 
                                   step="0.1" 
                                   required>
                            @error('points')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="difficulty_level" class="form-label">Dificuldade *</label>
                            <select class="form-select @error('difficulty_level') is-invalid @enderror" 
                                    id="difficulty_level" 
                                    name="difficulty_level" 
                                    required>
                                <option value="">Selecione</option>
                                <option value="easy" {{ old('difficulty_level') == 'easy' ? 'selected' : '' }}>Fácil</option>
                                <option value="medium" {{ old('difficulty_level', 'medium') == 'medium' ? 'selected' : '' }}>Médio</option>
                                <option value="hard" {{ old('difficulty_level') == 'hard' ? 'selected' : '' }}>Difícil</option>
                            </select>
                            @error('difficulty_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="explanation" class="form-label">Explicação/Comentário</label>
                            <textarea class="form-control @error('explanation') is-invalid @enderror" 
                                      id="explanation" 
                                      name="explanation" 
                                      rows="3">{{ old('explanation') }}</textarea>
                            @error('explanation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Explicação que será mostrada após a resposta (opcional)</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text" 
                                   class="form-control @error('tags') is-invalid @enderror" 
                                   id="tags" 
                                   name="tags" 
                                   value="{{ old('tags') }}" 
                                   placeholder="tag1, tag2, tag3">
                            @error('tags')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Separe as tags por vírgulas</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Questão Ativa
                                </label>
                                <small class="d-block text-muted">Se marcado, a questão ficará disponível para uso</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('question-bank.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i>
                    Criar Questão
                </button>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-info-circle"></i>
                    Instruções
                </h5>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <strong>1. Organização:</strong> Selecione curso → disciplina → módulo
                    </li>
                    <li class="mb-2">
                        <strong>2. Tipo:</strong> Escolha entre múltipla escolha, V/F ou dissertativa
                    </li>
                    <li class="mb-2">
                        <strong>3. Resposta:</strong> Para múltipla escolha use letras (A, B, C...)
                    </li>
                    <li class="mb-2">
                        <strong>4. Tags:</strong> Ajudam na organização e busca
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const courseSelect = document.getElementById('course_id');
    const subjectSelect = document.getElementById('subject_id');
    const moduleSelect = document.getElementById('module_id');
    const typeSelect = document.getElementById('type');
    const optionsContainer = document.getElementById('options-container');
    const correctAnswerHelp = document.getElementById('correct_answer_help');

    // Quando o curso muda, atualiza as disciplinas
    courseSelect.addEventListener('change', function() {
        const courseId = this.value;
        
        // Limpa disciplinas e módulos
        subjectSelect.innerHTML = '<option value="">Selecione uma disciplina</option>';
        moduleSelect.innerHTML = '<option value="">Selecione um módulo</option>';
        
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
        moduleSelect.innerHTML = '<option value="">Selecione um módulo</option>';
        
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

    // Quando o tipo de questão muda
    typeSelect.addEventListener('change', function() {
        const type = this.value;
        
        if (type === 'multiple_choice') {
            optionsContainer.style.display = 'block';
            correctAnswerHelp.textContent = 'Digite a letra da resposta correta (A, B, C, D ou E)';
        } else if (type === 'true_false') {
            optionsContainer.style.display = 'none';
            correctAnswerHelp.textContent = 'Digite "Verdadeiro" ou "Falso"';
        } else if (type === 'essay') {
            optionsContainer.style.display = 'none';
            correctAnswerHelp.textContent = 'Digite uma resposta modelo ou critérios de avaliação';
        } else {
            optionsContainer.style.display = 'none';
            correctAnswerHelp.textContent = 'Para múltipla escolha, digite a letra (A, B, C...). Para V/F, digite "Verdadeiro" ou "Falso".';
        }
    });

    // Inicializa baseado no valor atual
    if (typeSelect.value) {
        typeSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
