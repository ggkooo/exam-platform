@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="bi bi-plus-circle"></i>
        Criar Nova Prova
    </h1>
    <a href="{{ route('exams.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
        Voltar
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('exams.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="title" class="form-label">Título da Prova *</label>
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

                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="duration_minutes" class="form-label">Duração (minutos)</label>
                            <input type="number" 
                                   class="form-control @error('duration_minutes') is-invalid @enderror" 
                                   id="duration_minutes" 
                                   name="duration_minutes" 
                                   value="{{ old('duration_minutes', 60) }}" 
                                   min="1">
                            @error('duration_minutes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Padrão: 60 minutos</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="total_points" class="form-label">Total de Pontos</label>
                            <input type="number" 
                                   class="form-control @error('total_points') is-invalid @enderror" 
                                   id="total_points" 
                                   name="total_points" 
                                   value="{{ old('total_points', 100) }}" 
                                   min="0" 
                                   step="0.1">
                            @error('total_points')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Padrão: 100 pontos</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="start_time" class="form-label">Data/Hora de Início</label>
                            <input type="datetime-local" 
                                   class="form-control @error('start_time') is-invalid @enderror" 
                                   id="start_time" 
                                   name="start_time" 
                                   value="{{ old('start_time') }}">
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Deixe em branco para disponibilizar imediatamente</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="end_time" class="form-label">Data/Hora de Fim</label>
                            <input type="datetime-local" 
                                   class="form-control @error('end_time') is-invalid @enderror" 
                                   id="end_time" 
                                   name="end_time" 
                                   value="{{ old('end_time') }}">
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Deixe em branco para não ter data limite</small>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="max_attempts" class="form-label">Máximo de Tentativas</label>
                            <input type="number" 
                                   class="form-control @error('max_attempts') is-invalid @enderror" 
                                   id="max_attempts" 
                                   name="max_attempts" 
                                   value="{{ old('max_attempts', 1) }}" 
                                   min="1">
                            @error('max_attempts')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Padrão: 1 tentativa</small>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Configurações</h5>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Prova Ativa
                                </label>
                                <small class="d-block text-muted">Se marcado, a prova ficará disponível para os alunos</small>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="randomize_questions" 
                                       name="randomize_questions" 
                                       {{ old('randomize_questions') ? 'checked' : '' }}>
                                <label class="form-check-label" for="randomize_questions">
                                    Embaralhar Questões
                                </label>
                                <small class="d-block text-muted">As questões aparecerão em ordem aleatória</small>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="show_results_immediately" 
                                       name="show_results_immediately" 
                                       {{ old('show_results_immediately') ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_results_immediately">
                                    Mostrar Resultado Imediatamente
                                </label>
                                <small class="d-block text-muted">O aluno verá o resultado ao finalizar</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('exams.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i>
                            Criar Prova
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-info-circle"></i>
                    Informações
                </h5>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <strong>Título:</strong> Nome da prova que aparecerá para os alunos
                    </li>
                    <li class="mb-2">
                        <strong>Duração:</strong> Tempo limite em minutos para realizar a prova
                    </li>
                    <li class="mb-2">
                        <strong>Pontos:</strong> Pontuação máxima que pode ser obtida
                    </li>
                    <li class="mb-2">
                        <strong>Tentativas:</strong> Número máximo de vezes que um aluno pode fazer a prova
                    </li>
                </ul>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-lightbulb"></i>
                    Dica
                </h5>
                <p class="mb-0">
                    Após criar a prova, você poderá adicionar questões a ela. 
                    Lembre-se de ativar a prova somente quando todas as questões 
                    estiverem prontas.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
