@extends('layouts.accounting')

@section('title', 'Balance Générale')

@section('styles')
    <style>
        .balance-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            overflow: hidden;
        }

        .table-balance { 
            min-width: 1100px;
            table-layout: fixed;
        }

        .table-balance thead th {
            background-color: var(--primary-color);
            color: white;
            font-family: 'Manrope';
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 1px solid rgba(255,255,255,0.1);
            padding: 8px 5px;
            vertical-align: middle;
            text-align: center;
        }

        .table-balance tbody td {
            padding: 10px 8px;
            font-size: 11px;
            border: 1px solid #f0f0f0;
            vertical-align: middle;
        }

        .row-group { background-color: #f8f9fc; font-weight: 700; }
        .row-class { background-color: var(--primary-color); color: white; font-weight: 800; }
        .row-class td { border-color: rgba(255,255,255,0.1) !important; }
        
        .amount { 
            font-family: 'Inter' !important; 
            font-weight: 700 !important; 
            white-space: nowrap !important; 
            text-align: right !important; 
            letter-spacing: -0.5px;
            padding-right: 15px !important;
            font-size: 11px !important;
        }
        
        .filter-section {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            border: 1px solid #eee;
        }
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
            <h1 class="h3 font-weight-bold mb-1" style="font-family: 'Manrope';">Balance Générale</h1>
            <p class="text-muted small mb-0 font-weight-bold">Vérification de l'équilibre arithmétique de vos comptes</p>
        </div>
        <div class="mt-3 mt-md-0 d-flex flex-wrap shadow-none">
            <a href="{{ route('accounting.balance.pdf') }}" target="_blank" class="btn btn-white shadow-sm border font-weight-bold d-flex align-items-center px-4 mr-2 mb-2" style="border-radius: 10px; font-size: 12px;">
                <span class="material-symbols-outlined mr-2 text-danger">picture_as_pdf</span> PDF
            </a>
            <button onclick="exportTableToExcel('balance-table', 'Balance_Generale')" class="btn btn-white shadow-sm border font-weight-bold d-flex align-items-center px-4 mb-2" style="border-radius: 10px; font-size: 12px;">
                <span class="material-symbols-outlined mr-2 text-success">table_view</span> EXCEL
            </button>
        </div>
    </div>

    <!-- Filtre par période -->
    <div class="filter-section shadow-sm mb-5 no-print">
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
                    <button type="submit" class="btn btn-primary shadow-sm font-weight-bold flex-grow-1 mr-2 d-flex align-items-center justify-content-center" style="border-radius: 12px; height: 45px; background-color: var(--primary-color) !important;">
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

    <div class="balance-card shadow-sm" id="balance-table">
        <div class="bg-light p-4 text-center border-bottom">
            <h4 class="font-weight-bold text-uppercase tracking-widest mb-0" style="font-family: 'Manrope'; color: var(--primary-color);">État de la Balance au {{ date('d/m/Y') }}</h4>
        </div>

        <div class="table-responsive">
            <table class="table table-balance mb-0">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 90px; min-width: 90px;">CODE</th>
                        <th rowspan="2" style="width: 220px; min-width: 220px;">LIBELLÉ</th>
                        <th colspan="2">MOUVEMENTS</th>
                        <th colspan="2">PROVISIONS / AUTRES</th>
                        <th colspan="2">SOLDES FIN</th>
                    </tr>
                    <tr>
                        <th style="width: 130px; min-width: 130px;">DÉBIT</th>
                        <th style="width: 130px; min-width: 130px;">CRÉDIT</th>
                        <th style="width: 130px; min-width: 130px;">DÉBIT</th>
                        <th style="width: 130px; min-width: 130px;">CRÉDIT</th>
                        <th style="width: 130px; min-width: 130px;">DÉBIT</th>
                        <th style="width: 130px; min-width: 130px;">CRÉDIT</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($balanceData as $classId => $class)
                        @foreach ($class['groups'] as $prefix => $group)
                            @foreach ($group['accounts'] as $acc)
                                <tr>
                                    <td class="font-weight-bold text-dark text-center">{{ $acc['code'] }}</td>
                                    <td class="text-uppercase text-muted font-weight-bold">{{ $acc['libelle'] }}</td>
                                    <td class="amount text-muted">-</td>
                                    <td class="amount text-muted">-</td>
                                    <td class="amount text-dark">{{ $acc['mouv_debit'] > 0 ? number_format($acc['mouv_debit'], 2, ',', ' ') : '0,00' }}</td>
                                    <td class="amount text-dark">{{ $acc['mouv_credit'] > 0 ? number_format($acc['mouv_credit'], 2, ',', ' ') : '0,00' }}</td>
                                    <td class="amount text-primary" style="background: rgba(0, 98, 204, 0.02);">{{ $acc['fin_debit'] > 0 ? number_format($acc['fin_debit'], 2, ',', ' ') : '0,00' }}</td>
                                    <td class="amount text-primary" style="background: rgba(0, 98, 204, 0.02);">{{ $acc['fin_credit'] > 0 ? number_format($acc['fin_credit'], 2, ',', ' ') : '0,00' }}</td>
                                </tr>
                            @endforeach

                            <tr class="row-group">
                                <td colspan="2" class="text-uppercase small px-4 py-2">Sous-Total {{ $prefix }}</td>
                                <td class="amount py-2">-</td>
                                <td class="amount py-2">-</td>
                                <td class="amount py-2">{{ number_format($group['group_totals']['mouv_debit'], 2, ',', ' ') }}</td>
                                <td class="amount py-2">{{ number_format($group['group_totals']['mouv_credit'], 2, ',', ' ') }}</td>
                                <td class="amount py-2 text-primary" style="background: rgba(0, 98, 204, 0.05);">{{ number_format($group['group_totals']['fin_debit'], 2, ',', ' ') }}</td>
                                <td class="amount py-2 text-primary" style="background: rgba(0, 98, 204, 0.05);">{{ number_format($group['group_totals']['fin_credit'], 2, ',', ' ') }}</td>
                            </tr>
                        @endforeach

                        <tr class="row-class">
                            <td colspan="2" class="text-uppercase font-weight-bold px-4 py-2">Total Classe {{ $classId }}</td>
                            <td class="amount py-2 opacity-50">-</td>
                            <td class="amount py-2 opacity-50">-</td>
                            <td class="amount py-2">{{ number_format($class['class_totals']['mouv_debit'], 2, ',', ' ') }}</td>
                            <td class="amount py-2">{{ number_format($class['class_totals']['mouv_credit'], 2, ',', ' ') }}</td>
                            <td class="amount py-2">{{ number_format($class['class_totals']['fin_debit'], 2, ',', ' ') }}</td>
                            <td class="amount py-2">{{ number_format($class['class_totals']['fin_credit'], 2, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted italic">
                                <span class="material-symbols-outlined d-block mb-2" style="font-size: 40px; opacity: 0.2;">info</span>
                                Aucune donnée disponible pour établir la balance.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if (!empty($balanceData))
                    <tfoot class="bg-dark text-white font-weight-bold">
                        <tr>
                            <td colspan="2" class="px-4 py-3 text-uppercase tracking-widest" style="font-family: 'Manrope';">Total Général</td>
                            <td class="amount py-3">-</td>
                            <td class="amount py-3">-</td>
                            <td class="amount py-3 text-warning">{{ number_format($grandTotal['mouv_debit'], 2, ',', ' ') }}</td>
                            <td class="amount py-3 text-warning">{{ number_format($grandTotal['mouv_credit'], 2, ',', ' ') }}</td>
                            <td class="amount py-3 text-warning">{{ number_format($grandTotal['fin_debit'], 2, ',', ' ') }}</td>
                            <td class="amount py-3 text-warning">{{ number_format($grandTotal['fin_credit'], 2, ',', ' ') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <script>
        const balanceDataJson = @json($balanceData);
        const grandTotalJson = @json($grandTotal);

        function exportTableToExcel(tableWrapperId, filename) {
            const sep = ';';
            const q = (v) => '"' + String(v).replace(/"/g, '""') + '"';
            let rows = [];
            rows.push([q('NUM DE COMPTES'), q('INTITULÉ DES COMPTES'), q('MOUVEMENTS DE LA PERIODE (DÉBIT)'), q('MOUVEMENTS DE LA PERIODE (CRÉDIT)'), q('SOLDES EN FIN DE PERIODE (DÉBIT)'), q('SOLDES EN FIN DE PERIODE (CRÉDIT)')].join(sep));

            for (const [classId, classData] of Object.entries(balanceDataJson)) {
                for (const [prefix, group] of Object.entries(classData.groups)) {
                    for (const acc of group.accounts) {
                        rows.push([q(acc.code), q(acc.libelle), q(acc.mouv_debit > 0 ? acc.mouv_debit.toFixed(2).replace('.', ',') : '0,00'), q(acc.mouv_credit > 0 ? acc.mouv_credit.toFixed(2).replace('.', ',') : '0,00'), q(acc.fin_debit > 0 ? acc.fin_debit.toFixed(2).replace('.', ',') : '0,00'), q(acc.fin_credit > 0 ? acc.fin_credit.toFixed(2).replace('.', ',') : '0,00')].join(sep));
                    }
                    const gt = group.group_totals;
                    rows.push([q('SOUS TOTAL ' + prefix), q(''), q(gt.mouv_debit.toFixed(2).replace('.', ',')), q(gt.mouv_credit.toFixed(2).replace('.', ',')), q(gt.fin_debit.toFixed(2).replace('.', ',')), q(gt.fin_credit.toFixed(2).replace('.', ','))].join(sep));
                }
                const ct = classData.class_totals;
                rows.push([q('TOTAL CLASSE ' + classId), q(''), q(ct.mouv_debit.toFixed(2).replace('.', ',')), q(ct.mouv_credit.toFixed(2).replace('.', ',')), q(ct.fin_debit.toFixed(2).replace('.', ',')), q(ct.fin_credit.toFixed(2).replace('.', ','))].join(sep));
                rows.push(['', '', '', '', '', ''].join(sep));
            }

            rows.push([q('TOTAL BALANCE GÉNÉRALE'), q(''), q(grandTotalJson.mouv_debit.toFixed(2).replace('.', ',')), q(grandTotalJson.mouv_credit.toFixed(2).replace('.', ',')), q(grandTotalJson.fin_debit.toFixed(2).replace('.', ',')), q(grandTotalJson.fin_credit.toFixed(2).replace('.', ','))].join(sep));

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
