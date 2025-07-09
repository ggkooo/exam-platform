@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="bi bi-plus-circle"></i>
            Adicionar Questão
        </h1>
        <small class="text-muted">Prova: {{ $exam->title }}</small>
    </div>
    <a href="{{ route('exams.questions.index', $exam) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
        Voltar
    </a>
</div>

<!-- Seletor de modo de adição -->
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Como deseja adicionar a questão?</h5>
        <div class="row">
            <div class="col-md-6">
                <div class="d-grid">
                    <button type="button" class="btn btn-outline-primary" id="btn-from-bank" onclick="showBankSelection()">
                        <i class="bi bi-collection"></i>
                        <div class="mt-2">
                            <strong>Do Banco de Questões</strong>
                            <br><small>Selecionar questões já criadas</small>
                        </div>
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-grid">
                    <button type="button" class="btn btn-outline-success" id="btn-create-new" onclick="showNewQuestionForm()">
                        <i class="bi bi-plus-circle"></i>
                        <div class="mt-2">
                            <strong>Criar Nova Questão</strong>
                            <br><small>Criar uma questão personalizada</small>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Seção: Seleção do Banco de Questões -->
<div id="bank-selection" class="card" style="display: none;">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-collection"></i>
            Selecionar do Banco de Questões
        </h5>
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="filter_course" class="form-label">Curso</label>
                <select class="form-select" id="filter_course">
                    <option value="">Todos os cursos</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter_subject" class="form-label">Disciplina</label>
                <select class="form-select" id="filter_subject">
                    <option value="">Todas as disciplinas</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter_module" class="form-label">Módulo</label>
                <select class="form-select" id="filter_module">
                    <option value="">Todos os módulos</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter_difficulty" class="form-label">Dificuldade</label>
                <select class="form-select" id="filter_difficulty">
                    <option value="">Todas</option>
                    <option value="easy">Fácil</option>
                    <option value="medium">Médio</option>
                    <option value="hard">Difícil</option>
                </select>
            </div>
        </div>

        <!-- Lista de questões -->
        <div id="questions-list">
            <div class="text-center py-4">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mt-2">Carregando questões...</p>
            </div>
        </div>

        <!-- Botão para adicionar selecionadas -->
        <div class="mt-3">
            <button type="button" class="btn btn-success" id="add-selected-questions" onclick="addSelectedQuestions()" disabled>
                <i class="bi bi-plus-circle"></i>
                Adicionar Questões Selecionadas
            </button>
        </div>
    </div>
</div>

<!-- Seção: Nova Questão -->
<div id="new-question-form" class="card" style="display: none;">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-plus-circle"></i>
            Criar Nova Questão
        </h5>
    </div>
    <div class="card-body">
        <form action="{{ route('exams.questions.store', $exam) }}" method="POST" id="questionForm">
            @csrf
            
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label for="type" class="form-label">Tipo de Questão *</label>
                    <select class="form-select @error('type') is-invalid @enderror" 
                            id="type" 
                            name="type" 
                            required
                            onchange="toggleQuestionType()">
                        <option value="">Selecione o tipo</option>
                        <option value="multiple_choice" {{ old('type') === 'multiple_choice' ? 'selected' : '' }}>
                            Múltipla Escolha
                        </option>
                        <option value="true_false" {{ old('type') === 'true_false' ? 'selected' : '' }}>
                            Verdadeiro ou Falso
                        </option>
                        <option value="essay" {{ old('type') === 'essay' ? 'selected' : '' }}>
                            Dissertativa
                        </option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
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
            </div>

            <!-- Opções para Múltipla Escolha -->
            <div id="multiple_choice_options" style="display: none;">
                <h5 class="mb-3">Opções de Resposta</h5>
                <div id="options-container">
                    @for($i = 0; $i < 4; $i++)
                    <div class="option-group mb-3" data-option="{{ $i }}">
                        <div class="d-flex align-items-center">
                            <div class="form-check me-3">
                                <input class="form-check-input" 
                                       type="radio" 
                                       name="correct_option" 
                                       id="correct_{{ $i }}" 
                                       value="{{ $i }}"
                                       onchange="updateCorrectAnswer()"
                                       {{ old('correct_option') == $i ? 'checked' : '' }}>
                                <label class="form-check-label" for="correct_{{ $i }}">
                                    Correta
                                </label>
                            </div>
                            <div class="flex-grow-1">
                                <input type="text" 
                                       class="form-control" 
                                       name="options[{{ $i }}]" 
                                       placeholder="Opção {{ chr(65 + $i) }}"
                                       value="{{ old('options.' . $i) }}">
                            </div>
                            @if($i >= 2)
                            <button type="button" 
                                    class="btn btn-outline-danger btn-sm ms-2" 
                                    onclick="removeOption({{ $i }})"
                                    style="display: none;">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                    @endfor
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addOption()">
                    <i class="bi bi-plus"></i>
                    Adicionar Opção
                </button>
            </div>

            <!-- Resposta para Verdadeiro/Falso -->
            <div id="true_false_answer" style="display: none;">
                <h5 class="mb-3">Resposta Correta</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="radio" 
                                   name="tf_answer" 
                                   id="answer_true" 
                                   value="true"
                                   onchange="updateCorrectAnswer()"
                                   {{ old('tf_answer') === 'true' ? 'checked' : '' }}>
                            <label class="form-check-label" for="answer_true">
                                Verdadeiro
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="radio" 
                                   name="tf_answer" 
                                   id="answer_false" 
                                   value="false"
                                   onchange="updateCorrectAnswer()"
                                   {{ old('tf_answer') === 'false' ? 'checked' : '' }}>
                            <label class="form-check-label" for="answer_false">
                                Falso
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resposta para Dissertativa -->
            <div id="essay_answer" style="display: none;">
                <h5 class="mb-3">Critérios de Avaliação</h5>
                <div class="mb-3">
                    <label for="essay_criteria" class="form-label">Resposta Esperada / Critérios</label>
                    <textarea class="form-control" 
                              id="essay_criteria" 
                              name="essay_criteria" 
                              rows="3"
                              oninput="updateCorrectAnswer()"
                              placeholder="Descreva a resposta esperada ou os critérios de avaliação...">{{ old('essay_criteria') }}</textarea>
                </div>
            </div>

            <div class="mb-3">
                <label for="explanation" class="form-label">Explicação (opcional)</label>
                <textarea class="form-control @error('explanation') is-invalid @enderror" 
                          id="explanation" 
                          name="explanation" 
                          rows="3"
                          placeholder="Explicação da resposta correta (será mostrada após a correção)">{{ old('explanation') }}</textarea>
                @error('explanation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" 
                           type="checkbox" 
                           id="is_active" 
                           name="is_active" 
                           {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Questão Ativa
                    </label>
                    <small class="d-block text-muted">Se desmarcado, a questão não aparecerá na prova</small>
                </div>
            </div>

            <input type="hidden" id="correct_answer" name="correct_answer" value="{{ old('correct_answer') }}">

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-secondary" onclick="hideAllSections()">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i>
                    Criar Questão
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let optionCount = 4;
let selectedQuestions = [];

// Funções principais de navegação
function showBankSelection() {
    document.getElementById('bank-selection').style.display = 'block';
    document.getElementById('new-question-form').style.display = 'none';
    document.getElementById('btn-from-bank').classList.add('btn-primary');
    document.getElementById('btn-from-bank').classList.remove('btn-outline-primary');
    document.getElementById('btn-create-new').classList.add('btn-outline-success');
    document.getElementById('btn-create-new').classList.remove('btn-success');
    
    loadQuestions();
    loadCourses();
}

function showNewQuestionForm() {
    document.getElementById('new-question-form').style.display = 'block';
    document.getElementById('bank-selection').style.display = 'none';
    document.getElementById('btn-create-new').classList.add('btn-success');
    document.getElementById('btn-create-new').classList.remove('btn-outline-success');
    document.getElementById('btn-from-bank').classList.add('btn-outline-primary');
    document.getElementById('btn-from-bank').classList.remove('btn-primary');
}

function hideAllSections() {
    document.getElementById('bank-selection').style.display = 'none';
    document.getElementById('new-question-form').style.display = 'none';
    document.getElementById('btn-from-bank').classList.add('btn-outline-primary');
    document.getElementById('btn-from-bank').classList.remove('btn-primary');
    document.getElementById('btn-create-new').classList.add('btn-outline-success');
    document.getElementById('btn-create-new').classList.remove('btn-success');
}

// Funções do banco de questões
function loadCourses() {
    fetch('/courses/json')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(courses => {
            const select = document.getElementById('filter_course');
            select.innerHTML = '<option value="">Todos os cursos</option>';
            courses.forEach(course => {
                select.innerHTML += `<option value="${course.id}">${course.name}</option>`;
            });
        })
        .catch(error => {
            console.error('Erro ao carregar cursos:', error);
            const select = document.getElementById('filter_course');
            select.innerHTML = '<option value="">Erro ao carregar cursos</option>';
        });
}

function loadQuestions() {
    const courseId = document.getElementById('filter_course').value;
    const subjectId = document.getElementById('filter_subject').value;
    const moduleId = document.getElementById('filter_module').value;
    const difficulty = document.getElementById('filter_difficulty').value;
    
    const params = new URLSearchParams();
    if (courseId) params.append('course_id', courseId);
    if (subjectId) params.append('subject_id', subjectId);
    if (moduleId) params.append('module_id', moduleId);
    if (difficulty) params.append('difficulty_level', difficulty);
    
    fetch(`/question-bank/questions-json?${params.toString()}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            displayQuestions(data.questions || []);
        })
        .catch(error => {
            console.error('Erro ao carregar questões:', error);
            document.getElementById('questions-list').innerHTML = 
                `<div class="alert alert-danger">Erro ao carregar questões: ${error.message}</div>`;
        });
}

function displayQuestions(questions) {
    const container = document.getElementById('questions-list');
    
    if (questions.length === 0) {
        container.innerHTML = '<div class="alert alert-info">Nenhuma questão encontrada com os filtros selecionados.</div>';
        return;
    }
    
    let html = '';
    questions.forEach(question => {
        // Montar caminho hierárquico
        let hierarchyPath = '';
        if (question.course) hierarchyPath += question.course.name;
        if (question.subject) hierarchyPath += (hierarchyPath ? ' > ' : '') + question.subject.name;
        if (question.module) hierarchyPath += (hierarchyPath ? ' > ' : '') + question.module.name;
        if (!hierarchyPath) hierarchyPath = 'Sem organização';
        
        // Determinar tipo da questão
        let questionType = '';
        switch(question.type) {
            case 'multiple_choice': questionType = 'Múltipla Escolha'; break;
            case 'true_false': questionType = 'Verdadeiro/Falso'; break;
            case 'essay': questionType = 'Dissertativa'; break;
            default: questionType = question.type;
        }
        
        // Determinar badge de dificuldade
        let difficultyBadge = '';
        if (question.difficulty_level) {
            let badgeClass = '';
            let difficultyText = '';
            switch(question.difficulty_level) {
                case 'easy': 
                    badgeClass = 'success';
                    difficultyText = 'Fácil';
                    break;
                case 'medium': 
                    badgeClass = 'warning';
                    difficultyText = 'Médio';
                    break;
                case 'hard': 
                    badgeClass = 'danger';
                    difficultyText = 'Difícil';
                    break;
                default:
                    badgeClass = 'secondary';
                    difficultyText = question.difficulty_level;
            }
            difficultyBadge = `<span class="badge bg-${badgeClass}">${difficultyText}</span>`;
        }
        
        html += `
            <div class="card mb-2">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="form-check me-3">
                            <input class="form-check-input question-checkbox" type="checkbox" 
                                   value="${question.id}" onchange="updateSelectedQuestions()">
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${question.title || 'Questão sem título'}</h6>
                            <p class="mb-1 text-truncate" style="max-height: 3em; overflow: hidden;">${question.question_text}</p>
                            <small class="text-muted">
                                ${hierarchyPath} • 
                                ${questionType} • 
                                ${question.points || 1} pts
                                ${difficultyBadge ? ' • ' + difficultyBadge : ''}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function updateSelectedQuestions() {
    const checkboxes = document.querySelectorAll('.question-checkbox:checked');
    selectedQuestions = Array.from(checkboxes).map(cb => cb.value);
    
    const button = document.getElementById('add-selected-questions');
    button.disabled = selectedQuestions.length === 0;
    
    if (selectedQuestions.length > 0) {
        button.innerHTML = `<i class="bi bi-plus-circle"></i> Adicionar ${selectedQuestions.length} Questão(ões) Selecionada(s)`;
    } else {
        button.innerHTML = '<i class="bi bi-plus-circle"></i> Adicionar Questões Selecionadas';
    }
}

function addSelectedQuestions() {
    if (selectedQuestions.length === 0) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("exams.questions.store", $exam) }}';
    
    // Token CSRF
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    
    // Questões selecionadas
    const questionsInput = document.createElement('input');
    questionsInput.type = 'hidden';
    questionsInput.name = 'question_ids';
    questionsInput.value = JSON.stringify(selectedQuestions);
    form.appendChild(questionsInput);
    
    document.body.appendChild(form);
    form.submit();
}

// Event listeners para filtros
document.getElementById('filter_course').addEventListener('change', function() {
    const courseId = this.value;
    const subjectSelect = document.getElementById('filter_subject');
    const moduleSelect = document.getElementById('filter_module');
    
    subjectSelect.innerHTML = '<option value="">Carregando...</option>';
    moduleSelect.innerHTML = '<option value="">Todos os módulos</option>';
    
    if (courseId) {
        fetch(`/question-bank/subjects-by-course?course_id=${courseId}`)
            .then(response => response.json())
            .then(subjects => {
                subjectSelect.innerHTML = '<option value="">Todas as disciplinas</option>';
                subjects.forEach(subject => {
                    subjectSelect.innerHTML += `<option value="${subject.id}">${subject.name}</option>`;
                });
                loadQuestions();
            });
    } else {
        subjectSelect.innerHTML = '<option value="">Todas as disciplinas</option>';
        loadQuestions();
    }
});

document.getElementById('filter_subject').addEventListener('change', function() {
    const subjectId = this.value;
    const moduleSelect = document.getElementById('filter_module');
    
    moduleSelect.innerHTML = '<option value="">Carregando...</option>';
    
    if (subjectId) {
        fetch(`/question-bank/modules-by-subject?subject_id=${subjectId}`)
            .then(response => response.json())
            .then(modules => {
                moduleSelect.innerHTML = '<option value="">Todos os módulos</option>';
                modules.forEach(module => {
                    moduleSelect.innerHTML += `<option value="${module.id}">${module.name}</option>`;
                });
                loadQuestions();
            });
    } else {
        moduleSelect.innerHTML = '<option value="">Todos os módulos</option>';
        loadQuestions();
    }
});

document.getElementById('filter_module').addEventListener('change', loadQuestions);
document.getElementById('filter_difficulty').addEventListener('change', loadQuestions);

// Funções para nova questão
function toggleQuestionType() {
    const type = document.getElementById('type').value;
    
    // Esconde todas as seções
    document.getElementById('multiple_choice_options').style.display = 'none';
    document.getElementById('true_false_answer').style.display = 'none';
    document.getElementById('essay_answer').style.display = 'none';
    
    // Remove required dos campos
    document.querySelectorAll('input[name^="options"]').forEach(input => {
        input.removeAttribute('required');
    });
    document.querySelectorAll('input[name="tf_answer"]').forEach(input => {
        input.removeAttribute('required');
    });
    
    // Mostra a seção correspondente
    if (type === 'multiple_choice') {
        document.getElementById('multiple_choice_options').style.display = 'block';
        document.querySelectorAll('input[name^="options"]').forEach((input, index) => {
            if (index < 2) input.setAttribute('required', 'required');
        });
    } else if (type === 'true_false') {
        document.getElementById('true_false_answer').style.display = 'block';
        document.querySelectorAll('input[name="tf_answer"]').forEach(input => {
            input.setAttribute('required', 'required');
        });
    } else if (type === 'essay') {
        document.getElementById('essay_answer').style.display = 'block';
    }
    
    updateCorrectAnswer();
}

function addOption() {
    const container = document.getElementById('options-container');
    const newIndex = optionCount;
    
    if (newIndex >= 10) {
        alert('Máximo de 10 opções permitidas');
        return;
    }
    
    const optionDiv = document.createElement('div');
    optionDiv.className = 'option-group mb-3';
    optionDiv.setAttribute('data-option', newIndex);
    
    optionDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="form-check me-3">
                <input class="form-check-input" 
                       type="radio" 
                       name="correct_option" 
                       id="correct_${newIndex}" 
                       value="${newIndex}"
                       onchange="updateCorrectAnswer()">
                <label class="form-check-label" for="correct_${newIndex}">
                    Correta
                </label>
            </div>
            <div class="flex-grow-1">
                <input type="text" 
                       class="form-control" 
                       name="options[${newIndex}]" 
                       placeholder="Opção ${String.fromCharCode(65 + newIndex)}">
            </div>
            <button type="button" 
                    class="btn btn-outline-danger btn-sm ms-2" 
                    onclick="removeOption(${newIndex})">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    
    container.appendChild(optionDiv);
    optionCount++;
    
    // Mostra botões de remover se há mais de 2 opções
    if (optionCount > 2) {
        document.querySelectorAll('.option-group').forEach((group, index) => {
            if (index >= 2) {
                const removeBtn = group.querySelector('button');
                if (removeBtn) removeBtn.style.display = 'block';
            }
        });
    }
}

function removeOption(index) {
    const optionGroup = document.querySelector(`[data-option="${index}"]`);
    if (optionGroup) {
        optionGroup.remove();
        optionCount--;
        
        // Esconde botões de remover se há apenas 2 opções
        if (optionCount <= 2) {
            document.querySelectorAll('.option-group button').forEach(btn => {
                btn.style.display = 'none';
            });
        }
        
        updateCorrectAnswer();
    }
}

function updateCorrectAnswer() {
    const type = document.getElementById('type').value;
    let correctAnswer = '';
    
    if (type === 'multiple_choice') {
        const selectedOption = document.querySelector('input[name="correct_option"]:checked');
        if (selectedOption) {
            correctAnswer = String.fromCharCode(65 + parseInt(selectedOption.value));
        }
    } else if (type === 'true_false') {
        const selectedAnswer = document.querySelector('input[name="tf_answer"]:checked');
        if (selectedAnswer) {
            correctAnswer = selectedAnswer.value === 'true' ? 'Verdadeiro' : 'Falso';
        }
    } else if (type === 'essay') {
        const criteria = document.getElementById('essay_criteria').value;
        correctAnswer = criteria || 'Resposta dissertativa';
    }
    
    document.getElementById('correct_answer').value = correctAnswer;
}

// Form submit validation
document.getElementById('questionForm').addEventListener('submit', function(e) {
    const type = document.getElementById('type').value;
    
    if (type === 'multiple_choice') {
        const correctOption = document.querySelector('input[name="correct_option"]:checked');
        if (!correctOption) {
            e.preventDefault();
            alert('Por favor, selecione a opção correta.');
            return;
        }
        
        const options = document.querySelectorAll('input[name^="options"]:required');
        for (let option of options) {
            if (!option.value.trim()) {
                e.preventDefault();
                alert('Por favor, preencha todas as opções obrigatórias.');
                return;
            }
        }
    } else if (type === 'true_false') {
        const tfAnswer = document.querySelector('input[name="tf_answer"]:checked');
        if (!tfAnswer) {
            e.preventDefault();
            alert('Por favor, selecione a resposta correta.');
            return;
        }
    }
    
    updateCorrectAnswer();
});
</script>
@endsection
