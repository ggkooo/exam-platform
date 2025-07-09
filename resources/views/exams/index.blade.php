@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="bi bi-file-earmark-text"></i>
        Gerenciar Provas
    </h1>
    <a href="{{ route('exams.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i>
        Nova Prova
    </a>
</div>

@if($exams->count() > 0)
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Duração</th>
                            <th>Questões</th>
                            <th>Pontos</th>
                            <th>Status</th>
                            <th>Criado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($exams as $exam)
                        <tr>
                            <td>
                                <strong>{{ $exam->title }}</strong>
                                @if($exam->description)
                                    <br>
                                    <small class="text-muted">{{ Str::limit($exam->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $exam->duration_minutes }} min
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $exam->total_questions ?? 0 }} questões
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    {{ $exam->total_points }} pts
                                </span>
                            </td>
                            <td>
                                @if($exam->is_active)
                                    @if($exam->isAvailable())
                                        <span class="badge bg-success">Ativa</span>
                                    @else
                                        <span class="badge bg-warning">Programada</span>
                                    @endif
                                @else
                                    <span class="badge bg-danger">Inativa</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $exam->created_at->format('d/m/Y H:i') }}
                                </small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('exams.show', $exam) }}" 
                                       class="btn btn-outline-primary" 
                                       title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('exams.questions.index', $exam) }}" 
                                       class="btn btn-outline-info" 
                                       title="Questões">
                                        <i class="bi bi-question-circle"></i>
                                    </a>
                                    <a href="{{ route('exams.edit', $exam) }}" 
                                       class="btn btn-outline-warning" 
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($exam->attempts()->count() == 0)
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            title="Excluir"
                                            onclick="confirmDelete({{ $exam->id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{ $exams->links() }}
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-file-earmark-text display-1 text-muted mb-3"></i>
            <h4 class="text-muted">Nenhuma prova encontrada</h4>
            <p class="text-muted">Comece criando sua primeira prova.</p>
            <a href="{{ route('exams.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i>
                Criar Primeira Prova
            </a>
        </div>
    </div>
@endif

<!-- Modal de confirmação de exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir esta prova? Esta ação não pode ser desfeita.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(examId) {
    document.getElementById('deleteForm').action = `/exams/${examId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endsection
