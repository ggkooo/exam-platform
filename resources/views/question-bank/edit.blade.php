@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="bi bi-pencil"></i>
        Editar Questão
    </h1>
    <a href="{{ route('question-bank.show', $question) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
        Voltar
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('question-bank.update', $question) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <!-- Seleção de Curso -->
                <div class="col-md-4 mb-3">
                    <label for="course_id" class="form-label">Curso *</label>
                    <select class="form-select" id="course_id" name="course_id" required>
                        <option value="">Selecione um curso</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ old('course_id', $question->course_id) == $course->id ? 'selected' : '' }}>
                                {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('course_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Seleção de Disciplina -->
                <div class="col-md-4 mb-3">
                    <label for="subject_id" class="form-label">Disciplina *</label>
                    <select class="form-select" id="subject_id" name="subject_id" required>
                        <option value="">Selecione uma disciplina</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id', $question->subject_id) == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Seleção de Módulo -->
                <div class="col-md-4 mb-3">
                    <label for="module_id" class="form-label">Módulo *</label>
                    <select class="form-select" id="module_id" name="module_id" required>
                        <option value="">Selecione um módulo</option>
                        @foreach($modules as $module)
                            <option value="{{ $module->id }}" {{ old('module_id', $question->module_id) == $module->id ? 'selected' : '' }}>
                                {{ $module->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('module_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <!-- Título -->
                <div class="col-md-8 mb-3">
                    <label for="title" class="form-label">Título da Questão *</label>
                    <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $question->title) }}" required>
                    @error('title')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Tipo -->
                <div class="col-md-4 mb-3">
                    <label for="type" class="form-label">Tipo de Questão *</label>
                    <select class="form-select" id="type" name="type" required>
                        <option value="">Selecione o tipo</option>
                        <option value="multiple_choice" {{ old('type', $question->type) == 'multiple_choice' ? 'selected' : '' }}>Múltipla Escolha</option>
                        <option value="true_false" {{ old('type', $question->type) == 'true_false' ? 'selected' : '' }}>Verdadeiro/Falso</option>
                        <option value="essay" {{ old('type', $question->type) == 'essay' ? 'selected' : '' }}>Dissertativa</option>
                    </select>
                    @error('type')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Enunciado -->
            <div class="mb-3">
                <label for="question_text" class="form-label">Enunciado da Questão *</label>
                <textarea class="form-control" id="question_text" name="question_text" rows="4" required>{{ old('question_text', $question->question_text) }}</textarea>
                @error('question_text')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <!-- Opções (para múltipla escolha) -->
            <div id="options_container" style="display: {{ old('type', $question->type) == 'multiple_choice' ? 'block' : 'none' }};">
                <div class="mb-3">
                    <label class="form-label">Opções de Resposta</label>
                    <div id="options_list">
                        @php
                            $options = old('options', $question->formatted_options ?: ['', '', '', '']);
                            if (empty($options)) $options = ['', '', '', ''];
                        @endphp
                        @foreach($options as $index => $option)
                            <div class="input-group mb-2">
                                <span class="input-group-text">{{ chr(65 + $index) }}</span>
                                <input type="text" class="form-control" name="options[]" value="{{ $option }}" placeholder="Digite a opção {{ chr(65 + $index) }}">
                                @if($index >= 2)
                                    <button type="button" class="btn btn-outline-danger remove-option">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add_option">
                        <i class="bi bi-plus"></i>
                        Adicionar Opção
                    </button>
                </div>
            </div>

            <div class="row">
                <!-- Resposta Correta -->
                <div class="col-md-4 mb-3">
                    <label for="correct_answer" class="form-label">Resposta Correta *</label>
                    <input type="text" class="form-control" id="correct_answer" name="correct_answer" value="{{ old('correct_answer', $question->correct_answer) }}" required>
                    <small class="text-muted">Para múltipla escolha, use a letra (A, B, C, etc.)</small>
                    @error('correct_answer')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Pontos -->
                <div class="col-md-4 mb-3">
                    <label for="points" class="form-label">Pontos *</label>
                    <input type="number" class="form-control" id="points" name="points" value="{{ old('points', $question->points) }}" min="0" step="0.1" required>
                    @error('points')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Nível de Dificuldade -->
                <div class="col-md-4 mb-3">
                    <label for="difficulty_level" class="form-label">Nível de Dificuldade *</label>
                    <select class="form-select" id="difficulty_level" name="difficulty_level" required>
                        <option value="">Selecione o nível</option>
                        <option value="easy" {{ old('difficulty_level', $question->difficulty_level) == 'easy' ? 'selected' : '' }}>Fácil</option>
                        <option value="medium" {{ old('difficulty_level', $question->difficulty_level) == 'medium' ? 'selected' : '' }}>Médio</option>
                        <option value="hard" {{ old('difficulty_level', $question->difficulty_level) == 'hard' ? 'selected' : '' }}>Difícil</option>
                    </select>
                    @error('difficulty_level')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Explicação -->
            <div class="mb-3">
                <label for="explanation" class="form-label">Explicação (Opcional)</label>
                <textarea class="form-control" id="explanation" name="explanation" rows="3" placeholder="Explicação sobre a resposta correta">{{ old('explanation', $question->explanation) }}</textarea>
                @error('explanation')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <!-- Tags -->
            <div class="mb-3">
                <label for="tags" class="form-label">Tags (Opcional)</label>
                <input type="text" class="form-control" id="tags" name="tags" value="{{ old('tags', is_array($question->tags) ? implode(', ', $question->tags) : '') }}" placeholder="Separe as tags por vírgula">
                <small class="text-muted">Ex: matemática, álgebra, equações</small>
                @error('tags')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <!-- Status -->
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" {{ old('is_active', $question->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Questão ativa
                    </label>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-lg"></i>
                    Atualizar Questão
                </button>
                <a href="{{ route('question-bank.show', $question) }}" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const courseSelect = document.getElementById('course_id');
    const subjectSelect = document.getElementById('subject_id');
    const moduleSelect = document.getElementById('module_id');
    const typeSelect = document.getElementById('type');
    const optionsContainer = document.getElementById('options_container');
    const addOptionBtn = document.getElementById('add_option');

    // Carrega disciplinas quando curso é selecionado
    courseSelect.addEventListener('change', function() {
        const courseId = this.value;
        subjectSelect.innerHTML = '<option value="">Carregando...</option>';
        moduleSelect.innerHTML = '<option value="">Selecione um módulo</option>';

        if (courseId) {
            fetch(`/question-bank/subjects-by-course?course_id=${courseId}`)
                .then(response => response.json())
                .then(subjects => {
                    subjectSelect.innerHTML = '<option value="">Selecione uma disciplina</option>';
                    subjects.forEach(subject => {
                        const option = document.createElement('option');
                        option.value = subject.id;
                        option.textContent = subject.name;
                        if (subject.id == {{ $question->subject_id ?? 'null' }}) {
                            option.selected = true;
                        }
                        subjectSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Erro ao carregar disciplinas:', error);
                    subjectSelect.innerHTML = '<option value="">Erro ao carregar</option>';
                });
        } else {
            subjectSelect.innerHTML = '<option value="">Selecione uma disciplina</option>';
        }
    });

    // Carrega módulos quando disciplina é selecionada
    subjectSelect.addEventListener('change', function() {
        const subjectId = this.value;
        moduleSelect.innerHTML = '<option value="">Carregando...</option>';

        if (subjectId) {
            fetch(`/question-bank/modules-by-subject?subject_id=${subjectId}`)
                .then(response => response.json())
                .then(modules => {
                    moduleSelect.innerHTML = '<option value="">Selecione um módulo</option>';
                    modules.forEach(module => {
                        const option = document.createElement('option');
                        option.value = module.id;
                        option.textContent = module.name;
                        if (module.id == {{ $question->module_id ?? 'null' }}) {
                            option.selected = true;
                        }
                        moduleSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Erro ao carregar módulos:', error);
                    moduleSelect.innerHTML = '<option value="">Erro ao carregar</option>';
                });
        } else {
            moduleSelect.innerHTML = '<option value="">Selecione um módulo</option>';
        }
    });

    // Mostra/esconde opções baseado no tipo
    typeSelect.addEventListener('change', function() {
        optionsContainer.style.display = this.value === 'multiple_choice' ? 'block' : 'none';
    });

    // Adicionar nova opção
    addOptionBtn.addEventListener('click', function() {
        const optionsList = document.getElementById('options_list');
        const currentOptions = optionsList.querySelectorAll('.input-group');
        const newIndex = currentOptions.length;
        
        if (newIndex < 10) { // Máximo 10 opções
            const newOption = document.createElement('div');
            newOption.className = 'input-group mb-2';
            newOption.innerHTML = `
                <span class="input-group-text">${String.fromCharCode(65 + newIndex)}</span>
                <input type="text" class="form-control" name="options[]" placeholder="Digite a opção ${String.fromCharCode(65 + newIndex)}">
                <button type="button" class="btn btn-outline-danger remove-option">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            optionsList.appendChild(newOption);
        }
    });

    // Remover opção
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-option')) {
            const optionGroup = e.target.closest('.input-group');
            optionGroup.remove();
            
            // Reordena as letras
            const optionsList = document.getElementById('options_list');
            const options = optionsList.querySelectorAll('.input-group');
            options.forEach((option, index) => {
                const label = option.querySelector('.input-group-text');
                const input = option.querySelector('input');
                label.textContent = String.fromCharCode(65 + index);
                input.placeholder = `Digite a opção ${String.fromCharCode(65 + index)}`;
            });
        }
    });
});
</script>
@endsection
