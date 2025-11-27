@extends('layouts.app')

@section('title', 'Services')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Liste des Services</h5>
                    @can('create', App\Models\Service::class)
                        <a href="{{ route('services.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus"></i> Nouveau Service
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Nom</th>
                                    <th>Direction</th>
                                    <th>Responsable</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($services as $service)
                                    <tr>
                                        <td><strong>{{ $service->code }}</strong></td>
                                        <td>{{ $service->nom }}</td>
                                        <td>{{ $service->direction->nom ?? '-' }}</td>
                                        <td>{{ $service->responsable->name ?? '-' }}</td>
                                        <td>
                                            <div class="btn-group">
                                                @can('view', $service)
                                                    <a href="{{ route('services.show', $service->id) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                                        <i class="bx bx-show"></i>
                                                    </a>
                                                @endcan
                                                @can('update', $service)
                                                    <a href="{{ route('services.edit', $service->id) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                                                        <i class="bx bx-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('delete', $service)
                                                    <form action="{{ route('services.destroy', $service->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce service ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Aucun service trouvé</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $services->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

