@extends('layouts.app')

@section('title', 'Courriers Sortants')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Liste des Courriers Sortants</h5>
                    <div>
                        @can('viewAny', App\Models\CourrierSortant::class)
                            @if(auth()->user()->hasPermission('exports.excel'))
                                <a href="{{ route('exports.sortants.excel', request()->all()) }}" class="btn btn-success btn-sm me-2">
                                    <i class="bx bx-export"></i> Export Excel
                                </a>
                            @endif
                        @endcan
                        @can('create', App\Models\CourrierSortant::class)
                            <a href="{{ route('courriers.sortants.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus"></i> Nouveau Courrier
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <form method="GET" action="{{ route('courriers.sortants.index') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">NIM</label>
                                <input type="text" name="nim" class="form-control" value="{{ request('nim') }}" placeholder="DEP-2024-...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Destinataire</label>
                                <input type="text" name="destinataire_externe" class="form-control" value="{{ request('destinataire_externe') }}" placeholder="Destinataire externe">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Type</label>
                                <select name="type_courrier" class="form-select">
                                    <option value="">Tous</option>
                                    @foreach(config('courrier.types_courrier') as $key => $label)
                                        <option value="{{ $key }}" {{ request('type_courrier') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Statut</label>
                                <select name="statut" class="form-select">
                                    <option value="">Tous</option>
                                    @foreach(config('courrier.statuts_sortant') as $key => $label)
                                        <option value="{{ $key }}" {{ request('statut') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bx bx-search"></i> Filtrer
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Messages -->
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

                    <!-- Tableau -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>NIM</th>
                                    <th>Destinataire</th>
                                    <th>Provenance</th>
                                    <th>Type</th>
                                    <th>Date Départ</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($courriers as $courrier)
                                    <tr>
                                        <td>
                                            <strong>{{ $courrier->nim }}</strong>
                                            @if($courrier->niveau_confidentialite === 'urgent')
                                                <span class="badge bg-danger">Urgent</span>
                                            @elseif($courrier->niveau_confidentialite === 'confidentiel')
                                                <span class="badge bg-warning">Confidentiel</span>
                                            @elseif($courrier->niveau_confidentialite === 'secret_defense')
                                                <span class="badge bg-dark">Secret Défense</span>
                                            @endif
                                        </td>
                                        <td>{{ $courrier->destinataire_externe }}</td>
                                        <td>
                                            {{ $courrier->provenanceService->nom ?? '-' }}
                                            @if($courrier->provenanceUser)
                                                <br><small class="text-muted">{{ $courrier->provenanceUser->name }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ config('courrier.types_courrier')[$courrier->type_courrier] ?? $courrier->type_courrier }}</span>
                                        </td>
                                        <td>{{ $courrier->date_depart->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @php
                                                $statutColors = [
                                                    'enregistre' => 'secondary',
                                                    'transmis' => 'primary',
                                                    'livre' => 'success',
                                                    'confirme' => 'info',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statutColors[$courrier->statut] ?? 'secondary' }}">
                                                {{ config('courrier.statuts_sortant')[$courrier->statut] ?? $courrier->statut }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                @can('view', $courrier)
                                                    <a href="{{ route('courriers.sortants.show', $courrier->id) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                                        <i class="bx bx-show"></i>
                                                    </a>
                                                @endcan
                                                @can('update', $courrier)
                                                    <a href="{{ route('courriers.sortants.edit', $courrier->id) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                                                        <i class="bx bx-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('transmettre', $courrier)
                                                    @if($courrier->statut === 'enregistre')
                                                        <form action="{{ route('courriers.sortants.transmettre', $courrier->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Transmettre" onclick="return confirm('Transmettre ce courrier ?')">
                                                                <i class="bx bx-send"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endcan
                                                @can('confirmerLivraison', $courrier)
                                                    @if($courrier->statut === 'transmis')
                                                        <form action="{{ route('courriers.sortants.confirmer', $courrier->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-info" title="Confirmer livraison" onclick="return confirm('Confirmer la livraison ?')">
                                                                <i class="bx bx-check"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endcan
                                                @can('imprimerQr', $courrier)
                                                    <a href="{{ route('courriers.sortants.qr.pdf', $courrier->id) }}" class="btn btn-sm btn-outline-info" title="Imprimer QR" target="_blank">
                                                        <i class="bx bx-qr-scan"></i>
                                                    </a>
                                                @endcan
                                                @if(auth()->user()->hasPermission('exports.pdf'))
                                                    <a href="{{ route('exports.sortants.pdf', $courrier->id) }}" class="btn btn-sm btn-outline-secondary" title="Export PDF" target="_blank">
                                                        <i class="bx bx-file"></i>
                                                    </a>
                                                @endif
                                                @can('delete', $courrier)
                                                    <form action="{{ route('courriers.sortants.destroy', $courrier->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce courrier ?')">
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
                                        <td colspan="7" class="text-center">Aucun courrier trouvé</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $courriers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

