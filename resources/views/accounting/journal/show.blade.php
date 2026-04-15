@extends('layouts.accounting')

@section('title', 'Pièce Comptable ' . str_replace('PC-', '', $entry->numero_piece))

@section('styles')
    <style>
        .piece-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            overflow: hidden;
        }

        .piece-header {
            background: #f8f9fc;
            padding: 25px 30px;
            border-bottom: 2px solid #eee;
        }

        .table-piece thead th {
            font-family: 'Manrope';
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            padding: 15px 20px;
            border-bottom: 2px solid #f8f9fc;
        }

        .table-piece tbody td {
            padding: 15px 20px;
            vertical-align: middle;
            font-size: 13px;
            border-bottom: 1px solid #f8f9fc;
        }

        .info-premium-box {
            background: rgba(0, 91, 130, 0.03);
            border: 1px solid rgba(0, 91, 130, 0.1);
            border-radius: 15px;
            padding: 20px;
        }
    </style>
@endsection

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 no-print">
        <div class="d-flex align-items-center">
            <a href="javascript:history.back()" class="btn btn-light rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <h1 class="h3 font-weight-bold mb-1 text-dark" style="font-family: 'Manrope';">Pièce Comptable : {{ str_replace('PC-', '', $entry->numero_piece) }}</h1>
                <p class="text-muted small mb-0 font-weight-bold italic">Journal : <span class="text-primary">{{ $entry->journal->name }}</span></p>
            </div>
        </div>
        <div class="mt-3 mt-md-0">
            <button onclick="exportTableToExcel('piece-table', 'Piece_{{ $entry->numero_piece }}')" class="btn btn-success shadow-sm font-weight-bold px-4 d-flex align-items-center rounded-pill">
                <span class="material-symbols-outlined mr-2" style="font-size: 20px;">table_view</span> EXPORTER EXCEL
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="piece-card shadow-sm" id="piece-table">
                <div class="table-responsive">
                    <table class="table table-piece mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" style="white-space: nowrap;">DATE</th>
                                <th>COMPTE</th>
                                <th>INTITULÉ / LIBÉLLÉ</th>
                                <th class="text-right" style="white-space: nowrap;">DÉBIT</th>
                                <th class="text-right" style="white-space: nowrap;">CRÉDIT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($entry->lines as $index => $line)
                                <tr>
                                    @if ($index === 0)
                                        <td rowspan="{{ $entry->lines->count() }}" class="text-center font-weight-bold text-dark border-right bg-light" style="vertical-align: middle;">
                                            {{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}
                                        </td>
                                    @endif
                                    <td class="font-weight-bold text-primary">{{ $line->sousCompte->numero_sous_compte }}</td>
                                    <td>
                                        <div class="font-weight-bold text-dark text-uppercase mb-1">{{ $line->sousCompte->libelle }}</div>
                                        <div class="text-muted small italic">{{ $line->libelle }}</div>
                                    </td>
                                    <td class="text-right font-weight-bold text-dark text-nowrap">{{ $line->debit > 0 ? number_format($line->debit, 2, ',', ' ') : '-' }}</td>
                                    <td class="text-right font-weight-bold text-dark text-nowrap">{{ $line->credit > 0 ? number_format($line->credit, 2, ',', ' ') : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light-soft font-weight-bold text-dark">
                            <tr style="font-size: 16px;">
                                <td colspan="3" class="text-right text-uppercase small text-muted p-4">Totaux Équilibrés</td>
                                <td class="text-right p-4 border-right text-nowrap">{{ number_format($entry->lines->sum('debit'), 2, ',', ' ') }}</td>
                                <td class="text-right p-4 text-nowrap">{{ number_format($entry->lines->sum('credit'), 2, ',', ' ') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="info-premium-box shadow-sm mb-4">
                <div class="d-flex align-items-center mb-3 text-primary">
                    <span class="material-symbols-outlined mr-2">info</span>
                    <h6 class="font-weight-bold text-uppercase mb-0 small">Information Système</h6>
                </div>
                <p class="small text-muted mb-0 font-weight-bold italic leading-relaxed">
                    Cette pièce comptable est immuable et scellée. Elle garantit l'intégrité de vos enregistrements et sert de fondement à vos états financiers.
                </p>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        function exportTableToExcel(tableWrapperId, filename) {
            const table = document.getElementById(tableWrapperId).querySelector('table');
            let csv = [];
            table.querySelectorAll('tr').forEach(row => {
                const rowData = [];
                row.querySelectorAll('th, td').forEach(col => {
                    let text = col.innerText.replace(/\n/g, ' ').trim();
                    rowData.push('"' + text.replace(/"/g, '""') + '"');
                });
                csv.push(rowData.join(';'));
            });
            const csvContent = '\uFEFF' + csv.join('\n');
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
