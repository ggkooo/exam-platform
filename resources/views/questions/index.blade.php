@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="bi bi-question-circle"></i>
            Questões: {{ $exam->title }}
        </h1>
        <small class="text-muted">{{ $examQuestions->total() }} questões encontradas</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('exams.questions.create', $exam) }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i>
            Nova Questão
        </a>
        <a href="{{ route('exams.show', $exam) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
            Voltar para Prova
        </a>
    </div>
</div>

@if($examQuestions->count() > 0)
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <span class="badge bg-info">{{ $exam->duration_minutes }} min</span>
                    <span class="badge bg-primary">{{ $exam->total_points }} pts total</span>
                    <span class="badge bg-secondary">{{ $examQuestions->total() }} questões</span>
                </div>
                @if($examQuestions->total() > 1)
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleReorderMode()">
                    <i class="bi bi-arrows-move"></i>
                    Reordenar
                </button>
                @endif
            </div>

            <div id="questions-list">
                @foreach($examQuestions as $examQuestion)
                <div class="question-item card mb-3" data-question-id="{{ $examQuestion->question->id }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="drag-handle me-2" style="display: none; cursor: move;">
                                        <i class="bi bi-grip-vertical text-muted"></i>
                                    </span>
                                    <span class="badge bg-secondary me-2">{{ $examQuestion->order }}º</span>
                                    <span class="badge bg-primary me-2">
                                        {{ ucfirst(str_replace('_', ' ', $examQuestion->question->type)) }}
                                    </span>
                                    <span class="badge bg-info me-2">{{ $examQuestion->effective_points }} pts</span>
                                    @if(!$examQuestion->is_active)
                                        <span class="badge bg-danger me-2">Inativa</span>
                                    @endif
                                    @if($examQuestion->question->title || $examQuestion->question->course_id)
                                        <span class="badge bg-success me-2" title="Questão do banco de questões">
                                            <i class="bi bi-collection"></i> Do Banco
                                        </span>
                                    @else
                                        <span class="badge bg-warning me-2" title="Questão criada especificamente para esta prova">
                                            <i class="bi bi-plus-circle"></i> Nova
                                        </span>
                                    @endif
                                </div>
                                
                                <h6 class="card-title">{{ $examQuestion->question->question_text }}</h6>
                                
                                @if($examQuestion->question->type === 'multiple_choice' && $examQuestion->question->options)
                                    <div class="mt-2">
                                        <small class="text-muted">Opções:</small>
                                        <ul class="list-unstyled ms-3 mb-0">
                                            @foreach($examQuestion->question->formatted_options as $index => $option)
                                                <li class="small">
                                                    @if($examQuestion->question->correct_answer === chr(65 + $index))
                                                        <i class="bi bi-check-circle-fill text-success"></i>
                                                    @else
                                                        <i class="bi bi-circle text-muted"></i>
                                                    @endif
                                                    {{ chr(65 + $index) }}. {{ $option }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @elseif($examQuestion->question->type === 'true_false')
                                    <div class="mt-2">
                                        <small class="text-muted">Resposta correta:</small>
                                        <span class="badge bg-success ms-1">
                                            {{ $examQuestion->question->correct_answer }}
                                        </span>
                                    </div>
                                @endif
                                
                                @if($examQuestion->question->explanation)
                                    <div class="mt-2">
                                        <small class="text-muted">Explicação:</small>
                                        <p class="small mb-0 mt-1">{{ Str::limit($examQuestion->question->explanation, 100) }}</p>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="btn-group btn-group-sm ms-3">
                                <a href="{{ route('exams.questions.show', [$exam, $examQuestion->question]) }}" 
                                   class="btn btn-outline-primary" 
                                   title="Ver Questão">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Remover Questão da Prova"
                                        onclick="confirmRemove({{ $examQuestion->question->id }}, '{{ addslashes($examQuestion->question->question_text) }}', {{ ($examQuestion->question->title || $examQuestion->question->course_id) ? 'true' : 'false' }})">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            {{ $examQuestions->links() }}
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-question-circle display-1 text-muted mb-3"></i>
            <h4 class="text-muted">Nenhuma questão encontrada</h4>
            <p class="text-muted">Comece adicionando questões à sua prova.</p>
            <a href="{{ route('exams.questions.create', $exam) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i>
                Adicionar Primeira Questão
            </a>
        </div>
    </div>
@endif

<!-- Modal de confirmação de remoção -->
<div class="modal fade" id="removeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-warning"></i>
                    Remover Questão da Prova
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja <strong>remover esta questão da prova</strong>?</p>
                <p id="question-preview" class="text-muted small border-start border-3 ps-3"></p>
                <div class="alert alert-info mb-3" id="action-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>O que acontece:</strong>
                    <div id="action-details" class="mt-2">
                        <!-- Conteúdo será preenchido dinamicamente -->
                    </div>
                </div>
                <p class="text-warning mb-0">
                    <i class="bi bi-exclamation-triangle"></i>
                    Esta ação não pode ser desfeita.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="removeForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" id="confirmButton">
                        <i class="bi bi-x-circle"></i>
                        Remover da Prova
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let reorderMode = false;

function confirmRemove(questionId, questionText, isFromBank) {
    document.getElementById('removeForm').action = `/exams/{{ $exam->id }}/questions/${questionId}`;
    document.getElementById('question-preview').textContent = questionText;
    
    const actionDetails = document.getElementById('action-details');
    const confirmButton = document.getElementById('confirmButton');
    
    if (isFromBank) {
        actionDetails.innerHTML = `
            <ul class="mb-0">
                <li class="text-success">A questão será removida desta prova</li>
                <li class="text-success">A questão continuará disponível no banco de questões</li>
                <li class="text-info">Você pode adicionar esta questão novamente no futuro</li>
            </ul>
        `;
        confirmButton.innerHTML = '<i class="bi bi-x-circle"></i> Remover da Prova';
        confirmButton.className = 'btn btn-warning';
    } else {
        actionDetails.innerHTML = `
            <ul class="mb-0">
                <li class="text-warning">A questão será removida desta prova</li>
                <li class="text-danger">A questão será excluída permanentemente (foi criada especificamente para esta prova)</li>
                <li class="text-danger">Não será possível recuperar esta questão</li>
            </ul>
        `;
        confirmButton.innerHTML = '<i class="bi bi-trash"></i> Excluir Permanentemente';
        confirmButton.className = 'btn btn-danger';
    }
    
    new bootstrap.Modal(document.getElementById('removeModal')).show();
}

function toggleReorderMode() {
    reorderMode = !reorderMode;
    const dragHandles = document.querySelectorAll('.drag-handle');
    const button = event.target.closest('button');
    
    if (reorderMode) {
        dragHandles.forEach(handle => handle.style.display = 'block');
        button.innerHTML = '<i class="bi bi-check"></i> Salvar Ordem';
        button.className = 'btn btn-sm btn-success';
        initializeSortable();
    } else {
        dragHandles.forEach(handle => handle.style.display = 'none');
        button.innerHTML = '<i class="bi bi-arrows-move"></i> Reordenar';
        button.className = 'btn btn-sm btn-outline-primary';
        saveOrder();
    }
}

function initializeSortable() {
    // Implementação simples de drag and drop seria necessária aqui
    // Por enquanto, apenas mostramos os controles
}

function saveOrder() {
    const questionItems = document.querySelectorAll('.question-item');
    const questionIds = Array.from(questionItems).map(item => item.dataset.questionId);
    
    fetch(`/exams/{{ $exam->id }}/questions/reorder`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            questions: questionIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualiza os números das questões
            questionItems.forEach((item, index) => {
                const badge = item.querySelector('.badge.bg-secondary');
                badge.textContent = `${index + 1}º`;
            });
        }
    })
    .catch(error => {
        console.error('Erro ao salvar ordem:', error);
        alert('Erro ao salvar a nova ordem das questões.');
    });
}
</script>
@endsection
