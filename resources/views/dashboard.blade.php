@extends('layouts.app')

@section('title', 'Dashboard')

@section('nav-actions')
    <span class="{{ \App\Helpers\RoleHelper::getRoleBadgeClass() }} me-3">
        <i class="{{ \App\Helpers\RoleHelper::getRoleIcon() }} me-1"></i>
        {{ \App\Helpers\RoleHelper::getRoleDisplayName() }}
    </span>
@endsection

@section('content')
        <!-- Cabeçalho de Boas-vindas -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-person-circle display-1 mb-3"></i>
                        <h2 class="card-title mb-2">Bem-vindo, {{ Session::get('moodle_name', 'Usuário') }}!</h2>
                        <p class="card-text opacity-75">
                            <i class="bi bi-calendar-date me-1"></i>
                            {{ now()->format('d/m/Y - H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Informações do Usuário -->
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-person-badge me-2"></i>
                            Informações Pessoais
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-4"><strong>Nome:</strong></div>
                            <div class="col-8">{{ Session::get('moodle_name', 'N/A') }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><strong>Usuário:</strong></div>
                            <div class="col-8">
                                <code>{{ Session::get('moodle_username', 'N/A') }}</code>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><strong>Email:</strong></div>
                            <div class="col-8">{{ Session::get('moodle_email', 'N/A') }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><strong>ID Moodle:</strong></div>
                            <div class="col-8">
                                <span class="badge bg-secondary">{{ Session::get('moodle_user_id', 'N/A') }}</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4"><strong>Papel Principal:</strong></div>
                            <div class="col-8">
                                <span class="{{ \App\Helpers\RoleHelper::getRoleBadgeClass() }}">
                                    <i class="{{ \App\Helpers\RoleHelper::getRoleIcon() }} me-1"></i>
                                    {{ \App\Helpers\RoleHelper::getRoleDisplayName() }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Roles/Papéis do Usuário -->
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-shield-check me-2"></i>
                            Papéis no Sistema
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $userRoles = Session::get('moodle_user_roles', []);
                            $primaryRole = Session::get('moodle_primary_role', 'N/A');
                        @endphp
                        
                        @if(\App\Helpers\RoleHelper::isAdmin() && !\App\Helpers\RoleHelper::isRealAdmin())
                            <div class="alert alert-warning mb-3">
                                <h6><i class="bi bi-exclamation-triangle me-1"></i> Acesso Temporário de Admin</h6>
                                <small>
                                    Você tem acesso temporário às funcionalidades de administração como professor.
                                    <br>
                                    <strong>Para se tornar administrador permanente:</strong>
                                    <a href="{{ route('admin.users') }}" class="alert-link">Clique aqui para gerenciar usuários</a>
                                    e se promova a Site Administrator.
                                </small>
                                <div class="mt-2">
                                    <button id="refresh-roles-btn" class="btn btn-warning btn-sm">
                                        <i class="bi bi-arrow-clockwise me-1"></i>
                                        Atualizar Meus Roles
                                    </button>
                                    <small class="text-muted ms-2">
                                        Clique se já se promoveu a administrador
                                    </small>
                                </div>
                            </div>
                        @endif
                        
                        @if(count($userRoles) > 0)
                            <div class="mb-3">
                                <small class="text-muted">Você possui os seguintes papéis:</small>
                            </div>
                            @foreach($userRoles as $role)
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                    <div>
                                        <strong>{{ $role->role_name ?? $role->role_shortname }}</strong>
                                        <br>
                                        <small class="text-muted">Shortname: <code>{{ $role->role_shortname }}</code></small>
                                        <br>
                                        <small class="text-muted">Role ID: {{ $role->role_id }}</small>
                                    </div>
                                    <div>
                                        @if($role->contextlevel == 10)
                                            <span class="badge bg-danger">Sistema</span>
                                        @elseif($role->contextlevel == 50)
                                            <span class="badge bg-primary">Curso</span>
                                        @else
                                            <span class="badge bg-secondary">Contexto {{ $role->contextlevel }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted">
                                <i class="bi bi-info-circle fs-1 mb-3"></i>
                                <p>Nenhum papel específico encontrado</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Permissões/Funcionalidades -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-gear me-2"></i>
                            Funcionalidades Disponíveis
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if(\App\Helpers\RoleHelper::isAdmin())
                                <div class="col-md-4 mb-3">
                                    <div class="card border-danger">
                                        <div class="card-body text-center">
                                            <i class="bi bi-shield-check text-danger fs-1"></i>
                                            <h6 class="mt-2">
                                                Administração
                                                @if(!\App\Helpers\RoleHelper::isRealAdmin())
                                                    <small class="badge bg-warning text-dark ms-1">Temporário</small>
                                                @endif
                                            </h6>
                                            <small class="text-muted">
                                                @if(\App\Helpers\RoleHelper::isRealAdmin())
                                                    Acesso total ao sistema
                                                @else
                                                    Acesso temporário como professor
                                                @endif
                                            </small>
                                            <div class="mt-2">
                                                <a href="{{ route('admin.users') }}" class="btn btn-outline-danger btn-sm me-1">
                                                    <i class="bi bi-people me-1"></i>
                                                    Usuários
                                                </a>
                                                <a href="{{ route('admin.roles') }}" class="btn btn-outline-danger btn-sm">
                                                    <i class="bi bi-gear me-1"></i>
                                                    Meus Roles
                                                </a>
                                            </div>
                                            @if(!\App\Helpers\RoleHelper::isRealAdmin())
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <i class="bi bi-info-circle me-1"></i>
                                                        Para acesso permanente, promova-se a Site Administrator
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(\App\Helpers\RoleHelper::canCreateExams())
                                <div class="col-md-4 mb-3">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <i class="bi bi-file-earmark-text text-primary fs-1"></i>
                                            <h6 class="mt-2">Gerenciar Provas</h6>
                                            <small class="text-muted">Criar e gerenciar exames</small>
                                            <div class="mt-2">
                                                <a href="{{ route('exams.index') }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-list me-1"></i>
                                                    Minhas Provas
                                                </a>
                                                <a href="{{ route('exams.create') }}" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-plus-circle me-1"></i>
                                                    Nova Prova
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <i class="bi bi-collection text-success fs-1"></i>
                                            <h6 class="mt-2">Banco de Questões</h6>
                                            <small class="text-muted">Gerenciar questões por curso/disciplina/módulo</small>
                                            <div class="mt-2">
                                                <a href="{{ route('question-bank.index') }}" class="btn btn-outline-success btn-sm">
                                                    <i class="bi bi-list me-1"></i>
                                                    Ver Questões
                                                </a>
                                                <a href="{{ route('question-bank.create') }}" class="btn btn-success btn-sm">
                                                    <i class="bi bi-plus-circle me-1"></i>
                                                    Nova Questão
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(\App\Helpers\RoleHelper::isTeacher())
                                <div class="col-md-4 mb-3">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <i class="bi bi-mortarboard text-success fs-1"></i>
                                            <h6 class="mt-2">Ensino</h6>
                                            <small class="text-muted">Ferramentas de ensino</small>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(\App\Helpers\RoleHelper::isStudent())
                                <div class="col-md-4 mb-3">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <i class="bi bi-pencil-square text-success fs-1"></i>
                                            <h6 class="mt-2">Realizar Exames</h6>
                                            <small class="text-muted">Participar de avaliações</small>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-md-4 mb-3">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <i class="bi bi-graph-up text-info fs-1"></i>
                                        <h6 class="mt-2">Relatórios</h6>
                                        <small class="text-muted">Visualizar desempenho</small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="card border-warning">
                                    <div class="card-body text-center">
                                        <i class="bi bi-person-gear text-warning fs-1"></i>
                                        <h6 class="mt-2">Perfil</h6>
                                        <small class="text-muted">Gerenciar conta</small>
                                    </div>
                                </div>
                            </div>

                            @if(\App\Helpers\RoleHelper::canCreateExams())
                                <div class="col-md-4 mb-3">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <i class="bi bi-diagram-3 text-warning fs-1"></i>
                                            <h6 class="mt-2">Organizar Conteúdo</h6>
                                            <small class="text-muted">Gerenciar cursos, disciplinas e módulos</small>
                                            <div class="mt-2">
                                                <a href="{{ route('courses.index') }}" class="btn btn-outline-warning btn-sm">
                                                    <i class="bi bi-mortarboard me-1"></i>
                                                    Cursos
                                                </a>
                                                <a href="{{ route('subjects.index') }}" class="btn btn-outline-warning btn-sm">
                                                    <i class="bi bi-book me-1"></i>
                                                    Disciplinas
                                                </a>
                                                <a href="{{ route('modules.index') }}" class="btn btn-warning btn-sm">
                                                    <i class="bi bi-grid-3x3-gap me-1"></i>
                                                    Módulos
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
        // Botão para atualizar roles
        const refreshBtn = document.getElementById('refresh-roles-btn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function() {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="bi bi-arrow-clockwise me-1 spinner-border spinner-border-sm"></i> Atualizando...';
                this.disabled = true;
                
                fetch('{{ route("session.refresh.roles") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Mostrar sucesso e recarregar página
                        alert(data.success + '\n\nPrimary Role: ' + data.primary_role + '\nTotal Roles: ' + data.roles_count);
                        location.reload();
                    } else {
                        alert('Erro: ' + (data.error || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    alert('Erro na requisição: ' + error.message);
                })
                .finally(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                });
            });
        }
    });
</script>
@endpush

@endsection