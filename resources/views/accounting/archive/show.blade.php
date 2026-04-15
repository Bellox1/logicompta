@extends('layouts.accounting')

@section('title', "Archives $year")

@section('styles')
    <style>
        .archive-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .archive-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            border-color: #0062cc;
        }

        .icon-box-archive {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            background: #f8fafc;
            color: #0062cc;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            transition: all 0.3s ease;
        }

        .archive-card:hover .icon-box-archive {
            background: #0062cc;
            color: white;
            transform: scale(1.1) rotate(5deg);
        }

        .badge-secure {
            background: #fffbeb;
            color: #b45309;
            padding: 8px 16px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            border: 1px solid #fef3c7;
        }

        .btn-view {
            background: #f8fafc;
            color: #1e293b;
            border-top: 1px solid #f1f5f9;
            padding: 20px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 11px;
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-decoration: none !important;
        }

        .archive-card:hover .btn-view {
            background: #0062cc;
            color: white;
        }
    </style>
@endsection

@section('content')
    <div class="mb-5">
        <div class="d-flex align-items-center mb-3">
            <a href="{{ route('accounting.archive.index') }}" class="btn btn-light btn-sm rounded-pill px-3 font-weight-bold d-inline-flex align-items-center text-muted border-0 mr-3">
                <span class="material-symbols-outlined mr-1" style="font-size: 18px;">arrow_back</span> Retour Hub
            </a>
            <div class="badge-secure">
                <span class="material-symbols-outlined mr-1" style="font-size: 16px;">verified_user</span> Coffre Scellé
            </div>
        </div>
        
        <h1 class="h2 font-weight-bold text-dark mb-1 font-manrope uppercase">Archive Exercice {{ $year }}</h1>
        <p class="text-muted small font-weight-bold italic mb-0">Rapports définitifs et écritures comptables certifiées ({{ number_format($totalEntries, 0, ',', ' ') }} lignes).</p>
    </div>

    <div class="row">
        <!-- JOURNAL -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="archive-card">
                <div class="p-4 flex-grow-1">
                    <div class="icon-box-archive">
                        <span class="material-symbols-outlined" style="font-size: 32px;">menu_book</span>
                    </div>
                    <h3 class="font-weight-bold h5 mb-3 font-manrope">Journal</h3>
                    <p class="text-secondary small font-weight-bold italic mb-0">Historique chronologique complet des écritures scellées.</p>
                </div>
                <a href="{{ $links['journal'] }}" class="btn-view">
                    Consulter <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            </div>
        </div>

        <!-- BALANCE -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="archive-card">
                <div class="p-4 flex-grow-1">
                    <div class="icon-box-archive">
                        <span class="material-symbols-outlined" style="font-size: 32px;">balance</span>
                    </div>
                    <h3 class="font-weight-bold h5 mb-3 font-manrope">Balance</h3>
                    <p class="text-secondary small font-weight-bold italic mb-0">Soldes créditeurs et débiteurs de tous les comptes.</p>
                </div>
                <a href="{{ $links['balance'] }}" class="btn-view">
                    Consulter <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            </div>
        </div>

        <!-- BILAN -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="archive-card">
                <div class="p-4 flex-grow-1">
                    <div class="icon-box-archive">
                        <span class="material-symbols-outlined" style="font-size: 32px;">account_balance</span>
                    </div>
                    <h3 class="font-weight-bold h5 mb-3 font-manrope">Bilan</h3>
                    <p class="text-secondary small font-weight-bold italic mb-0">État patrimonial de clôture (Actif & Passif).</p>
                </div>
                <a href="{{ $links['bilan'] }}" class="btn-view">
                    Consulter <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            </div>
        </div>

        <!-- RÉSULTAT -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="archive-card">
                <div class="p-4 flex-grow-1">
                    <div class="icon-box-archive">
                        <span class="material-symbols-outlined" style="font-size: 32px;">analytics</span>
                    </div>
                    <h3 class="font-weight-bold h5 mb-3 font-manrope">Résultat</h3>
                    <p class="text-secondary small font-weight-bold italic mb-0">Analyse détaillée du bénéfice ou de la perte.</p>
                </div>
                <a href="{{ $links['resultat'] }}" class="btn-view">
                    Consulter <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            </div>
        </div>
    </div>
@endsection
