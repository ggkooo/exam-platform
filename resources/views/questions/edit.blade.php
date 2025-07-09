@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="bi bi-pencil"></i>
            Editar Questão
        </h1>
        <small class="text-muted">Prova: {{ $exam->title }} | Questão {{ $question->order }}º</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('exams.questions.show', [$exam, $question]) }}" class="btn btn-outline-primary">
            <i class="bi bi-eye"></i>
            Visualizar
        </a>
        <a href="{{ route('exams.questions.index', $exam) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
            Voltar
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('exams.questions.update', [$exam, $question]) }}" method="POST" id="questionForm">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label for="type" class="form-label">Tipo de Questão *</label>
                    <select class="form-select @error('type') is-invalid @enderror" 
                            id="type" 
                            name="type" 
                            required
                            onchange="toggleQuestionType()">
                        <option value="">Selecione o tipo</option>
                        <option value="multiple_choice" {{ old('type', $question->type) === 'multiple_choice' ? 'selected' : '' }}>
                            Múltipla Escolha
                        </option>
                        <option value="true_false" {{ old('type', $question->type) === 'true_false' ? 'selected' : '' }}>
                            Verdadeiro ou Falso
                        </option>
                        <option value="essay" {{ old('type', $question->type) === 'essay' ? 'selected' : '' }}>
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
                           value="{{ old('points', isset($examQuestion) ? $examQuestion->effective_points : $question->points) }}" 
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
                              required>{{ old('question_text', $question->question_text) }}</textarea>
                    @error('question_text')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Opções para Múltipla Escolha -->
            <div id="multiple_choice_options" style="display: none;">
                <h5 class="mb-3">Opções de Resposta</h5>
                <div id="options-container">
                    @php
                        $options = old('options', $question->formatted_options ?? []);
                        $correctOption = null;
                        if ($question->type === 'multiple_choice' && $question->correct_answer) {
                            $correctOption = ord($question->correct_answer) - 65;
                        }
                    @endphp
                    
                    @for($i = 0; $i < max(4, count($options)); $i++)
                    <div class="option-group mb-3" data-option="{{ $i }}">
                        <div class="d-flex align-items-center">
                            <div class="form-check me-3">
                                <input class="form-check-input" 
                                       type="radio" 
                                       name="correct_option" 
                                       id="correct_{{ $i }}" 
                                       value="{{ $i }}"
                                       onchange="updateCorrectAnswer()"
                                       {{ old('correct_option', $correctOption) == $i ? 'checked' : '' }}>
                                <label class="form-check-label" for="correct_{{ $i }}">
                                    Correta
                                </label>
                            </div>
                            <div class="flex-grow-1">
                                <input type="text" 
                                       class="form-control" 
                                       name="options[{{ $i }}]" 
                                       placeholder="Opção {{ chr(65 + $i) }}"
                                       value="{{ $options[$i] ?? '' }}">
                            </div>
                            @if($i >= 2)
                            <button type="button" 
                                    class="btn btn-outline-danger btn-sm ms-2" 
                                    onclick="removeOption({{ $i }})"
                                    style="display: {{ $i < count($options) && count($options) > 2 ? 'block' : 'none' }};">
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
                                   {{ old('tf_answer', $question->correct_answer === 'Verdadeiro' ? 'true' : '') === 'true' ? 'checked' : '' }}>
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
                                   {{ old('tf_answer', $question->correct_answer === 'Falso' ? 'false' : '') === 'false' ? 'checked' : '' }}>
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
                              placeholder="Descreva a resposta esperada ou os critérios de avaliação...">{{ old('essay_criteria', $question->type === 'essay' && $question->correct_answer !== 'Resposta dissertativa' ? $question->correct_answer : '') }}</textarea>
                </div>
            </div>

            <div class="mb-3">
                <label for="explanation" class="form-label">Explicação (opcional)</label>
                <textarea class="form-control @error('explanation') is-invalid @enderror" 
                          id="explanation" 
                          name="explanation" 
                          rows="3"
                          placeholder="Explicação da resposta correta (será mostrada após a correção)">{{ old('explanation', $question->explanation) }}</textarea>
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
                           {{ old('is_active', isset($examQuestion) ? $examQuestion->is_active : $question->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Questão Ativa
                    </label>
                    <small class="d-block text-muted">Se desmarcado, a questão não aparecerá na prova</small>
                </div>
            </div>

            <input type="hidden" id="correct_answer" name="correct_answer" value="{{ old('correct_answer', $question->correct_answer) }}">

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('exams.questions.show', [$exam, $question]) }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i>
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
console.log('=== EDIT VIEW LOADED ===');
console.log('Question data:', {
    id: {{ $question->id }},
    type: '{{ $question->type }}',
    text: '{{ addslashes($question->question_text) }}',
    answer: '{{ addslashes($question->correct_answer) }}',
    points: {{ $question->points }}
});

let optionCount = {{ max(4, count($question->formatted_options ?? [])) }};

// Inicializar na carga da página
document.addEventListener('DOMContentLoaded', function() {
    toggleQuestionType();
});

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
            if (index < 2 && input.value.trim()) input.setAttribute('required', 'required');
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
    console.log('=== FORM SUBMISSION ===');
    const type = document.getElementById('type').value;
    console.log('Type:', type);
    
    if (type === 'multiple_choice') {
        const correctOption = document.querySelector('input[name="correct_option"]:checked');
        if (!correctOption) {
            e.preventDefault();
            alert('Por favor, selecione a opção correta.');
            return;
        }
        
        const options = document.querySelectorAll('input[name^="options"]');
        let filledOptions = 0;
        options.forEach(option => {
            if (option.value.trim()) filledOptions++;
        });
        
        if (filledOptions < 2) {
            e.preventDefault();
            alert('Por favor, preencha pelo menos 2 opções.');
            return;
        }
    } else if (type === 'true_false') {
        const tfAnswer = document.querySelector('input[name="tf_answer"]:checked');
        if (!tfAnswer) {
            e.preventDefault();
            alert('Por favor, selecione a resposta correta.');
            return;
        }
        console.log('TF Answer:', tfAnswer.value);
    } else if (type === 'essay') {
        const criteria = document.getElementById('essay_criteria').value;
        console.log('Essay criteria:', criteria);
    }
    
    updateCorrectAnswer();
    
    // Log final form data before submission
    const formData = new FormData(e.target);
    console.log('Final form data:');
    for (let [key, value] of formData.entries()) {
        console.log(`  ${key}: ${value}`);
    }
    console.log('=== SUBMITTING FORM ===');
});
</script>
@endsection
