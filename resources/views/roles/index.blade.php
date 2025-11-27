@extends('layouts.app')

@section('title', 'Rôles & Permissions')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Gestion des Rôles et Permissions</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Rôle</th>
                                    <th>Service</th>
                                    <th>Permissions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold">{{ $user->name }}</span>
                                                <small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $roleColors = [
                                                    'admin' => 'danger',
                                                    'directeur' => 'warning',
                                                    'chef_service' => 'info',
                                                    'agent_courrier' => 'secondary',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $roleColors[$user->role] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                            </span>
                                        </td>
                                        <td>{{ $user->service->nom ?? '-' }}</td>
                                        <td>
                                            @php
                                                $userPermissions = $user->permissions ?? [];
                                                $permissionCount = count($userPermissions);
                                            @endphp
                                            <span class="badge bg-label-primary">{{ $permissionCount }} permission(s)</span>
                                            @if($permissionCount > 0)
                                                <button type="button" class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#permissionsModal{{ $user->id }}">
                                                    Voir détails
                                                </button>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editPermissionsModal{{ $user->id }}">
                                                <i class="bx bx-edit"></i> Modifier
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal pour voir les permissions -->
                                    <div class="modal fade" id="permissionsModal{{ $user->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Permissions de {{ $user->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        @foreach($allPermissions as $permission)
                                                            <div class="col-md-6 mb-2">
                                                                @if(in_array($permission, $userPermissions))
                                                                    <span class="badge bg-success">
                                                                        <i class="bx bx-check"></i> {{ $permission }}
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-secondary">
                                                                        <i class="bx bx-x"></i> {{ $permission }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal pour modifier les permissions -->
                                    <div class="modal fade" id="editPermissionsModal{{ $user->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <form action="{{ route('roles.updatePermissions', $user->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Modifier les permissions de {{ $user->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            @foreach($allPermissions as $permission)
                                                                <div class="col-md-6 mb-2">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission }}" id="perm_{{ $user->id }}_{{ $loop->index }}" 
                                                                               {{ in_array($permission, $userPermissions) ? 'checked' : '' }}>
                                                                        <label class="form-check-label" for="perm_{{ $user->id }}_{{ $loop->index }}">
                                                                            {{ $permission }}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

