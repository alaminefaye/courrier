@extends('layouts.app')

@section('title', 'Détails Service')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Détails du Service</h5>
                    <div>
                        <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bx bx-arrow-back"></i> Retour
                        </a>
                        <a href="{{ route('services.edit', $service->id) }}" class="btn btn-warning btn-sm">
                            <i class="bx bx-edit"></i> Modifier
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Code:</th>
                                    <td><strong>{{ $service->code }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Nom:</th>
                                    <td>{{ $service->nom }}</td>
                                </tr>
                                <tr>
                                    <th>Direction:</th>
                                    <td>{{ $service->direction->nom ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Responsable:</th>
                                    <td>{{ $service->responsable->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Description:</th>
                                    <td>{{ $service->description ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($service->users->count() > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="mb-3">Utilisateurs de ce Service ({{ $service->users->count() }})</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Nom</th>
                                            <th>Email</th>
                                            <th>Rôle</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($service->users as $user)
                                            <tr>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td><span class="badge bg-info">{{ config('courrier.roles')[$user->role] ?? $user->role }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

