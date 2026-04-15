@extends('layouts.accounting')

@section('title', 'Tableau de bord')

@section('content')
    <div class="container-fluid p-0">
        {{-- Quick Actions Top Bar --}}
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('profile') }}"
                class="btn btn-white bg-white shadow-sm border d-flex align-items-center px-4 py-2"
                style="border-radius: 12px; transition: 0.3s; font-weight: 700; color: var(--primary-color);">
                <span class="material-symbols-outlined mr-2" style="font-size: 18px;">account_circle</span>
                <span class="text-uppercase tracking-wider" style="font-size: 11px;">Mon Profil</span>
            </a>
        </div>

        {{-- Hero Header (Reduced height) --}}
        <div class="position-relative overflow-hidden p-4 text-white shadow-sm mb-5"
            style="background: linear-gradient(135deg, var(--primary-color) 0%, #004d8a 100%); border-radius: 18px;">
            <div class="position-absolute" style="top: -40px; right: -40px; opacity: 0.1; pointer-events: none;">
                <svg viewBox="0 0 200 200" width="200" height="200">
                    <circle cx="100" cy="100" r="80" fill="white" />
                </svg>
            </div>

            <div class="row align-items-center position-relative" style="z-index: 2;">
                <div class="col-lg-8">
                    <h1 class="h2 font-weight-bold mb-1" style="font-family: 'Manrope';">Bienvenue, <span style="color: #ffca28;">{{ $user->name }}</span> 👋</h1>
                    <p class="mb-0 small" style="opacity: 0.9;">
                        Connecté en tant que 
                        <span class="badge badge-light px-2 py-1 text-primary ml-1" style="border-radius: 6px; font-size: 10px; text-transform: uppercase;">
                            {{ $user->role == 'admin' ? 'Administrateur' : ($user->role == 'comptable' ? 'Comptable' : 'Utilisateur') }}
                        </span>
                    </p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0">
                    @if ($user->entreprise)
                        <div onclick="showEnterpriseModal()"
                            class="bg-white text-dark p-3 shadow-sm cursor-pointer hover-lift transition-all"
                            style="border-radius: 14px; background: rgba(255,255,255,0.1) !important; border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(5px);">
                            <div class="d-flex align-items-center">
                                <div class="bg-white text-primary rounded d-flex align-items-center justify-content-center mr-3"
                                    style="width: 35px; height: 35px; flex-shrink: 0;">
                                    <span class="material-symbols-outlined" style="font-size: 20px;">corporate_fare</span>
                                </div>
                                <div class="overflow-hidden">
                                    <span class="text-uppercase font-weight-bold d-block text-white-50"
                                        style="font-size: 8px; letter-spacing: 1px;">Espace Actif</span>
                                    <h2 class="h6 mb-0 font-weight-bold text-white text-truncate">{{ $user->entreprise->name }}</h2>
                                    @if ($user->role == 'admin')
                                        <div class="d-flex align-items-center mt-1">
                                            <code class="text-white-50 px-1 rounded small mr-1" 
                                                  style="font-size: 9px; background: rgba(0,0,0,0.2);">#{{ $user->entreprise->code }}</code>
                                            <span class="material-symbols-outlined text-white-50" 
                                                  style="font-size: 12px; cursor: pointer;"
                                                  onclick="event.stopPropagation(); navigator.clipboard.writeText('{{ $user->entreprise->code }}'); alert('Code copié !')">content_copy</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Modules Grid --}}
        <div class="mb-5">
            <h5 class="text-uppercase font-weight-bold text-muted mb-4 d-flex align-items-center" style="font-size: 11px; letter-spacing: 2px;">
                <span class="mr-3">Gestion & Pilotage</span>
                <div class="flex-grow-1" style="height: 1px; background: #eee;"></div>
            </h5>
            <div class="row">
                {{-- 1. Paramétrage --}}
                <div class="col-md-6 col-lg-4 mb-4">
                    <a href="{{ route('accounting.journals-settings.index') }}"
                        class="card h-100 border-0 shadow-sm text-dark overflow-hidden text-decoration-none transition-all hover-transform"
                        style="border-radius: 15px; border: 1px solid #f0f0f0 !important;">
                        <div class="card-body p-4">
                            <div class="bg-primary text-white rounded-lg d-flex align-items-center justify-content-center mb-4 shadow-sm"
                                style="width: 45px; height: 45px; background-color: var(--primary-color) !important;">
                                <span class="material-symbols-outlined">settings_suggest</span>
                            </div>
                            <h3 class="h5 font-weight-bold mb-2">Paramètres</h3>
                            <p class="small text-muted mb-4">Structure comptable et journaux.</p>
                            <div class="d-flex align-items-center font-weight-bold text-uppercase text-primary"
                                style="font-size: 10px; letter-spacing: 1px;">
                                Ouvrir <span class="material-symbols-outlined ml-1" style="font-size: 16px;">arrow_forward</span>
                            </div>
                        </div>
                    </a>
                </div>

                {{-- 2. Boîte Noire --}}
                @if (Auth::user()->role === 'admin')
                    <div class="col-md-6 col-lg-4 mb-4">
                        <a href="{{ route('accounting.traceabilite.index') }}"
                            class="card h-100 border-0 shadow-sm text-dark overflow-hidden text-decoration-none transition-all hover-transform"
                            style="border-radius: 15px; border: 1px solid #f0f0f0 !important;">
                            <div class="card-body p-4">
                                <div class="bg-dark text-white rounded-lg d-flex align-items-center justify-content-center mb-4 shadow-sm"
                                    style="width: 45px; height: 45px; background-color: #1a1c2e !important;">
                                    <span class="material-symbols-outlined">history_edu</span>
                                </div>
                                <h3 class="h5 font-weight-bold mb-2">Boîte Noire</h3>
                                <p class="small text-muted mb-4">Traçabilité et sécurité.</p>
                                <div class="d-flex align-items-center font-weight-bold text-uppercase text-dark"
                                    style="font-size: 10px; letter-spacing: 1px;">
                                    Consulter <span class="material-symbols-outlined ml-1" style="font-size: 16px;">terminal</span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endif

                {{-- 3. Journal --}}
                <div class="col-md-6 col-lg-4 mb-4">
                    <a href="{{ route('accounting.journal.index') }}"
                        class="card h-100 border-0 shadow-sm text-dark overflow-hidden text-decoration-none transition-all hover-transform"
                        style="border-radius: 15px; border: 1px solid #f0f0f0 !important;">
                        <div class="card-body p-4">
                            <div class="bg-primary text-white rounded-lg d-flex align-items-center justify-content-center mb-4 shadow-sm"
                                style="width: 45px; height: 45px; background-color: var(--primary-color) !important;">
                                <span class="material-symbols-outlined">menu_book</span>
                            </div>
                            <h3 class="h5 font-weight-bold mb-2">Journal de Saisie</h3>
                            <p class="small text-muted mb-4">Écritures au quotidien.</p>
                            <div class="d-flex align-items-center font-weight-bold text-uppercase text-primary"
                                style="font-size: 10px; letter-spacing: 1px;">
                                Accéder <span class="material-symbols-outlined ml-1" style="font-size: 16px;">arrow_forward</span>
                            </div>
                        </div>
                    </a>
                </div>

                {{-- 4. Grand Livre --}}
                <div class="col-md-6 col-lg-4 mb-4">
                    <a href="{{ route('accounting.ledger') }}"
                        class="card h-100 border-0 shadow-sm text-dark overflow-hidden text-decoration-none transition-all hover-transform"
                        style="border-radius: 15px; border: 1px solid #f0f0f0 !important;">
                        <div class="card-body p-4">
                            <div class="bg-primary text-white rounded-lg d-flex align-items-center justify-content-center mb-4 shadow-sm"
                                style="width: 45px; height: 45px; background-color: var(--primary-color) !important;">
                                <span class="material-symbols-outlined">list_alt</span>
                            </div>
                            <h3 class="h5 font-weight-bold mb-2">Grand Livre</h3>
                            <p class="small text-muted mb-4">Détails des comptes.</p>
                            <div class="d-flex align-items-center font-weight-bold text-uppercase text-primary"
                                style="font-size: 10px; letter-spacing: 1px;">
                                Ouvrir <span class="material-symbols-outlined ml-1" style="font-size: 16px;">arrow_forward</span>
                            </div>
                        </div>
                    </a>
                </div>

                {{-- 5. Balance --}}
                <div class="col-md-6 col-lg-4 mb-4">
                    <a href="{{ route('accounting.balance') }}"
                        class="card h-100 border-0 shadow-sm text-dark overflow-hidden text-decoration-none transition-all hover-transform"
                        style="border-radius: 15px; border: 1px solid #f0f0f0 !important;">
                        <div class="card-body p-4">
                            <div class="bg-primary text-white rounded-lg d-flex align-items-center justify-content-center mb-4 shadow-sm"
                                style="width: 45px; height: 45px; background-color: var(--primary-color) !important;">
                                <span class="material-symbols-outlined">balance</span>
                            </div>
                            <h3 class="h5 font-weight-bold mb-2">Balance</h3>
                            <p class="small text-muted mb-4">Équilibre des comptes.</p>
                            <div class="d-flex align-items-center font-weight-bold text-uppercase text-primary"
                                style="font-size: 10px; letter-spacing: 1px;">
                                Consulter <span class="material-symbols-outlined ml-1" style="font-size: 16px;">arrow_forward</span>
                            </div>
                        </div>
                    </a>
                </div>

                {{-- 6. Bilan --}}
                <div class="col-md-6 col-lg-4 mb-4">
                    <a href="{{ route('accounting.bilan') }}"
                        class="card h-100 border-0 shadow-sm text-dark overflow-hidden text-decoration-none transition-all hover-transform"
                        style="border-radius: 15px; border: 1px solid #f0f0f0 !important;">
                        <div class="card-body p-4">
                            <div class="bg-primary text-white rounded-lg d-flex align-items-center justify-content-center mb-4 shadow-sm"
                                style="width: 45px; height: 45px; background-color: var(--primary-color) !important;">
                                <span class="material-symbols-outlined">analytics</span>
                            </div>
                            <h3 class="h5 font-weight-bold mb-2">États Financiers</h3>
                            <p class="small text-muted mb-4">Bilan & Résultats.</p>
                            <div class="d-flex align-items-center font-weight-bold text-uppercase text-primary"
                                style="font-size: 10px; letter-spacing: 1px;">
                                Voir plus <span class="material-symbols-outlined ml-1" style="font-size: 16px;">arrow_forward</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        {{-- Alerte si pas d'entreprise (Reduced size) --}}
        @if (!$user->entreprise)
            <div class="alert alert-light border shadow-sm p-4 mb-5" style="border-radius: 18px;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="font-weight-bold mb-1">Aucune entreprise associée</h5>
                        <p class="text-muted mb-0 small">Associez votre compte à une structure existante ou créez la vôtre.</p>
                    </div>
                    <div class="col-md-4 mt-3 mt-md-0 d-flex justify-content-md-end">
                        <a href="{{ url('/entreprise-setup') }}" class="btn btn-primary font-weight-bold px-4 rounded-pill">Démarrer</a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal Entreprise --}}
    @if ($user->entreprises->count() > 0)
        <div id="enterprise-selection-modal"
            class="modal fade {{ session()->has('active_entreprise_id') ? '' : 'show d-block' }}"
            style="background: rgba(0,0,0,0.5); backdrop-filter: blur(5px);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0" style="border-radius: 20px;">
                    <div class="modal-header border-0 bg-light p-4">
                        <h6 class="modal-title font-weight-bold text-uppercase">VOTRE ESPACE</h6>
                        <button type="button" class="close" onclick="hideEnterpriseModal()">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="list-group list-group-flush">
                            @foreach ($user->entreprises as $entreprise)
                                <form action="{{ route('accounting.entreprise.switch') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="entreprise_id" value="{{ $entreprise->id }}">
                                    <button type="submit"
                                        class="list-group-item list-group-item-action border-0 mb-2 p-3 d-flex align-items-center {{ $user->entreprise && $user->entreprise->id == $entreprise->id ? 'bg-light font-weight-bold' : '' }}"
                                        style="border-radius: 12px; transition: 0.2s;">
                                        <span class="material-symbols-outlined mr-3 text-primary">corporate_fare</span>
                                        <div class="flex-grow-1">
                                            <div class="small">{{ $entreprise->name }}</div>
                                            <div class="text-muted lowercase" style="font-size: 10px;">{{ $entreprise->pivot->role }}</div>
                                        </div>
                                        @if($user->entreprise && $user->entreprise->id == $entreprise->id)
                                            <span class="material-symbols-outlined text-success" style="font-size: 18px;">check_circle</span>
                                        @endif
                                    </button>
                                </form>
                            @endforeach
                            <a href="{{ url('/entreprise-setup') }}" class="btn btn-outline-primary btn-sm btn-block mt-3 rounded-pill py-2 font-weight-bold">
                                + Nouvelle Société
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .hover-transform:hover { transform: translateY(-5px); transition: 0.3s; }
        .hover-lift:hover { transform: scale(1.02); }
    </style>
@endsection

@section('scripts')
    <script>
        function showEnterpriseModal() { $('#enterprise-selection-modal').removeClass('fade').addClass('d-block'); }
        function hideEnterpriseModal() { $('#enterprise-selection-modal').addClass('fade').removeClass('d-block'); }
    </script>
@endsection
