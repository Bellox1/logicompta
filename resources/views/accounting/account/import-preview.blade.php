@extends('layouts.accounting')

@section('title', 'Aperçu de l\'import')

@section('styles')
    <style>
        .preview-table thead th {
            background: #f8fafc;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            padding: 15px;
            border-bottom: 2px solid #f1f5f9;
        }

        .preview-table tbody td {
            font-size: 13px;
            padding: 15px;
            font-weight: 600;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .status-badge {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 5px 12px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
        }

        .premium-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            overflow: hidden;
        }

        .error-row {
            background-color: #fff1f2;
            border-left: 4px solid #f43f5e;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
    </style>
@endsection

@section('content')
    <div class="mb-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <h1 class="h3 font-weight-bold text-dark mb-1" style="font-family: 'Manrope';">Aperçu de l'import</h1>
            <p class="text-muted small font-weight-bold uppercase tracking-wider">Vérification de l'intégrité avant validation</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('accounting.account.import') }}" class="btn btn-white btn-sm px-4 py-2 font-weight-bold border rounded-lg shadow-sm">
                <span class="material-symbols-outlined align-middle mr-1" style="font-size: 18px;">arrow_back</span> CHANGER DE FICHIER
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-9 mb-4">
            <div class="premium-card">
                <div class="table-responsive">
                    <table class="table preview-table mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>NUMÉRO</th>
                                <th>LIBELLÉ</th>
                                <th>PARENT</th>
                                <th>STATUT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($previewData as $row)
                                @php $isError = str_starts_with($row['status'], 'error_'); @endphp
                                <tr class="{{ $isError ? 'error-row' : '' }}">
                                    <td class="text-muted font-mono small">{{ $row['line'] }}</td>
                                    <td class="font-weight-bold text-primary">{{ $row['numero'] }}</td>
                                    <td class="font-weight-bold text-dark">{{ $row['libelle'] }}</td>
                                    <td>
                                        @if($row['parent'])
                                            <span class="badge badge-light px-2 py-1 text-primary border font-weight-bold">{{ $row['parent'] }}</span>
                                        @else
                                            <span class="text-danger small font-weight-bold">INCONNU</span>
                                        @endif
                                    </td>
                                    <td>
                                        @switch($row['status'])
                                            @case('new')
                                                <span class="status-badge bg-success text-white">Nouveau</span>
                                                @break
                                            @case('update')
                                                <span class="status-badge bg-primary text-white">Mise à jour</span>
                                                @break
                                            @case('error_main')
                                                <span class="status-badge bg-danger text-white">Compte Principal</span>
                                                @break
                                            @case('error_no_parent')
                                                <span class="status-badge bg-danger text-white">Parent Inconnu</span>
                                                @break
                                        @endswitch
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="premium-card p-4 sticky-top" style="top: 20px;">
                <h6 class="font-weight-bold text-uppercase text-muted small mb-4" style="letter-spacing: 1px;">Résumé de l'opération</h6>
                
                @php
                    $newCount = collect($previewData)->where('status', 'new')->count();
                    $updateCount = collect($previewData)->where('status', 'update')->count();
                    $errorCount = collect($previewData)->whereIn('status', ['error_main', 'error_no_parent'])->count();
                @endphp

                <div class="summary-item">
                    <span class="small font-weight-bold text-muted">À CRÉER</span>
                    <span class="badge badge-success px-3">{{ $newCount }}</span>
                </div>
                <div class="summary-item">
                    <span class="small font-weight-bold text-muted">À MAJ</span>
                    <span class="badge badge-primary px-3">{{ $updateCount }}</span>
                </div>
                <div class="summary-item mb-4">
                    <span class="small font-weight-bold text-muted">INVALIDES</span>
                    <span class="badge badge-danger px-3">{{ $errorCount }}</span>
                </div>

                <form action="{{ route('accounting.account.import.process') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold rounded-xl shadow-lg text-uppercase" style="letter-spacing: 1px;">
                        CONFIRMER L'IMPORT <span class="material-symbols-outlined align-middle ml-2" style="font-size: 18px;">check_circle</span>
                    </button>
                </form>

                @if($errorCount > 0)
                    <div class="mt-4 p-3 bg-light rounded-lg border-left border-danger">
                        <p class="small text-danger font-weight-bold mb-0">
                            <span class="material-symbols-outlined align-middle mr-1" style="font-size: 16px;">warning</span>
                            {{ $errorCount }} ligne(s) invalide(s) seront ignorées.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
