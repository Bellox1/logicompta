@extends('layouts.accounting')

@section('title', 'Centre d\'Aide Comptable')

@section('content')
    <style>
        .help-header-section {
            background-color: #fff;
            border-radius: 20px;
            padding: 50px 30px;
            border: 1px solid #eef2f7;
            margin-bottom: 35px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        }

        .help-card-custom {
            border: none;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
            height: 100%;
            background: #fff;
        }

        .help-card-custom:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
        }

        .icon-circle {
            width: 65px;
            height: 65px;
            background-color: rgba(0, 98, 204, 0.08);
            color: #0062cc;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 26px;
        }

        .class-list {
            padding: 0;
            list-style: none;
        }

        .class-item {
            display: flex;
            align-items: center;
            padding: 12px 18px;
            background: #f8f9fc;
            border-radius: 12px;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: 700;
            border: 1px solid transparent;
        }

        .class-item:hover {
            border-color: #0062cc;
            background: #fff;
        }

        .class-number {
            background: #0062cc;
            color: #fff;
            width: 26px;
            height: 26px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            margin-right: 18px;
            flex-shrink: 0;
        }

        .rule-section {
            background: #1a1c2e;
            color: #fff;
            border-radius: 25px;
            padding: 45px;
            margin-top: 30px;
            position: relative;
            overflow: hidden;
        }

        .rule-card {
            background: rgba(255, 255, 255, 0.04);
            padding: 25px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            height: 100%;
            transition: all 0.3s;
        }

        .rule-card:hover {
            background: rgba(255, 255, 255, 0.07);
            border-color: #0062cc;
        }

        .rule-card i {
            font-size: 28px;
            color: #38bdf8;
            margin-bottom: 20px;
            display: block;
        }

        .rule-highlight {
            border: 2px solid #38bdf8;
            background: rgba(56, 189, 248, 0.05);
        }
    </style>

    <div class="help-header-section">
        <span class="badge badge-primary px-3 py-2 rounded-pill mb-3 uppercase"
            style="font-size: 10px; letter-spacing: 1px; font-weight: 800;">Support Technique & Métier</span>
        <h1 class="font-weight-bold mb-2 h2">Centre d'Aide COMPTAFIQ</h1>
        <p class="text-muted font-weight-bold mb-0">Référentiel comptable SYSCOHADA & Bonnes Pratiques.</p>
    </div>

    <div class="row">
        <!-- Plan Comptable -->
        <div class="col-lg-6 mb-4">
            <div class="card help-card-custom">
                <div class="card-body p-4">
                    <div class="icon-circle">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <h4 class="text-center font-weight-bold mb-4">Le Plan Comptable</h4>
                    <div class="class-list">
                        <div class="class-item">
                            <div class="class-number">1</div> Ressources durables
                        </div>
                        <div class="class-item">
                            <div class="class-number">2</div> Actif immobilisé
                        </div>
                        <div class="class-item">
                            <div class="class-number">3</div> Stocks et en-cours
                        </div>
                        <div class="class-item">
                            <div class="class-number">4</div> Comptes de tiers
                        </div>
                        <div class="class-item">
                            <div class="class-number">5</div> Comptes de trésorerie
                        </div>
                        <div class="class-item">
                            <div class="class-number">6</div> Charges
                        </div>
                        <div class="class-item">
                            <div class="class-number">7</div> Produits
                        </div>
                        <div class="class-item">
                            <div class="class-number">8</div> Autres profits et pertes
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- États de Synthèse -->
        <div class="col-lg-6 mb-4">
            <div class="card help-card-custom">
                <div class="card-body p-4">
                    <div class="icon-circle" style="color: #28a745; background: rgba(40, 167, 69, 0.1);">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <h4 class="text-center font-weight-bold mb-4">États de Synthèse</h4>
                    <div class="mt-4 px-2">
                        <div class="media mb-4 align-items-center">
                            <i class="fas fa-book-open text-primary mr-4" style="font-size: 20px; width: 25px;"></i>
                            <div class="media-body">
                                <h6 class="font-weight-bold mb-1">Le Journal Général</h6>
                                <p class="small text-muted mb-0">Livre obligatoire où les écritures sont enregistrées jour
                                    par jour.</p>
                            </div>
                        </div>
                        <div class="media mb-4 align-items-center">
                            <i class="fas fa-stream text-primary mr-4" style="font-size: 20px; width: 25px;"></i>
                            <div class="media-body">
                                <h6 class="font-weight-bold mb-1">Le Grand Livre</h6>
                                <p class="small text-muted mb-0">Regroupement des écritures du journal par compte
                                    individuel.</p>
                            </div>
                        </div>
                        <div class="media mb-4 align-items-center">
                            <i class="fas fa-scale-balanced text-primary mr-4" style="font-size: 20px; width: 25px;"></i>
                            <div class="media-body">
                                <h6 class="font-weight-bold mb-1">La Balance</h6>
                                <p class="small text-muted mb-0">Contrôle périodique de l'égalité Débit/Crédit.</p>
                            </div>
                        </div>
                        <div class="media align-items-center">
                            <i class="fas fa-chart-pie text-primary mr-4" style="font-size: 20px; width: 25px;"></i>
                            <div class="media-body">
                                <h6 class="font-weight-bold mb-1">Bilan & Compte de Résultat</h6>
                                <p class="small text-muted mb-0">Synthèse annuelle de la situation et du profit.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Règles de Rigueur -->
    <div class="rule-section">
        <div class="d-flex align-items-center mb-4 ml-2">
            <i class="fas fa-gavel mr-3" style="font-size: 24px; color: #38bdf8;"></i>
            <h4 class="font-weight-bold mb-0">Règles de Rigueur & Conformité</h4>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="rule-card rule-highlight">
                    <i class="fas fa-edit"></i>
                    <h5 class="font-weight-bold mb-3" style="color: #38bdf8;">Flexibilité</h5>
                    <p class="small opacity-90 font-weight-bold" style="line-height: 1.6;">
                        Droit à l'erreur : Le système vous permet de modifier ou de supprimer vos pièces comptables en cas
                        de besoin.
                    </p>
                    <p class="extra-small opacity-60 mt-2 italic">Vous gardez le contrôle total sur vos saisies pour une
                        gestion simplifiée.</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="rule-card">
                    <i class="fas fa-balance-scale-right"></i>
                    <h5 class="font-weight-bold mb-3 text-white">Équilibre Strict</h5>
                    <p class="small opacity-90" style="line-height: 1.6;">
                        Aucune pièce comptable ne peut être enregistrée si le total des Débits n'est pas strictement égal au
                        total des Crédits.
                    </p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="rule-card">
                    <i class="fas fa-paperclip"></i>
                    <h5 class="font-weight-bold mb-3 text-white">Pièces Justificatives</h5>
                    <p class="small opacity-90" style="line-height: 1.6;">
                        Chaque opération saisie doit impérativement être appuyée par une preuve (facture, reçu, contrat)
                        jointe au format numérique.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-5 mb-5 pb-4">
        <p class="small text-muted font-weight-bold text-uppercase" style="letter-spacing: 2px;">COMPTAFIQ ©
            {{ date('Y') }}</p>
    </div>
@endsection
