@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="bi bi-eye"></i>
        Visualizar Questão
    </h1>
    <div>
        <a href="{{ route('question-bank.edit', $question) }}" class="btn btn-outline-primary me-2">
            <i class="bi bi-pencil"></i>
            Editar
        </a>
        <a href="{{ route('question-bank.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
            Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ $question->title ?? 'Questão sem título' }}</h5>
                <small class="text-muted">{{ $question->hierarchy_path }}</small>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Tipo:</strong> 
                        @if($question->type == 'multiple_choice')
                            Múltipla Escolha
                        @elseif($question->type == 'true_false')
                            Verdadeiro/Falso
                        @else
                            Dissertativa
                        @endif
                    </div>
                    <div class="col-md-3">
                        <strong>Pontos:</strong> {{ $question->points }}
                    </div>
                    <div class="col-md-3">
                        <strong>Dificuldade:</strong> 
                        <span class="badge bg-{{ $question->difficulty_level == 'easy' ? 'success' : ($question->difficulty_level == 'medium' ? 'warning' : 'danger') }}">
                            {{ $question->difficulty_level == 'easy' ? 'Fácil' : ($question->difficulty_level == 'medium' ? 'Médio' : 'Difícil') }}
                        </span>
                    </div>
                </div>

                <div class="mb-4">
                    <h6>Texto da Questão:</h6>
                    <div class="p-3 bg-light rounded">
                        {!! nl2br(e($question->question_text)) !!}
                    </div>
                </div>

                @if($question->type == 'multiple_choice' && $question->formatted_options)
                    <div class="mb-4">
                        <h6>Opções de Resposta:</h6>
                        @foreach($question->formatted_options as $index => $option)
                            @if(trim($option))
                                <div class="p-2 mb-1 {{ strtoupper($question->correct_answer) == chr(65 + $index) ? 'bg-success text-white' : 'bg-light' }} rounded">
                                    <strong>{{ chr(65 + $index) }})</strong> {{ $option }}
                                    @if(strtoupper($question->correct_answer) == chr(65 + $index))
                                        <i class="bi bi-check-circle float-end"></i>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                <div class="mb-3">
                    <h6>Resposta Correta:</h6>
                    <div class="p-2 bg-success text-white rounded">
                        {{ $question->correct_answer }}
                    </div>
                </div>

                @if($question->explanation)
                    <div class="mb-3">
                        <h6>Explicação:</h6>
                        <div class="p-3 bg-light rounded">
                            {!! nl2br(e($question->explanation)) !!}
                        </div>
                    </div>
                @endif

                @if($question->tags && count($question->tags) > 0)
                    <div class="mb-3">
                        <h6>Tags:</h6>
                        @foreach($question->tags as $tag)
                            <span class="badge bg-secondary me-1">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Informações</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Status:</strong>
                    <span class="badge bg-{{ $question->is_active ? 'success' : 'secondary' }}">
                        {{ $question->is_active ? 'Ativa' : 'Inativa' }}
                    </span>
                </div>
                <div class="mb-2">
                    <strong>Curso:</strong> {{ $question->course->name ?? 'N/A' }}
                </div>
                <div class="mb-2">
                    <strong>Disciplina:</strong> {{ $question->subject->name ?? 'N/A' }}
                </div>
                <div class="mb-2">
                    <strong>Módulo:</strong> {{ $question->module->name ?? 'N/A' }}
                </div>
                <div class="mb-2">
                    <strong>Criado em:</strong> {{ $question->created_at->format('d/m/Y H:i') }}
                </div>
                @if($question->updated_at != $question->created_at)
                    <div class="mb-2">
                        <strong>Atualizado em:</strong> {{ $question->updated_at->format('d/m/Y H:i') }}
                    </div>
                @endif
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title">Ações</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('question-bank.edit', $question) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i>
                        Editar Questão
                    </a>
                    <form action="{{ route('question-bank.destroy', $question) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100" 
                                onclick="return confirm('Tem certeza que deseja excluir esta questão?')">
                            <i class="bi bi-trash"></i>
                            Excluir Questão
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
