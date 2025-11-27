@extends('layouts.app')

@section('title', 'Nouveau Courrier Entrant')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Enregistrer un Nouveau Courrier Entrant</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('courriers.entrants.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Provenance (Expéditeur) <span class="text-danger">*</span></label>
                                <input type="text" name="provenance" class="form-control @error('provenance') is-invalid @enderror" value="{{ old('provenance') }}" required>
                                @error('provenance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Service Destinataire <span class="text-danger">*</span></label>
                                <select name="destinataire_service_id" class="form-select @error('destinataire_service_id') is-invalid @enderror" required>
                                    <option value="">Sélectionner un service</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}" {{ old('destinataire_service_id') == $service->id ? 'selected' : '' }}>
                                            {{ $service->nom }} ({{ $service->direction->nom ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('destinataire_service_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Personne Destinataire (optionnel)</label>
                                <select name="destinataire_user_id" class="form-select">
                                    <option value="">Sélectionner une personne</option>
                                    <!-- TODO: Charger les utilisateurs du service sélectionné via AJAX -->
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Personne qui apporte le courrier <span class="text-danger">*</span></label>
                                <input type="text" name="personne_apporteur" class="form-control @error('personne_apporteur') is-invalid @enderror" value="{{ old('personne_apporteur') }}" required>
                                @error('personne_apporteur')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type de Courrier <span class="text-danger">*</span></label>
                                <select name="type_courrier" class="form-select @error('type_courrier') is-invalid @enderror" required>
                                    @foreach(config('courrier.types_courrier') as $key => $label)
                                        <option value="{{ $key }}" {{ old('type_courrier') == $key ? 'selected' : '' }}>{{ $label }}</option>
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
                                        <option value="{{ $key }}" {{ old('niveau_confidentialite', 'ordinaire') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('niveau_confidentialite')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Fichier Joint (optionnel)</label>
                                <input type="file" name="fichier_joint" class="form-control @error('fichier_joint') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <small class="text-muted">Taille max: 10MB. Formats: PDF, JPG, PNG, DOC, DOCX</small>
                                @error('fichier_joint')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Observations</label>
                                <textarea name="observations" class="form-control" rows="3">{{ old('observations') }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save"></i> Enregistrer
                            </button>
                            <a href="{{ route('courriers.entrants.index') }}" class="btn btn-secondary">
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

