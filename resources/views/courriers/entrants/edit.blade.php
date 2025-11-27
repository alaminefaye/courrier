@extends('layouts.app')

@section('title', 'Modifier Courrier Entrant')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Modifier le Courrier Entrant - {{ $courrier->nim }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('courriers.entrants.update', $courrier->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">NIM</label>
                                <input type="text" class="form-control" value="{{ $courrier->nim }}" disabled>
                                <small class="text-muted">Le NIM ne peut pas être modifié</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Provenance (Expéditeur) <span class="text-danger">*</span></label>
                                <input type="text" name="provenance" class="form-control @error('provenance') is-invalid @enderror" value="{{ old('provenance', $courrier->provenance) }}" required>
                                @error('provenance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Service Destinataire <span class="text-danger">*</span></label>
                                <select name="destinataire_service_id" class="form-select @error('destinataire_service_id') is-invalid @enderror" required>
                                    <option value="">Sélectionner un service</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}" {{ old('destinataire_service_id', $courrier->destinataire_service_id) == $service->id ? 'selected' : '' }}>
                                            {{ $service->nom }} ({{ $service->direction->nom ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('destinataire_service_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Personne qui apporte le courrier <span class="text-danger">*</span></label>
                                <input type="text" name="personne_apporteur" class="form-control @error('personne_apporteur') is-invalid @enderror" value="{{ old('personne_apporteur', $courrier->personne_apporteur) }}" required>
                                @error('personne_apporteur')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type de Courrier <span class="text-danger">*</span></label>
                                <select name="type_courrier" class="form-select @error('type_courrier') is-invalid @enderror" required>
                                    @foreach(config('courrier.types_courrier') as $key => $label)
                                        <option value="{{ $key }}" {{ old('type_courrier', $courrier->type_courrier) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type_courrier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Niveau de Confidentialité <span class="text-danger">*</span></label>
                                <select name="niveau_confidentialite" class="form-select @error('niveau_confidentialite') is-invalid @enderror" required>
                                    @foreach(config('courrier.niveaux_confidentialite') as $key => $label)
                                        <option value="{{ $key }}" {{ old('niveau_confidentialite', $courrier->niveau_confidentialite) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('niveau_confidentialite')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Fichier Joint (optionnel)</label>
                                @if($courrier->fichier_joint)
                                    <div class="mb-2">
                                        <small class="text-muted">Fichier actuel: {{ basename($courrier->fichier_joint) }}</small>
                                    </div>
                                @endif
                                <input type="file" name="fichier_joint" class="form-control @error('fichier_joint') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <small class="text-muted">Laisser vide pour conserver le fichier actuel. Taille max: 10MB</small>
                                @error('fichier_joint')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Observations</label>
                                <textarea name="observations" class="form-control" rows="3">{{ old('observations', $courrier->observations) }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save"></i> Enregistrer les modifications
                            </button>
                            <a href="{{ route('courriers.entrants.show', $courrier->id) }}" class="btn btn-secondary">
                                <i class="bx bx-x"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

