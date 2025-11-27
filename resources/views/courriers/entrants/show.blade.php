@extends('layouts.app')

@section('title', 'Détails Courrier Entrant')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Détails du Courrier Entrant</h5>
                    <div>
                        <a href="{{ route('courriers.entrants.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bx bx-arrow-back"></i> Retour
                        </a>
                        @if($courrier->statut === 'enregistre')
                            <form action="{{ route('courriers.entrants.transmettre', $courrier->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Transmettre ce courrier ?')">
                                    <i class="bx bx-send"></i> Transmettre
                                </button>
                            </form>
                        @endif
                        @if($courrier->statut === 'transmis')
                            <form action="{{ route('courriers.entrants.confirmer', $courrier->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="signature_type" value="signature_numerique">
                                <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Confirmer la réception ?')">
                                    <i class="bx bx-check"></i> Confirmer Réception
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('courriers.entrants.qr.pdf', $courrier->id) }}" class="btn btn-info btn-sm" target="_blank">
                            <i class="bx bx-qr-scan"></i> Imprimer QR
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Informations Générales</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">NIM:</th>
                                    <td><strong>{{ $courrier->nim }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Provenance:</th>
                                    <td>{{ $courrier->provenance }}</td>
                                </tr>
                                <tr>
                                    <th>Service Destinataire:</th>
                                    <td>{{ $courrier->destinataireService->nom ?? '-' }}</td>
                                </tr>
                                @if($courrier->destinataireUser)
                                <tr>
                                    <th>Personne Destinataire:</th>
                                    <td>{{ $courrier->destinataireUser->name }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Personne Apporteur:</th>
                                    <td>{{ $courrier->personne_apporteur }}</td>
                                </tr>
                                <tr>
                                    <th>Date d'Arrivée:</th>
                                    <td>{{ $courrier->date_arrivee->format('d/m/Y à H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Classification</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Type:</th>
                                    <td>
                                        <span class="badge bg-info">{{ config('courrier.types_courrier')[$courrier->type_courrier] ?? $courrier->type_courrier }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Niveau Confidentialité:</th>
                                    <td>
                                        @php
                                            $confColors = [
                                                'ordinaire' => 'secondary',
                                                'urgent' => 'danger',
                                                'confidentiel' => 'warning',
                                                'secret_defense' => 'dark',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $confColors[$courrier->niveau_confidentialite] ?? 'secondary' }}">
                                            {{ config('courrier.niveaux_confidentialite')[$courrier->niveau_confidentialite] ?? $courrier->niveau_confidentialite }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Statut:</th>
                                    <td>
                                        @php
                                            $statutColors = [
                                                'enregistre' => 'secondary',
                                                'transmis' => 'primary',
                                                'recu' => 'success',
                                                'en_retard' => 'danger',
                                                'non_retire' => 'warning',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statutColors[$courrier->statut] ?? 'secondary' }}">
                                            {{ config('courrier.statuts_entrant')[$courrier->statut] ?? $courrier->statut }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Enregistré par:</th>
                                    <td>{{ $courrier->createdBy->name ?? '-' }}</td>
                                </tr>
                                @if($courrier->fichier_joint)
                                <tr>
                                    <th>Fichier Joint:</th>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-download"></i> Télécharger
                                        </a>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($courrier->observations)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-muted mb-2">Observations</h6>
                            <p>{{ $courrier->observations }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- QR Code -->
                    @if($courrier->qr_code)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">QR Code</h6>
                            <div class="text-center">
                                <img src="data:image/png;base64,{{ base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(200)->generate($courrier->qr_code)) }}" alt="QR Code" class="img-fluid">
                            </div>
                            <div class="text-center mt-2">
                                <small class="text-muted">Scannez ce QR Code pour vérifier l'authenticité du courrier</small>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Timeline -->
                    @if($timeline->count() > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">Historique (Timeline)</h6>
                            <div class="timeline">
                                @foreach($timeline as $event)
                                    <div class="timeline-item mb-3">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                <div class="avatar avatar-sm">
                                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                                        <i class="bx bx-time"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-1">{{ $event->user->name ?? 'Système' }}</h6>
                                                <p class="mb-0 text-muted">
                                                    <strong>{{ ucfirst($event->action) }}</strong> - 
                                                    {{ $event->created_at->format('d/m/Y à H:i') }}
                                                </p>
                                                @if($event->details)
                                                    <small class="text-muted">{{ json_decode($event->details, true)['message'] ?? '' }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
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

