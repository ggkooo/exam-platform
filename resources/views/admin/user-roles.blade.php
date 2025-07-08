@extends('layouts.app')

@section('title', "Roles de {$user->firstname} {$user->lastname} - Exam Platform")

@section('nav-actions')
    <a href="{{ route('admin.users') }}" class="btn btn-outline-light btn-sm me-2">
        <i class="bi bi-arrow-left me-1"></i>
        Lista Usuários
    </a>
@endsection

@section('content')
<!-- Informações do Usuário -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-primary">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="bi bi-person-badge me-2"></i>
                    Informações do Usuário
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="row mb-2">
                            <div class="col-4"><strong>Nome:</strong></div>
                            <div class="col-8">{{ $user->firstname }} {{ $user->lastname }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Username:</strong></div>
                            <div class="col-8"><code>{{ $user->username }}</code></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Email:</strong></div>
                            <div class="col-8">{{ $user->email }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row mb-2">
                            <div class="col-4"><strong>ID Moodle:</strong></div>
                            <div class="col-8">
                                <span class="badge bg-secondary">{{ $user->id }}</span>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Criado em:</strong></div>
                            <div class="col-8">{{ date('d/m/Y H:i', $user->timecreated) }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Total de Roles:</strong></div>
                            <div class="col-8">
                                <span class="badge bg-info">{{ count($userAssignments) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Roles Atuais -->
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="bi bi-shield-check me-2"></i>
                    Roles Atuais ({{ count($userAssignments) }})
                </h5>
            </div>
            <div class="card-body">
                @if(count($userAssignments) > 0)
                    <div class="list-group">
                        @foreach($userAssignments as $assignment)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $assignment->role_name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        Shortname: <code>{{ $assignment->shortname }}</code>
                                        <br>
                                        Contexto: 
                                        @if($assignment->contextlevel == 10)
                                            <span class="badge bg-danger">Sistema</span>
                                        @elseif($assignment->contextlevel == 50)
                                            <span class="badge bg-primary">Curso</span>
                                        @else
                                            <span class="badge bg-secondary">Nível {{ $assignment->contextlevel }}</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">Atribuído: {{ date('d/m/Y H:i', $assignment->timemodified) }}</small>
                                    </small>
                                </div>
                                <button class="btn btn-sm btn-outline-danger remove-role" 
                                        data-role="{{ $assignment->shortname }}"
                                        data-role-name="{{ $assignment->role_name }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center p-4">
                        <i class="bi bi-shield-x fs-1 text-muted mb-3"></i>
                        <h6 class="text-muted">Nenhum role atribuído</h6>
                        <p class="text-muted">Este usuário não possui roles específicos no sistema.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Adicionar Roles -->
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-plus-circle me-2"></i>
                    Adicionar Roles
                </h5>
            </div>
            <div class="card-body">
                <!-- Ações Rápidas -->
                <div class="mb-4">
                    <h6><i class="bi bi-lightning me-1"></i>Ações Rápidas:</h6>
                    <div class="d-grid gap-2">
                        <button class="btn btn-danger btn-sm add-role" 
                                data-role="admin"
                                data-role-name="Site Administrator">
                            <i class="bi bi-shield-fill me-1"></i>
                            Tornar Site Administrator
                        </button>
                        <button class="btn btn-warning btn-sm add-role" 
                                data-role="manager"
                                data-role-name="Manager">
                            <i class="bi bi-gear me-1"></i>
                            Tornar Manager
                        </button>
                        <button class="btn btn-primary btn-sm add-role" 
                                data-role="editingteacher"
                                data-role-name="Teacher (editing)">
                            <i class="bi bi-mortarboard me-1"></i>
                            Tornar Professor
                        </button>
                        <button class="btn btn-success btn-sm add-role" 
                                data-role="student"
                                data-role-name="Student">
                            <i class="bi bi-person me-1"></i>
                            Tornar Aluno
                        </button>
                    </div>
                </div>
                
                <hr>
                
                <!-- Todos os Roles -->
                <div>
                    <h6><i class="bi bi-list me-1"></i>Todos os Roles Disponíveis:</h6>
                    <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                        @foreach($availableRoles as $role)
                            <button class="list-group-item list-group-item-action add-role d-flex justify-content-between align-items-center" 
                                    data-role="{{ $role->shortname }}"
                                    data-role-name="{{ $role->name }}">
                                <div>
                                    <strong>{{ $role->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $role->shortname }}</small>
                                </div>
                                <i class="bi bi-plus-circle text-success"></i>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Instruções -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-question-circle me-2"></i>Instruções</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6><i class="bi bi-plus-circle text-success me-1"></i>Adicionar Role</h6>
                        <ul>
                            <li>Use as "Ações Rápidas" para roles comuns</li>
                            <li>Ou escolha da lista completa</li>
                            <li>Clique no role desejado para atribuir</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6><i class="bi bi-trash text-danger me-1"></i>Remover Role</h6>
                        <ul>
                            <li>Clique no ícone de lixeira ao lado do role</li>
                            <li>Confirme a ação na caixa de diálogo</li>
                            <li>A mudança é aplicada imediatamente</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6><i class="bi bi-exclamation-triangle text-warning me-1"></i>Cuidados</h6>
                        <ul>
                            <li>Roles de admin concedem privilégios altos</li>
                            <li>Mudanças afetam o acesso do usuário</li>
                            <li>Sempre confirme antes de aplicar</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const userId = {{ $user->id }};
    
    // Add role
    document.querySelectorAll('.add-role').forEach(button => {
        button.addEventListener('click', function() {
            const role = this.dataset.role;
            const roleName = this.dataset.roleName;
            
            if (confirm(`Tem certeza que deseja adicionar o role "${roleName}" para {{ $user->firstname }} {{ $user->lastname }}?`)) {
                fetch('{{ route("admin.user.roles.assign") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        role_shortname: role
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', data.success);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('danger', data.error || 'Erro desconhecido');
                    }
                })
                .catch(error => {
                    showAlert('danger', 'Erro na requisição: ' + error.message);
                });
            }
        });
    });
    
    // Remove role
    document.querySelectorAll('.remove-role').forEach(button => {
        button.addEventListener('click', function() {
            const role = this.dataset.role;
            const roleName = this.dataset.roleName;
            
            if (confirm(`Tem certeza que deseja remover o role "${roleName}" de {{ $user->firstname }} {{ $user->lastname }}?`)) {
                fetch('{{ route("admin.user.roles.remove") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        role_shortname: role
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', data.success);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('danger', data.error || 'Erro desconhecido');
                    }
                })
                .catch(error => {
                    showAlert('danger', 'Erro na requisição: ' + error.message);
                });
            }
        });
    });
</script>
@endpush
