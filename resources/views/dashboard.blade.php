@extends('layouts.app')

@section('title', 'Dashboard')

@push('vendor-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- En-tête avec actions rapides -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1">Tableau de Bord</h4>
                    <p class="text-muted mb-0">Vue d'ensemble de votre activité</p>
                </div>
                <div>
                    @can('create', App\Models\CourrierEntrant::class)
                        <a href="{{ route('courriers.entrants.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus"></i> Nouveau Courrier
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques principales - 4 cartes -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-down-arrow-circle fs-4"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <span class="text-muted d-block mb-1">Entrants Aujourd'hui</span>
                            <h3 class="mb-0">{{ $entrantsAujourdhui }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-up-arrow-circle fs-4"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <span class="text-muted d-block mb-1">Sortants Aujourd'hui</span>
                            <h3 class="mb-0">{{ $sortantsAujourdhui }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="bx bx-error-circle fs-4"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <span class="text-muted d-block mb-1">En Retard</span>
                            <h3 class="mb-0">{{ $entrantsEnRetard }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="bx bx-time fs-4"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <span class="text-muted d-block mb-1">Urgents</span>
                            <h3 class="mb-0">{{ $entrantsUrgents }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques - 2 colonnes -->
    <div class="row g-3 mb-4">
        <!-- Évolution Mensuelle -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Évolution Mensuelle</h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-label-primary">Entrants</span>
                        <span class="badge bg-label-success">Sortants</span>
                    </div>
                </div>
                <div class="card-body">
                    <div id="evolutionChart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>

        <!-- Répartition par Type -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Répartition par Type</h5>
                </div>
                <div class="card-body">
                    <div id="repartitionChart" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Services et Actions Rapides -->
    <div class="row g-3">
        <!-- Top Services -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Top Services</h5>
                    <a href="{{ route('services.index') }}" class="btn btn-sm btn-link p-0">Voir tout</a>
                </div>
                <div class="card-body">
                    @if($topServices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Service</th>
                                        <th class="text-end">Courriers</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topServices as $index => $service)
                                        <tr>
                                            <td>
                                                <span class="badge bg-label-primary">{{ $index + 1 }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold">{{ $service->nom }}</span>
                                                    <small class="text-muted">{{ $service->code }}</small>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-semibold">{{ $service->total_entrants }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">Aucune donnée disponible</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions Rapides -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions Rapides</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @can('create', App\Models\CourrierEntrant::class)
                            <a href="{{ route('courriers.entrants.create') }}" class="btn btn-outline-primary">
                                <i class="bx bx-plus"></i> Nouveau Courrier Entrant
                            </a>
                        @endcan
                        @can('create', App\Models\CourrierSortant::class)
                            <a href="{{ route('courriers.sortants.create') }}" class="btn btn-outline-success">
                                <i class="bx bx-plus"></i> Nouveau Courrier Sortant
                            </a>
                        @endcan
                        @if(auth()->user()->hasPermission('recherche.view'))
                            <a href="{{ route('recherche.index') }}" class="btn btn-outline-info">
                                <i class="bx bx-search"></i> Recherche Avancée
                            </a>
                        @endif
                        @can('viewAny', App\Models\CourrierEntrant::class)
                            @if(auth()->user()->isAdmin() || auth()->user()->isDirecteur())
                                <a href="{{ route('courriers.entrants.index', ['statut' => 'en_retard']) }}" class="btn btn-outline-danger">
                                    <i class="bx bx-error-circle"></i> Voir Courriers en Retard
                                </a>
                            @endif
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('vendor-js')
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
@endpush

@push('page-js')
<script>
    // Données pour les graphiques
    const evolutionData = @json($evolutionMensuelle);
    const repartitionData = @json($repartitionType);

    // Graphique Évolution Mensuelle - Version améliorée
    const evolutionChartEl = document.querySelector('#evolutionChart');
    if (evolutionChartEl) {
        const evolutionChart = new ApexCharts(evolutionChartEl, {
            chart: {
                type: 'bar',
                height: 350,
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '60%',
                    borderRadius: 4,
                    dataLabels: {
                        position: 'top'
                    }
                }
            },
            dataLabels: {
                enabled: true,
                offsetY: -20,
                style: {
                    fontSize: '12px',
                    colors: ['#697a8d']
                }
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: evolutionData.map(d => d.mois),
                labels: {
                    style: {
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                title: {
                    text: 'Nombre de courriers',
                    style: {
                        fontSize: '12px'
                    }
                },
                labels: {
                    style: {
                        fontSize: '12px'
                    }
                }
            },
            fill: {
                opacity: 1
            },
            series: [{
                name: 'Entrants',
                data: evolutionData.map(d => d.entrants)
            }, {
                name: 'Sortants',
                data: evolutionData.map(d => d.sortants)
            }],
            colors: ['#696cff', '#71dd37'],
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'right'
            },
            grid: {
                borderColor: '#e7e7e7',
                strokeDashArray: 4
            }
        });
        evolutionChart.render();
    }

    // Graphique Répartition par Type - Version améliorée
    const repartitionChartEl = document.querySelector('#repartitionChart');
    if (repartitionChartEl) {
        const repartitionChart = new ApexCharts(repartitionChartEl, {
            chart: {
                type: 'donut',
                height: 300,
                fontFamily: 'inherit'
            },
            labels: ['Ordinaire', 'Urgent', 'Confidentiel', 'Secret Défense'],
            series: [
                repartitionData.ordinaire || 0,
                repartitionData.urgent || 0,
                repartitionData.confidentiel || 0,
                repartitionData.secret_defense || 0
            ],
            colors: ['#696cff', '#ff3e1d', '#ffab00', '#233446'],
            legend: {
                show: true,
                position: 'bottom',
                fontSize: '12px'
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                fontSize: '14px'
                            },
                            value: {
                                show: true,
                                fontSize: '16px',
                                fontWeight: 600
                            },
                            total: {
                                show: true,
                                label: 'Total',
                                fontSize: '14px',
                                formatter: function() {
                                    const total = repartitionData.ordinaire + repartitionData.urgent + 
                                                  repartitionData.confidentiel + repartitionData.secret_defense;
                                    return total;
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function(val, opts) {
                    return val.toFixed(1) + '%';
                }
            }
        });
        repartitionChart.render();
    }
</script>
@endpush
