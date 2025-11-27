@extends('layouts.app')

@section('title', 'Détails Direction')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Détails de la Direction</h5>
                    <div>
                        <a href="{{ route('directions.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bx bx-arrow-back"></i> Retour
                        </a>
                        <a href="{{ route('directions.edit', $direction->id) }}" class="btn btn-warning btn-sm">
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
                                    <td><strong>{{ $direction->code }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Nom:</th>
                                    <td>{{ $direction->nom }}</td>
                                </tr>
                                <tr>
                                    <th>Description:</th>
                                    <td>{{ $direction->description ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($direction->services->count() > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="mb-3">Services de cette Direction ({{ $direction->services->count() }})</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Nom</th>
                                            <th>Responsable</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($direction->services as $service)
                                            <tr>
                                                <td>{{ $service->code }}</td>
                                                <td>{{ $service->nom }}</td>
                                                <td>{{ $service->responsable->name ?? '-' }}</td>
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

