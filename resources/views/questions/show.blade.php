@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="bi bi-eye"></i>
            Visualizar Questão
        </h1>
        <small class="text-muted">Prova: {{ $exam->title }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('exams.questions.index', $exam) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
            Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Questão {{ $examQuestion->order ?? 'N/A' }}º</h5>
                    <div>
                        <span class="badge bg-primary me-2">
                            {{ ucfirst(str_replace('_', ' ', $question->type)) }}
                        </span>
                        <span class="badge bg-info me-2">{{ $examQuestion->effective_points ?? $question->points }} pts</span>
                        @if(!($examQuestion->is_active ?? $question->is_active))
                            <span class="badge bg-danger">Inativa</span>
                        @else
                            <span class="badge bg-success">Ativa</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body">
                <h6 class="text-muted mb-3">Pergunta:</h6>
                <div class="border-start border-3 border-primary ps-3 mb-4">
                    <p class="mb-0">{{ $question->question_text }}</p>
                </div>

                @if($question->type === 'multiple_choice' && $question->options)
                    <h6 class="text-muted mb-3">Opções de Resposta:</h6>
                    <div class="list-group mb-4">
                        @foreach($question->formatted_options as $index => $option)
                            <div class="list-group-item d-flex align-items-center">
                                @if($question->correct_answer === chr(65 + $index))
                                    <i class="bi bi-check-circle-fill text-success me-3"></i>
                                    <strong class="text-success">{{ chr(65 + $index) }}. {{ $option }} (Correta)</strong>
                                @else
                                    <i class="bi bi-circle text-muted me-3"></i>
                                    <span>{{ chr(65 + $index) }}. {{ $option }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @elseif($question->type === 'true_false')
                    <h6 class="text-muted mb-3">Resposta Correta:</h6>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i>
                        <strong>{{ $question->correct_answer === 'Verdadeiro' ? 'Verdadeiro' : 'Falso' }}</strong>
                    </div>
                @elseif($question->type === 'essay')
                    <h6 class="text-muted mb-3">Tipo de Resposta:</h6>
                    <div class="alert alert-info">
                        <i class="bi bi-file-text"></i>
                        <strong>Resposta Dissertativa</strong>
                        <p class="mb-0 mt-2">Esta questão requer uma resposta escrita do aluno.</p>
                    </div>
                    
                    @if($question->correct_answer && $question->correct_answer !== 'Resposta dissertativa')
                        <h6 class="text-muted mb-3">Critérios/Resposta Esperada:</h6>
                        <div class="border-start border-3 border-info ps-3 mb-4">
                            <p class="mb-0">{{ $question->correct_answer }}</p>
                        </div>
                    @endif
                @endif

                @if($question->explanation)
                    <h6 class="text-muted mb-3">Explicação:</h6>
                    <div class="alert alert-light">
                        <i class="bi bi-info-circle"></i>
                        {{ $question->explanation }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Informações da Questão</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Ordem:</dt>
                    <dd class="col-sm-7">{{ $examQuestion->order ?? 'N/A' }}º</dd>

                    <dt class="col-sm-5">Tipo:</dt>
                    <dd class="col-sm-7">
                        @switch($question->type)
                            @case('multiple_choice')
                                Múltipla Escolha
                                @break
                            @case('true_false')
                                Verdadeiro/Falso
                                @break
                            @case('essay')
                                Dissertativa
                                @break
                            @default
                                {{ $question->type }}
                        @endswitch
                    </dd>

                    <dt class="col-sm-5">Pontos na Prova:</dt>
                    <dd class="col-sm-7">{{ $examQuestion->effective_points ?? $question->points }}</dd>

                    <dt class="col-sm-5">Status na Prova:</dt>
                    <dd class="col-sm-7">
                        @if($examQuestion->is_active ?? $question->is_active)
                            <span class="badge bg-success">Ativa</span>
                        @else
                            <span class="badge bg-danger">Inativa</span>
                        @endif
                    </dd>

                    <dt class="col-sm-5">Origem:</dt>
                    <dd class="col-sm-7">
                        @if($question->title || $question->course_id)
                            <span class="badge bg-success">
                                <i class="bi bi-collection"></i> Do Banco
                            </span>
                        @else
                            <span class="badge bg-warning">
                                <i class="bi bi-plus-circle"></i> Nova
                            </span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>

                    @if($question->created_at)
                        <dt class="col-sm-5">Criada em:</dt>
                        <dd class="col-sm-7">{{ $question->created_at->format('d/m/Y H:i') }}</dd>
                    @endif

                    @if($question->updated_at && $question->updated_at != $question->created_at)
                        <dt class="col-sm-5">Atualizada em:</dt>
                        <dd class="col-sm-7">{{ $question->updated_at->format('d/m/Y H:i') }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" 
                            class="btn btn-danger" 
                            onclick="confirmRemove({{ $question->id }}, '{{ addslashes($question->question_text) }}', {{ $question->title ? 'true' : 'false' }})">
                        @if($question->title)
                            <i class="bi bi-x-circle"></i>
                            Remover da Prova
                        @else
                            <i class="bi bi-trash"></i>
                            Excluir Questão
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

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
</script>
@endsection
