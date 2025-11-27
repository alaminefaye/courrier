@extends('layouts.app')

@section('title', 'Détails Utilisateur')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Détails de l'Utilisateur</h5>
                    <div>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bx bx-arrow-back"></i> Retour
                        </a>
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm">
                            <i class="bx bx-edit"></i> Modifier
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Nom:</th>
                                    <td><strong>{{ $user->name }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <th>Rôle:</th>
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
                                            {{ config('courrier.roles')[$user->role] ?? $user->role }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Service:</th>
                                    <td>{{ $user->service->nom ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Direction:</th>
                                    <td>{{ $user->direction->nom ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Date de création:</th>
                                    <td>{{ $user->created_at->format('d/m/Y à H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

