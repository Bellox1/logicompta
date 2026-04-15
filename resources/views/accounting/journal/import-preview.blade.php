@extends('layouts.accounting')

@section('title', 'Validation de l\'import Journal')

@section('styles')
    <style>
        .preview-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #eee;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .preview-table thead th {
            background: #f8fafc;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            padding: 15px 20px;
            border-bottom: 2px solid #f1f5f9;
        }

        .preview-table tbody td {
            padding: 10px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .input-preview {
            background: transparent;
            border: 1px solid transparent;
            border-radius: 8px;
            padding: 5px 10px;
            font-weight: 600;
            font-size: 13px;
            color: #1e293b;
            width: 100%;
            transition: all 0.2s;
        }

        .input-preview:focus {
            background: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 98, 204, 0.1);
            outline: none;
        }

        .input-error {
            background: #fff1f2;
            border-color: #fca5a5;
            color: #be123c;
        }

        .sticky-summary {
            position: fixed !important;
            bottom: 40px !important;
            right: 40px !important;
            left: unset !important;
            width: 360px !important;
            background: #1a1c2e !important;
            color: white !important;
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
            z-index: 9999 !important;
            border-right: 8px solid var(--primary-color) !important;
            animation: slideInRight 0.4s ease-out;
            margin: 0 !important;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .btn-confirm-import {
            background: var(--primary-color);
            border: none;
            color: white;
            padding: 14px 30px;
            border-radius: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 11px;
            width: auto;
            min-width: 200px;
            transition: all 0.3s;
            float: right;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-confirm-import:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 98, 204, 0.3);
            color: white;
        }

        .error-tooltip {
            position: absolute;
            background: #be123c;
            color: white;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 700;
            top: -25px;
            left: 0;
            white-space: nowrap;
            display: none;
            z-index: 10;
        }

        .input-wrapper:hover .error-tooltip {
            display: block;
        }
    </style>
@endsection

@section('content')
    <div class="mb-5 d-flex flex-column align-items-end text-right">
        <h1 class="h3 font-weight-bold text-dark mb-1" style="font-family: 'Manrope';">Validation de l'import Journal</h1>
        <p class="text-muted small font-weight-bold text-uppercase tracking-wider">Vérifiez et corrigez les données avant
            l'intégration finale</p>
        <div class="mt-2">
            <a href="{{ route('accounting.journal.import') }}"
                class="btn btn-outline-secondary btn-sm rounded-pill px-4 font-weight-bold">
                <i class="fas fa-arrow-left mr-2"></i> Changer de fichier
            </a>
        </div>
    </div>

    <form action="{{ route('accounting.journal.import.process') }}" method="POST">
        @csrf
        <div class="preview-card">
            <div class="table-responsive">
                <table class="table preview-table mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">Ligne</th>
                            <th style="width: 120px;">N° Pièce</th>
                            <th class="text-center" style="width: 150px;">Date</th>
                            <th style="width: 140px;">Compte</th>
                            <th>Libellé de l'opération</th>
                            <th class="text-right" style="width: 130px;">Débit</th>
                            <th class="text-right" style="width: 130px;">Crédit</th>
                            <th class="text-center" style="width: 60px;">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($previewData as $index => $row)
                            @php $isError = $row['status'] !== 'ok'; @endphp
                            <tr class="{{ $isError ? 'bg-light-danger' : '' }}">
                                <td class="text-center small font-weight-bold text-muted">
                                    {{ $row['line'] }}
                                    <input type="hidden" name="rows[{{ $index }}][line]"
                                        value="{{ $row['line'] }}">
                                </td>
                                <td>
                                    <input type="text" name="rows[{{ $index }}][piece]"
                                        value="{{ $row['piece'] }}"
                                        class="input-preview text-uppercase tracking-wider font-weight-bold">
                                </td>
                                <td>
                                    <input type="date" name="rows[{{ $index }}][date]" value="{{ $row['date'] }}"
                                        class="input-preview text-center text-muted">
                                </td>
                                <td>
                                    <div class="input-wrapper position-relative">
                                        <input type="text" name="rows[{{ $index }}][account]"
                                            value="{{ $row['account'] }}"
                                            class="input-preview font-weight-bold {{ $isError ? 'input-error' : '' }}">
                                        @if ($isError)
                                            <span class="error-tooltip">
                                                {{ $row['status'] == 'error_main' ? 'Compte général interdit' : 'Compte introuvable' }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="rows[{{ $index }}][label]"
                                        value="{{ $row['label'] }}" class="input-preview text-muted">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="rows[{{ $index }}][debit]"
                                        value="{{ $row['debit'] }}"
                                        class="input-preview text-right font-weight-bold text-success"
                                        oninput="calculateTotals()">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="rows[{{ $index }}][credit]"
                                        value="{{ $row['credit'] }}"
                                        class="input-preview text-right font-weight-bold text-danger"
                                        oninput="calculateTotals()">
                                </td>
                                <td class="text-center">
                                    @if ($row['status'] == 'ok')
                                        <i class="fas fa-check-circle text-success" style="font-size: 18px;"></i>
                                    @else
                                        <i class="fas fa-exclamation-triangle text-danger" title="Données invalides"
                                            style="font-size: 18px;"></i>
                                    @endif
                                    <input type="hidden" name="rows[{{ $index }}][journal]"
                                        value="{{ $row['journal'] }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="sticky-summary">
            <div class="summary-item flex-column align-items-end text-right">
                <span class="small font-weight-bold text-uppercase opacity-50 mb-1">Total Débit</span>
                <span id="global-debit" class="h5 mb-0 font-weight-bold text-success">0,00</span>
            </div>
            <div class="summary-item flex-column align-items-end text-right">
                <span class="small font-weight-bold text-uppercase opacity-50 mb-1">Total Crédit</span>
                <span id="global-credit" class="h5 mb-0 font-weight-bold text-danger">0,00</span>
            </div>
            <div class="summary-item flex-column align-items-end text-right border-0 mb-4">

                <span id="global-balance" class="h4 mb-0 font-weight-bold"></span>
            </div>
            <div class="text-right">
                <button type="submit" class="btn-confirm-import">
                    Confirmer l'importation <i class="fas fa-arrow-right ml-2" style="font-size: 11px;"></i>
                </button>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        function calculateTotals() {
            let totalDebit = 0;
            let totalCredit = 0;

            document.querySelectorAll('input[name$="[debit]"]').forEach(input => {
                totalDebit += parseFloat(input.value || 0);
            });
            document.querySelectorAll('input[name$="[credit]"]').forEach(input => {
                totalCredit += parseFloat(input.value || 0);
            });

            const diff = totalDebit - totalCredit;

            document.getElementById('global-debit').innerText = totalDebit.toLocaleString('fr-FR', {
                minimumFractionDigits: 2
            }) + ' FCFA';
            document.getElementById('global-credit').innerText = totalCredit.toLocaleString('fr-FR', {
                minimumFractionDigits: 2
            }) + ' FCFA';

            const balanceEl = document.getElementById('global-balance');
            const balanceStatus = document.getElementById('balance-status');

            if (Math.abs(diff) < 0.01) {
                balanceEl.innerHTML =
                    '<span class="badge badge-success px-3 py-2 rounded-pill"><i class="fas fa-check-circle mr-1"></i> Équilibré</span>';
                balanceStatus.style.display = 'none';
            } else {
                balanceEl.innerText = diff.toLocaleString('fr-FR', {
                    minimumFractionDigits: 2
                }) + ' FCFA';
                balanceEl.style.color = '#f43f5e';
                balanceStatus.style.display = 'flex';
            }
        }

        function removeRow(index) {
            if (confirm('Supprimer cette ligne ?')) {
                const rows = document.querySelectorAll('tr[id^="row-"]');
                const row = document.getElementById('row-' + index);
                if (row) row.remove();
                calculateTotals();
            }
        }

        calculateTotals();
    </script>
@endsection
