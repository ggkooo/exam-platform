@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="bi bi-mortarboard"></i>
        Gerenciar Cursos
    </h1>
    <a href="{{ route('courses.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i>
        Novo Curso
    </a>
</div>

<div class="row">
    @forelse($courses as $course)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="card-title mb-0">{{ $course->name }}</h6>
                        <span class="badge bg-{{ $course->is_active ? 'success' : 'secondary' }}">
                            {{ $course->is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </div>

                    <p class="card-text small text-muted mb-2">
                        <strong>Código:</strong> {{ $course->code }}
                    </p>

                    @if($course->description)
                        <p class="card-text">
                            {{ Str::limit($course->description, 100) }}
                        </p>
                    @endif

                    <div class="row text-center small text-muted mb-3">
                        <div class="col-6">
                            <strong>Disciplinas</strong><br>
                            {{ $course->subjects->count() }}
                        </div>
                        <div class="col-6">
                            <strong>Questões</strong><br>
                            {{ $course->total_questions }}
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-transparent">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('courses.show', $course) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i>
                            Ver
                        </a>
                        <div class="btn-group">
                            <a href="{{ route('courses.edit', $course) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('courses.destroy', $course) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('Tem certeza que deseja excluir este curso?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="bi bi-mortarboard display-1 text-muted"></i>
                <h4 class="mt-3">Nenhum curso encontrado</h4>
                <p class="text-muted">Crie seu primeiro curso para começar a organizar as disciplinas e questões.</p>
                <a href="{{ route('courses.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    Criar Novo Curso
                </a>
            </div>
        </div>
    @endforelse
</div>

<!-- Paginação -->
@if($courses->hasPages())
    <div class="d-flex justify-content-center">
        {{ $courses->links() }}
    </div>
@endif

@endsection
