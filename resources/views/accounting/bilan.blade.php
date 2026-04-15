@extends('layouts.accounting')

@section('title', 'Bilan Patrimonial')

@section('styles')
    <style>
        .bilan-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #003d57 100%);
            color: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 91, 130, 0.1);
        }

        .bilan-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .card-title-premium {
            font-family: 'Manrope';
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 16px;
            margin-bottom: 0;
        }

        .table-bilan thead th {
            font-family: 'Manrope';
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            border-bottom: 2px solid #f8f9fc;
            padding: 15px 20px;
        }

        .table-bilan tbody td {
            padding: 15px 20px;
            vertical-align: middle;
            border-bottom: 1px solid #f8f9fc;
        }

        .rubrique-name {
            font-family: 'Inter';
            font-weight: 700;
            color: #1e293b;
            font-size: 13px;
            display: block;
        }

        .rubrique-sub {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 800;
        }

        .solde-amount {
            font-family: 'Inter';
            font-weight: 800;
            font-size: 15px;
            color: #0f172a;
            white-space: nowrap !important;
        }

        .bilan-footer {
            background: #f8f9fc;
            padding: 20px 25px;
            border-top: 1px solid #eee;
        }
    </style>
@endsection

@section('content')
    @if(request('show_archived') == '1' && request('start_date'))
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
            <h1 class="h3 font-weight-bold mb-1 text-dark" style="font-family: 'Manrope';">Bilan Patrimonial</h1>
            <p class="text-muted small mb-0 font-weight-bold">État de santé financière au {{ date('d/m/Y') }}</p>
        </div>
        <div class="mt-3 mt-md-0 d-flex flex-wrap shadow-none">
            <div class="dropdown">
                <button class="btn btn-dark font-weight-bold px-4 d-flex align-items-center rounded-pill" type="button" data-toggle="dropdown" style="font-size: 12px;">
                    <span class="material-symbols-outlined mr-2" style="font-size: 20px;">download</span> OPTIONS D'EXPORT
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow border-0" style="border-radius: 15px;">
                    <a class="dropdown-item py-3 font-weight-bold small" href="{{ route('accounting.bilan.pdf') }}" target="_blank">
                        <i class="fas fa-file-pdf text-danger mr-2"></i> Télécharger en PDF
                    </a>
                    <button class="dropdown-item py-3 font-weight-bold small" onclick="exportBilanComplete()">
                        <i class="fas fa-file-csv text-success mr-2"></i> Exporter en CSV
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

    <div class="row">
        <!-- ACTIF -->
        <div class="col-xl-6 mb-4">
            <div class="bilan-card shadow-sm">
                <div class="bg-white p-4 d-flex align-items-center justify-content-between border-bottom">
                    <div class="d-flex align-items-center text-success">
                        <span class="material-symbols-outlined mr-2" style="font-size: 32px;">trending_up</span>
                        <h5 class="card-title-premium mb-0">ACTIF</h5>
                    </div>
                    <div class="text-right">
                        <span class="text-uppercase small font-weight-bold text-muted" style="font-size: 9px; letter-spacing: 1px;">Total Actif</span>
                        <div class="h4 mb-0 font-weight-bold text-dark text-nowrap">{{ number_format($actif->sum('solde'), 2, ',', ' ') }} F</div>
                    </div>
                </div>
                <div class="table-responsive flex-grow-1" id="bilan-actif">
                    <table class="table table-bilan mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>RUBRIQUE</th>
                                <th class="text-right">MONTANT NET</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($actif as $item)
                                <tr>
                                    <td>
                                        <span class="rubrique-name text-uppercase">{{ $item['libelle'] }}</span>
                                        <span class="rubrique-sub">Emploi / Ressource durable</span>
                                    </td>
                                    <td class="text-right">
                                        <span class="solde-amount">{{ number_format($item['solde'], 2, ',', ' ') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center py-5 text-muted small font-weight-bold italic">Aucun mouvement détecté</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="bilan-footer d-flex justify-content-between align-items-center">
                    <span class="small font-weight-bold text-muted text-uppercase">Total Bilan (Actif)</span>
                    <span class="h4 font-weight-bold mb-0 text-nowrap" style="color: var(--primary-color);">{{ number_format($actif->sum('solde'), 2, ',', ' ') }} F</span>
                </div>
            </div>
        </div>

        <!-- PASSIF -->
        <div class="col-xl-6 mb-4">
            <div class="bilan-card shadow-sm">
                <div class="bg-white p-4 d-flex align-items-center justify-content-between border-bottom">
                    <div class="d-flex align-items-center text-primary">
                        <span class="material-symbols-outlined mr-2" style="font-size: 32px;">account_balance_wallet</span>
                        <h5 class="card-title-premium mb-0">PASSIF</h5>
                    </div>
                    <div class="text-right">
                        <span class="text-uppercase small font-weight-bold text-muted" style="font-size: 9px; letter-spacing: 1px;">Total Passif</span>
                        <div class="h4 mb-0 font-weight-bold text-dark text-nowrap">{{ number_format($passif->sum('solde'), 2, ',', ' ') }} F</div>
                    </div>
                </div>
                <div class="table-responsive flex-grow-1" id="bilan-passif">
                    <table class="table table-bilan mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>RUBRIQUE</th>
                                <th class="text-right">MONTANT NET</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($passif as $item)
                                <tr>
                                    <td>
                                        <span class="rubrique-name text-uppercase">{{ $item['libelle'] }}</span>
                                        <span class="rubrique-sub">Fonds propres / Dettes</span>
                                    </td>
                                    <td class="text-right">
                                        <span class="solde-amount">{{ number_format($item['solde'], 2, ',', ' ') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center py-5 text-muted small font-weight-bold italic">Aucun mouvement détecté</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="bilan-footer d-flex justify-content-between align-items-center">
                    <span class="small font-weight-bold text-muted text-uppercase">Total Bilan (Passif)</span>
                    <span class="h4 font-weight-bold mb-0 text-nowrap" style="color: var(--primary-color);">{{ number_format($passif->sum('solde'), 2, ',', ' ') }} F</span>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
function exportBilanComplete() {
    const sep = ';';
    const q = (v) => '"' + String(v).replace(/"/g, '""').trim() + '"';
    let csv = [];
    csv.push([q('TYPE'), q('RUBRIQUE'), q('MONTANT NET')].join(sep));
    
    document.querySelectorAll('#bilan-actif table tbody tr').forEach(row => {
        const cols = row.querySelectorAll('td');
        if (cols.length < 2) return;
        csv.push([q('ACTIF'), q(cols[0].innerText.trim()), q(cols[1].innerText.trim())].join(sep));
    });
    csv.push([q('SOUS-TOTAL'), q('TOTAL ACTIF'), q('{{ number_format($actif->sum('solde'), 2, ',', ' ') }}')].join(sep));
    csv.push(['', '', ''].join(sep));
    
    document.querySelectorAll('#bilan-passif table tbody tr').forEach(row => {
        const cols = row.querySelectorAll('td');
        if (cols.length < 2) return;
        csv.push([q('PASSIF'), q(cols[0].innerText.trim()), q(cols[1].innerText.trim())].join(sep));
    });
    csv.push([q('SOUS-TOTAL'), q('TOTAL PASSIF'), q('{{ number_format($passif->sum('solde'), 2, ',', ' ') }}')].join(sep));

    const csvContent = '\uFEFF' + csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.setAttribute('download', 'Bilan_Patrimonial_' + new Date().toISOString().slice(0, 10) + '.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endsection
