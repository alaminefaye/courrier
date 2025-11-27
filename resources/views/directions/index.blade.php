@extends('layouts.app')

@section('title', 'Directions')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Liste des Directions</h5>
                    @can('create', App\Models\Direction::class)
                        <a href="{{ route('directions.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus"></i> Nouvelle Direction
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
                                    <th>Description</th>
                                    <th>Nombre de Services</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($directions as $direction)
                                    <tr>
                                        <td><strong>{{ $direction->code }}</strong></td>
                                        <td>{{ $direction->nom }}</td>
                                        <td>{{ $direction->description ?? '-' }}</td>
                                        <td><span class="badge bg-info">{{ $direction->services_count }}</span></td>
                                        <td>
                                            <div class="btn-group">
                                                @can('view', $direction)
                                                    <a href="{{ route('directions.show', $direction->id) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                                        <i class="bx bx-show"></i>
                                                    </a>
                                                @endcan
                                                @can('update', $direction)
                                                    <a href="{{ route('directions.edit', $direction->id) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                                                        <i class="bx bx-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('delete', $direction)
                                                    <form action="{{ route('directions.destroy', $direction->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette direction ?')">
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
                                        <td colspan="5" class="text-center">Aucune direction trouvée</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $directions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

