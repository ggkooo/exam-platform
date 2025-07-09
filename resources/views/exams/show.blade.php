@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="bi bi-file-earmark-text"></i>
        {{ $exam->title }}
    </h1>
    <div class="d-flex gap-2">
        <a href="{{ route('exams.questions.index', $exam) }}" class="btn btn-info">
            <i class="bi bi-question-circle"></i>
            Gerenciar Questões
        </a>
        <a href="{{ route('exams.edit', $exam) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i>
            Editar
        </a>
        <a href="{{ route('exams.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
            Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle"></i>
                    Informações da Prova
                </h5>
            </div>
            <div class="card-body">
                @if($exam->description)
                <div class="mb-3">
                    <strong>Descrição:</strong>
                    <p class="mt-1">{{ $exam->description }}</p>
                </div>
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Duração:</strong>
                        <span class="badge bg-info ms-2">{{ $exam->duration_minutes }} minutos</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Total de Pontos:</strong>
                        <span class="badge bg-primary ms-2">{{ $exam->total_points }} pts</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Questões:</strong>
                        <span class="badge bg-secondary ms-2">{{ $exam->total_questions ?? 0 }} questões</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Máximo de Tentativas:</strong>
                        <span class="badge bg-warning ms-2">{{ $exam->max_attempts }}</span>
                    </div>
                </div>

                @if($exam->start_time || $exam->end_time)
                <div class="row">
                    @if($exam->start_time)
                    <div class="col-md-6 mb-3">
                        <strong>Início:</strong>
                        <div class="mt-1">{{ $exam->start_time->format('d/m/Y H:i') }}</div>
                    </div>
                    @endif
                    @if($exam->end_time)
                    <div class="col-md-6 mb-3">
                        <strong>Fim:</strong>
                        <div class="mt-1">{{ $exam->end_time->format('d/m/Y H:i') }}</div>
                    </div>
                    @endif
                </div>
                @endif

                <div class="row">
                    <div class="col-md-12">
                        <strong>Configurações:</strong>
                        <div class="mt-2">
                            @if($exam->is_active)
                                <span class="badge bg-success me-2">Ativa</span>
                            @else
                                <span class="badge bg-danger me-2">Inativa</span>
                            @endif

                            @if($exam->randomize_questions)
                                <span class="badge bg-info me-2">Questões Embaralhadas</span>
                            @endif

                            @if($exam->show_results_immediately)
                                <span class="badge bg-primary me-2">Resultado Imediato</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($exam->questions->count() > 0)
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-list-ol"></i>
                    Questões ({{ $exam->questions->count() }})
                </h5>
                <a href="{{ route('exams.questions.create', $exam) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    Nova Questão
                </a>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @foreach($exam->questions->take(5) as $question)
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-1">
                                <span class="badge bg-secondary me-2">{{ $question->order }}º</span>
                                <span class="badge bg-primary me-2">{{ ucfirst(str_replace('_', ' ', $question->type)) }}</span>
                                <span class="badge bg-info">{{ $question->points }} pts</span>
                            </div>
                            <p class="mb-1">{{ Str::limit($question->question_text, 100) }}</p>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('exams.questions.show', [$exam, $question]) }}" 
                               class="btn btn-outline-primary" 
                               title="Ver">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                @if($exam->questions->count() > 5)
                <div class="text-center mt-3">
                    <a href="{{ route('exams.questions.index', $exam) }}" class="btn btn-outline-primary">
                        Ver todas as {{ $exam->questions->count() }} questões
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-graph-up"></i>
                    Status da Prova
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    @if($exam->isAvailable())
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i>
                            <strong>Disponível</strong><br>
                            A prova está disponível para os alunos.
                        </div>
                    @elseif($exam->is_active && $exam->start_time && $exam->start_time > now())
                        <div class="alert alert-warning">
                            <i class="bi bi-clock"></i>
                            <strong>Programada</strong><br>
                            A prova será disponibilizada em {{ $exam->start_time->format('d/m/Y H:i') }}.
                        </div>
                    @elseif($exam->is_active && $exam->end_time && $exam->end_time < now())
                        <div class="alert alert-info">
                            <i class="bi bi-clock-history"></i>
                            <strong>Encerrada</strong><br>
                            A prova foi encerrada em {{ $exam->end_time->format('d/m/Y H:i') }}.
                        </div>
                    @else
                        <div class="alert alert-secondary">
                            <i class="bi bi-pause-circle"></i>
                            <strong>Inativa</strong><br>
                            A prova não está disponível para os alunos.
                        </div>
                    @endif
                </div>

                <div class="d-grid gap-2">
                    @if($exam->questions->count() === 0)
                    <a href="{{ route('exams.questions.create', $exam) }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i>
                        Adicionar Primeira Questão
                    </a>
                    @else
                    <a href="{{ route('exams.questions.index', $exam) }}" class="btn btn-info">
                        <i class="bi bi-question-circle"></i>
                        Gerenciar Questões
                    </a>
                    @endif
                    
                    <a href="{{ route('exams.edit', $exam) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i>
                        Editar Prova
                    </a>

                    @if($exam->attempts()->count() == 0)
                    <button type="button" 
                            class="btn btn-danger" 
                            onclick="confirmDelete()">
                        <i class="bi bi-trash"></i>
                        Excluir Prova
                    </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-calendar"></i>
                    Criação
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Criado em:</strong></p>
                <p class="text-muted">{{ $exam->created_at->format('d/m/Y H:i') }}</p>
                
                @if($exam->updated_at != $exam->created_at)
                <p class="mb-1"><strong>Última modificação:</strong></p>
                <p class="text-muted">{{ $exam->updated_at->format('d/m/Y H:i') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

@if($exam->attempts()->count() == 0)
<!-- Modal de confirmação de exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir a prova <strong>"{{ $exam->title }}"</strong>?</p>
                <p class="text-danger mb-0">Esta ação não pode ser desfeita e todas as questões serão excluídas também.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('exams.destroy', $exam) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endif
@endsection
