@extends('layouts.app')

@section('title', 'Gerenciar Usuários - Exam Platform')

@section('nav-actions')
    <a href="{{ route('admin.roles') }}" class="btn btn-outline-light btn-sm me-2">
        <i class="bi bi-shield-check me-1"></i>
        Meus Roles
    </a>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm me-2">
        <i class="bi bi-arrow-left me-1"></i>
        Dashboard
    </a>
@endsection

@section('content')
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-people me-2"></i>
                            Gerenciar Usuários ({{ number_format($totalUsers) }} usuários)
                        </h4>
                        <a href="{{ route('admin.roles') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-shield-check me-1"></i>
                            Meus Roles
                        </a>
                    </div>
                    
                    <!-- Busca -->
                    <div class="card-body border-bottom">
                        <form method="GET" action="{{ route('admin.users') }}" class="row g-3">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           name="search" 
                                           value="{{ $search }}" 
                                           placeholder="Buscar por nome, username ou email...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-search me-1"></i>
                                        Buscar
                                    </button>
                                    @if($search)
                                        <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle me-1"></i>
                                            Limpar
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Lista de Usuários -->
                    <div class="card-body p-0">
                        @if(count($users) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nome</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Criado em</th>
                                            <th width="150">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($users as $user)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $user->id }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $user->firstname }} {{ $user->lastname }}</strong>
                                                </td>
                                                <td>
                                                    <code>{{ $user->username }}</code>
                                                </td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    <small>{{ date('d/m/Y H:i', $user->timecreated) }}</small>
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.user.roles', $user->id) }}" 
                                                       class="btn btn-sm btn-primary" 
                                                       title="Gerenciar Roles">
                                                        <i class="bi bi-shield-check"></i>
                                                        Roles
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Paginação -->
                            @if($totalPages > 1)
                                <div class="card-footer">
                                    <nav aria-label="Navegação de usuários">
                                        <ul class="pagination pagination-sm justify-content-center mb-0">
                                            @if($page > 1)
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ route('admin.users', ['search' => $search, 'page' => $page - 1]) }}">
                                                        <i class="bi bi-chevron-left"></i>
                                                    </a>
                                                </li>
                                            @endif
                                            
                                            @for($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++)
                                                <li class="page-item {{ $i == $page ? 'active' : '' }}">
                                                    <a class="page-link" href="{{ route('admin.users', ['search' => $search, 'page' => $i]) }}">
                                                        {{ $i }}
                                                    </a>
                                                </li>
                                            @endfor
                                            
                                            @if($page < $totalPages)
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ route('admin.users', ['search' => $search, 'page' => $page + 1]) }}">
                                                        <i class="bi bi-chevron-right"></i>
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </nav>
                                    
                                    <div class="text-center mt-2">
                                        <small class="text-muted">
                                            Página {{ $page }} de {{ $totalPages }} 
                                            ({{ number_format($totalUsers) }} usuários total)
                                        </small>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="text-center p-5">
                                <i class="bi bi-person-x fs-1 text-muted mb-3"></i>
                                <h5 class="text-muted">Nenhum usuário encontrado</h5>
                                @if($search)
                                    <p class="text-muted mb-3">
                                        Nenhum resultado para: <strong>"{{ $search }}"</strong>
                                    </p>
                                    <a href="{{ route('admin.users') }}" class="btn btn-outline-primary">
                                        <i class="bi bi-arrow-left me-1"></i>
                                        Ver todos os usuários
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Instruções -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-info-circle me-2"></i>Como Usar</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="bi bi-1-circle text-primary me-1"></i>Buscar Usuários</h6>
                                <ul>
                                    <li>Use a barra de busca para encontrar usuários específicos</li>
                                    <li>Busque por nome, username ou email</li>
                                    <li>A busca é case-insensitive</li>
                                </ul>
                            </div>
                        <div class="col-md-6">
                            <h6><i class="bi bi-2-circle text-success me-1"></i>Gerenciar Roles</h6>
                            <ul>
                                <li>Clique no botão "Roles" ao lado do usuário</li>
                                <li>Adicione ou remova roles conforme necessário</li>
                                <li>Mudanças são aplicadas imediatamente</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
