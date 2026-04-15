@extends('layouts.accounting')

@section('title', 'Compte de Résultat')

@section('styles')
    <style>
        .resultat-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .header-charges { background: linear-gradient(135deg, #e11d48 0%, #9f1239 100%); color: white; }
        .header-produits { background: linear-gradient(135deg, #059669 0%, #065f46 100%); color: white; }

        .table-resultat thead th {
            font-family: 'Manrope';
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            border-bottom: 2px solid #f8f9fc;
            padding: 15px 20px;
        }

        .table-resultat tbody td {
            padding: 15px 20px;
            vertical-align: middle;
            border-bottom: 1px solid #f8f9fc;
        }

        .amount-val {
            font-family: 'Inter';
            font-weight: 800;
            font-size: 14px;
            white-space: nowrap !important;
        }

        .final-result-box {
            background: #fff;
            border-radius: 30px;
            padding: 50px;
            border: 2px solid #eee;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .final-result-box:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .profit-text { color: #059669; }
        .loss-text { color: #e11d48; }

        @media print { .no-print { display: none !important; } }
    </style>
@endsection

@section('content')
    @if (request('show_archived') == '1' && request('start_date'))
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center justify-content-between p-3 mb-4" style="border-radius: 12px; background: rgba(0, 91, 130, 0.05);">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white rounded-lg d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px;">
                    <span class="material-symbols-outlined">archive</span>
                </div>
                <div>
                    <h5 class="mb-0 font-weight-bold uppercase" style="font-size: 14px;">Archives {{ date('Y', strtotime(request('start_date'))) }}</h5>
                    <p class="mb-0 text-muted small uppercase font-weight-bold tracking-wider">Données scellées et définitives</p>
                </div>
            </div>
            <a href="{{ route('accounting.archive.show', date('Y', strtotime(request('start_date')))) }}" class="btn btn-sm btn-outline-primary font-weight-bold px-3">Retour à l'année {{ date('Y', strtotime(request('start_date'))) }}</a>
        </div>
    @endif

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 no-print">
        <div>
            <h1 class="h3 font-weight-bold mb-1 text-dark" style="font-family: 'Manrope';">Compte de Résultat</h1>
            <p class="text-muted small mb-0 font-weight-bold">Analyse de la performance au {{ date('d/m/Y') }}</p>
        </div>
        <div class="mt-3 mt-md-0">
            <div class="dropdown">
                <button class="btn btn-dark font-weight-bold px-4 d-flex align-items-center rounded-pill" type="button" data-toggle="dropdown" style="font-size: 12px;">
                    <span class="material-symbols-outlined mr-2" style="font-size: 20px;">download</span> OPTIONS D'EXPORT
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow border-0" style="border-radius: 15px;">
                    <a class="dropdown-item py-3 font-weight-bold small" href="{{ route('accounting.resultat.pdf') }}" target="_blank">
                        <i class="fas fa-file-pdf text-danger mr-2"></i> PDF Complet
                    </a>
                    <button class="dropdown-item py-3 font-weight-bold small" onclick="exportResultatToExcel('charges', 'Compte_Resultat_Charges')">
                        <i class="fas fa-file-csv text-rose mr-2"></i> Export Charges (CSV)
                    </button>
                    <button class="dropdown-item py-3 font-weight-bold small" onclick="exportResultatToExcel('produits', 'Compte_Resultat_Produits')">
                        <i class="fas fa-file-csv text-success mr-2"></i> Export Produits (CSV)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtre par période -->
    <div class="bg-white border rounded-lg p-4 mb-5 shadow-sm no-print" style="border-radius: 20px;">
        <form action="{{ request()->url() }}" method="GET">
            <div class="row align-items-end">
                <div class="col-md-4 mb-3 mb-md-0">
                    <label class="small font-weight-bold text-uppercase text-muted mb-2 tracking-wider">Période du</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control font-weight-bold rounded-lg" style="height: 45px;">
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <label class="small font-weight-bold text-uppercase text-muted mb-2 tracking-wider">Au</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control font-weight-bold rounded-lg" style="height: 45px;">
                </div>
                <div class="col-md-4 d-flex">
                    <button type="submit" class="btn btn-primary shadow-sm font-weight-bold flex-grow-1 mr-2 d-flex align-items-center justify-content-center rounded-lg" style="height: 45px;">
                        <span class="material-symbols-outlined mr-2" style="font-size: 20px;">sync</span> Actualiser
                    </button>
                    @if (request()->hasAny(['start_date', 'end_date']))
                        <a href="{{ request()->url() }}" class="btn btn-light border font-weight-bold d-flex align-items-center justify-content-center rounded-lg" style="width: 50px; height: 45px;">
                            <span class="material-symbols-outlined">close</span>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <div class="row mb-5">
        <!-- CHARGES -->
        <div class="col-lg-6 mb-4">
            <div class="resultat-card shadow-sm">
                <div class="header-charges p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0 font-weight-bold text-uppercase tracking-widest" style="font-size: 15px;">CHARGES</h5>
                        <span class="small opacity-80 font-weight-bold text-uppercase" style="font-size: 9px;">Nature des dépenses</span>
                    </div>
                    <span class="material-symbols-outlined" style="font-size: 30px;">trending_down</span>
                </div>
                <div class="table-responsive flex-grow-1" id="resultat-charges">
                    <table class="table table-resultat mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>COMPTE</th>
                                <th>INTITULÉ</th>
                                <th class="text-right">MONTANT</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($charges['groups'] as $prefix => $group)
                                @foreach ($group['accounts'] as $acc)
                                    <tr>
                                        <td class="font-weight-bold text-dark">{{ $acc['code'] }}</td>
                                        <td class="text-uppercase text-muted font-weight-bold">{{ $acc['libelle'] }}</td>
                                        <td class="text-right amount-val">{{ number_format($acc['montant'], 2, ',', ' ') }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-light-soft font-weight-bold">
                                    <td colspan="2" class="text-uppercase small italic text-muted">Sous-Total {{ $group['prefix'] }}</td>
                                    <td class="text-right amount-val">{{ number_format($group['total'], 2, ',', ' ') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-5 text-muted">Aunuce charge trouvée</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 header-charges d-flex justify-content-between align-items-center">
                    <span class="small font-weight-bold text-uppercase">Total Charges (VI)</span>
                    <span class="h4 font-weight-bold mb-0 text-nowrap">{{ number_format($totalCharges, 2, ',', ' ') }} F</span>
                </div>
            </div>
        </div>

        <!-- PRODUITS -->
        <div class="col-lg-6 mb-4">
            <div class="resultat-card shadow-sm">
                <div class="header-produits p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0 font-weight-bold text-uppercase tracking-widest" style="font-size: 15px;">PRODUITS</h5>
                        <span class="small opacity-80 font-weight-bold text-uppercase" style="font-size: 9px;">Nature des revenus</span>
                    </div>
                    <span class="material-symbols-outlined" style="font-size: 30px;">trending_up</span>
                </div>
                <div class="table-responsive flex-grow-1" id="resultat-produits">
                    <table class="table table-resultat mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>COMPTE</th>
                                <th>INTITULÉ</th>
                                <th class="text-right">MONTANT</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($produits['groups'] as $prefix => $group)
                                @foreach ($group['accounts'] as $acc)
                                    <tr>
                                        <td class="font-weight-bold text-dark">{{ $acc['code'] }}</td>
                                        <td class="text-uppercase text-muted font-weight-bold">{{ $acc['libelle'] }}</td>
                                        <td class="text-right amount-val">{{ number_format($acc['montant'], 2, ',', ' ') }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-light-soft font-weight-bold">
                                    <td colspan="2" class="text-uppercase small italic text-muted">Sous-Total {{ $group['prefix'] }}</td>
                                    <td class="text-right amount-val">{{ number_format($group['total'], 2, ',', ' ') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-5 text-muted">Aucun produit trouvé</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 header-produits d-flex justify-content-between align-items-center">
                    <span class="small font-weight-bold text-uppercase">Total Produits (VII)</span>
                    <span class="h4 font-weight-bold mb-0 text-nowrap">{{ number_format($totalProduits, 2, ',', ' ') }} F</span>
                </div>
            </div>
        </div>
    </div>

    <!-- RÉSULTAT FINAL -->
    <div class="final-result-box text-center shadow-sm">
        <h5 class="text-uppercase font-weight-bold text-muted mb-4 tracking-widest" style="font-size: 11px;">RÉSULTAT NET DE L'EXERCICE</h5>
        <div class="display-3 font-weight-bold mb-4 text-nowrap {{ $profit >= 0 ? 'profit-text' : 'loss-text' }}" style="font-family: 'Inter';">
            {{ number_format(abs($profit), 2, ',', ' ') }} <span class="h4 font-weight-normal opacity-50">FCFA</span>
        </div>
        <div class="d-flex justify-content-center">
            @if ($profit >= 0)
                <div class="badge badge-success px-4 py-3 rounded-pill d-flex align-items-center shadow-sm">
                    <span class="material-symbols-outlined mr-2">sentiment_very_satisfied</span> BÉNÉFICE RÉALISÉ
                </div>
            @else
                <div class="badge badge-danger px-4 py-3 rounded-pill d-flex align-items-center shadow-sm">
                    <span class="material-symbols-outlined mr-2">sentiment_very_dissatisfied</span> DÉFICIT CONSTATÉ
                </div>
            @endif
        </div>
        <p class="mt-4 text-muted small font-weight-bold italic px-md-5">
            {{ $profit >= 0 ? 'Excellente performance ! Les revenus générés assurent la pérennité de l\'entreprise.' : 'Une analyse des charges s\'impose pour rétablir l\'équilibre financier.' }}
        </p>
    </div>

@endsection

@section('scripts')
    <script>
        const chargesJson = @json($charges);
        const produitsJson = @json($produits);

        function exportResultatToExcel(dataset, filename) {
            const sep = ';';
            const q = (v) => '"' + String(v ?? '').replace(/"/g, '""') + '"';
            const fmt = (n) => parseFloat(n).toFixed(2).replace('.', ',');
            let rows = [];
            rows.push([q('COMPTE'), q('INTITULÉ'), q('MONTANT')].join(sep));

            const data = dataset === 'charges' ? chargesJson : produitsJson;
            for (const [prefix, group] of Object.entries(data.groups)) {
                for (const acc of group.accounts) { rows.push([q(acc.code), q(acc.libelle), q(fmt(acc.montant))].join(sep)); }
                rows.push([q('Sous Total ' + group.prefix), q(''), q(fmt(group.total))].join(sep));
                rows.push(['', '', ''].join(sep));
            }
            const total = dataset === 'charges' ? chargesJson.total : produitsJson.total;
            rows.push([q('TOTAL'), q(''), q(fmt(total))].join(sep));

            const csvContent = '\uFEFF' + rows.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.setAttribute('download', filename + '_' + new Date().toISOString().slice(0, 10) + '.csv');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
@endsection
