@extends('layouts.app')

@section('title', 'Recherche Avancée')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recherche Avancée</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('recherche.index') }}">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Recherche (NIM, Provenance, Destinataire, etc.)</label>
                                <input type="text" name="recherche" class="form-control" value="{{ request('recherche') }}" placeholder="Entrez votre recherche...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select">
                                    <option value="tous" {{ request('type', 'tous') == 'tous' ? 'selected' : '' }}>Tous</option>
                                    <option value="entrants" {{ request('type') == 'entrants' ? 'selected' : '' }}>Entrants uniquement</option>
                                    <option value="sortants" {{ request('type') == 'sortants' ? 'selected' : '' }}>Sortants uniquement</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Type de Courrier</label>
                                <select name="type_courrier" class="form-select">
                                    <option value="">Tous</option>
                                    @foreach(config('courrier.types_courrier') as $key => $label)
                                        <option value="{{ $key }}" {{ request('type_courrier') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date Début</label>
                                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date Fin</label>
                                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Niveau Confidentialité</label>
                                <select name="niveau_confidentialite" class="form-select">
                                    <option value="">Tous</option>
                                    @foreach(config('courrier.niveaux_confidentialite') as $key => $label)
                                        <option value="{{ $key }}" {{ request('niveau_confidentialite') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bx bx-search"></i> Rechercher
                                </button>
                            </div>
                        </div>
                    </form>

                    @if(request()->filled('recherche'))
                        <div class="row">
                            <!-- Résultats Entrants -->
                            @if($resultatsEntrants->count() > 0)
                            <div class="col-md-6">
                                <h6 class="mb-3">Courriers Entrants ({{ $resultatsEntrants->count() }})</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>NIM</th>
                                                <th>Provenance</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($resultatsEntrants as $courrier)
                                                <tr>
                                                    <td><strong>{{ $courrier->nim }}</strong></td>
                                                    <td>{{ $courrier->provenance }}</td>
                                                    <td>{{ $courrier->date_arrivee->format('d/m/Y') }}</td>
                                                    <td>
                                                        <a href="{{ route('courriers.entrants.show', $courrier->id) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="bx bx-show"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif

                            <!-- Résultats Sortants -->
                            @if($resultatsSortants->count() > 0)
                            <div class="col-md-6">
                                <h6 class="mb-3">Courriers Sortants ({{ $resultatsSortants->count() }})</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>NIM</th>
                                                <th>Destinataire</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($resultatsSortants as $courrier)
                                                <tr>
                                                    <td><strong>{{ $courrier->nim }}</strong></td>
                                                    <td>{{ $courrier->destinataire_externe }}</td>
                                                    <td>{{ $courrier->date_depart->format('d/m/Y') }}</td>
                                                    <td>
                                                        <a href="{{ route('courriers.sortants.show', $courrier->id) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="bx bx-show"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif

                            @if($resultatsEntrants->count() == 0 && $resultatsSortants->count() == 0)
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        Aucun résultat trouvé pour votre recherche.
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

