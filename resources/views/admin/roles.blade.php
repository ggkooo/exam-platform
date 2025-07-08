@extends('layouts.app')

@section('title', 'Gerenciar Roles - Exam Platform')

@section('nav-actions')
    <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm me-2">
        <i class="bi bi-arrow-left me-1"></i>
        Voltar
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="bi bi-shield-check me-2"></i>
                    Gerenciamento de Roles
                </h4>
            </div>
            <div class="card-body">
                        <div class="row">
                            <!-- Roles Atuais -->
                            <div class="col-md-6">
                                <h5><i class="bi bi-person-badge me-2"></i>Seus Roles Atuais</h5>
                                
                                @if(count($userAssignments) > 0)
                                    <div class="list-group mb-3">
                                        @foreach($userAssignments as $assignment)
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>{{ $assignment->role_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        Shortname: <code>{{ $assignment->shortname }}</code>
                                                        | Contexto: {{ $assignment->contextlevel }}
                                                    </small>
                                                </div>
                                                <button class="btn btn-sm btn-outline-danger remove-role" 
                                                        data-role="{{ $assignment->shortname }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Nenhum role específico encontrado
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Adicionar Roles -->
                            <div class="col-md-6">
                                <h5><i class="bi bi-plus-circle me-2"></i>Adicionar Roles</h5>
                                
                                <div class="mb-3">
                                    <label class="form-label">Roles Disponíveis:</label>
                                    <div class="list-group">
                                        @foreach($availableRoles as $role)
                                            <button class="list-group-item list-group-item-action add-role" 
                                                    data-role="{{ $role->shortname }}">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>{{ $role->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $role->shortname }}</small>
                                                    </div>
                                                    <i class="bi bi-plus-circle text-success"></i>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                
                                <!-- Ações Especiais -->
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0">
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            Ações Especiais
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <button class="btn btn-danger btn-sm mb-2 w-100 add-role" data-role="admin">
                                            <i class="bi bi-shield-fill me-1"></i>
                                            Tornar Site Administrator
                                        </button>
                                        <button class="btn btn-primary btn-sm mb-2 w-100 add-role" data-role="manager">
                                            <i class="bi bi-gear me-1"></i>
                                            Tornar Manager
                                        </button>
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Use com cuidado! Estas ações concedem privilégios administrativos.
                                        </small>
                                    </div>
                                </div>
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
                        <h5><i class="bi bi-question-circle me-2"></i>Como Usar</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="bi bi-1-circle text-primary me-1"></i>Via Interface Moodle (Recomendado)</h6>
                                <ul>
                                    <li>Acesse: <strong>Administração do site > Usuários > Permissões > Administradores do site</strong></li>
                                    <li>Ou: <strong>Administração do site > Usuários > Contas > Definir papéis</strong></li>
                                    <li>Busque o usuário e atribua o role desejado</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="bi bi-2-circle text-success me-1"></i>Via Esta Interface</h6>
                                <ul>
                                    <li>Clique em um role disponível para adicioná-lo</li>
                                    <li>Use o botão de lixeira para remover roles</li>
                                    <li>Recarregue a página para ver as mudanças</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning mt-3">
                            <h6><i class="bi bi-exclamation-triangle me-1"></i>Principais Roles do Moodle:</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>admin/manager:</strong> Administrador<br>
                                    <strong>coursecreator:</strong> Criador de cursos<br>
                                </div>
                                <div class="col-md-4">
                                    <strong>editingteacher:</strong> Professor editor<br>
                                    <strong>teacher:</strong> Professor<br>
                                </div>
                                <div class="col-md-4">
                                    <strong>student:</strong> Aluno<br>
                                    <strong>guest:</strong> Visitante<br>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
    <script>
        // Add role
        document.querySelectorAll('.add-role').forEach(button => {
            button.addEventListener('click', function() {
                const role = this.dataset.role;
                
                if (confirm(`Tem certeza que deseja adicionar o role "${role}"?`)) {
                    fetch('{{ route("admin.roles.assign") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
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
                
                if (confirm(`Tem certeza que deseja remover o role "${role}"?`)) {
                    fetch('{{ route("admin.roles.remove") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
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
@endsection
