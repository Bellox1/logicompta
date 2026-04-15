@extends('layouts.accounting')

@section('title', 'Grand Livre')

@section('styles')
    <style>
        .ledger-account-card {
            background: #fff;
            border-radius: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            border: 1px solid #eee;
            margin-bottom: 40px;
            overflow: hidden;
        }

        .ledger-account-header {
            background: #f8f9fc;
            padding: 25px 30px;
            border-bottom: 1px solid #eee;
        }

        .account-label {
            font-family: 'Manrope';
            font-weight: 800;
            font-size: 18px;
            text-transform: uppercase;
            color: #1e293b;
            letter-spacing: 0.5px;
        }

        .account-code {
            font-family: 'Inter';
            font-weight: 800;
            color: var(--primary-color);
            background: rgba(0, 91, 130, 0.05);
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 14px;
        }

        .table-ledger thead th {
            font-family: 'Manrope';
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            padding: 15px 30px;
            border-bottom: 2px solid #f8f9fc;
        }

        .table-ledger tbody td {
            padding: 15px 30px;
            vertical-align: middle;
            font-size: 13px;
            border-bottom: 1px solid #f8f9fc;
        }

        .ledger-date { font-weight: 700; color: #64748b; }
        .ledger-libelle { font-weight: 700; color: #1e293b; text-transform: uppercase; }
        .ledger-amount { 
            font-family: 'Inter'; 
            font-weight: 800; 
            font-size: 14px; 
            white-space: nowrap !important;
            letter-spacing: -0.5px;
        }

        .ledger-footer {
            background: #fff;
            padding: 20px 30px;
            border-top: 2px solid #f8f9fc;
        }

        .badge-piece {
            background: rgba(0, 98, 204, 0.08);
            color: var(--primary-color);
            border-radius: 8px;
            padding: 6px 12px;
            font-weight: 800;
            font-size: 11px;
            text-decoration: none !important;
            transition: 0.3s;
        }

        .badge-piece:hover {
            background: var(--primary-color);
            color: #fff;
        }

        @media print { .no-print { display: none !important; } .page-break { page-break-after: always; } }
    </style>
@endsection

@section('content')
    @if (request('show_archived') == '1' && request('start_date'))
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center justify-content-between p-3 mb-4 no-print" style="border-radius: 12px; background: rgba(0, 91, 130, 0.05);">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white rounded-lg d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px;">
                    <span class="material-symbols-outlined">archive</span>
                </div>
                <div>
                    <h5 class="mb-0 font-weight-bold uppercase" style="font-size: 14px;">Archives {{ date('Y', strtotime(request('start_date'))) }}</h5>
                    <p class="mb-0 text-muted small uppercase font-weight-bold tracking-wider">Données scellées et définitives</p>
                </div>
            </div>
            <a href="{{ route('accounting.archive.index') }}" class="btn btn-sm btn-outline-primary font-weight-bold px-3">Retour</a>
        </div>
    @endif

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 no-print">
        <div>
            <h1 class="h3 font-weight-bold mb-1 text-dark" style="font-family: 'Manrope';">Grand Livre</h1>
            <p class="text-muted small mb-0 font-weight-bold">Détail chronologique des mouvements par compte</p>
        </div>
        <div class="mt-3 mt-md-0 d-flex flex-wrap shadow-none">
            <a href="{{ route('accounting.ledger', ['mode' => 'all']) }}" class="btn {{ $mode === 'all' && !$selectedClass ? 'btn-primary shadow-sm' : 'btn-white border' }} font-weight-bold px-4 mr-2 mb-2 rounded-pill" style="font-size: 12px;">Vue complète</a>
            <div class="dropdown mb-2">
                <button class="btn {{ $selectedClass ? 'btn-primary shadow-sm' : 'btn-white border' }} font-weight-bold px-4 dropdown-toggle rounded-pill" type="button" data-toggle="dropdown" style="font-size: 12px;">
                    {{ $selectedClass ? 'Classe ' . $selectedClass : 'Par Classe' }}
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow border-0" style="border-radius: 15px;">
                    @foreach (range(1, 9) as $class)
                        <a class="dropdown-item py-2 font-weight-bold small {{ $selectedClass == $class ? 'text-primary' : '' }}" href="{{ route('accounting.ledger', ['mode' => 'class', 'class' => $class]) }}">Classe {{ $class }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Filtre par période -->
    <div class="bg-white border rounded-lg p-4 mb-4 shadow-sm no-print" style="border-radius: 20px;">
        <form action="{{ request()->url() }}" method="GET">
            <input type="hidden" name="mode" value="{{ request('mode', 'single') }}">
            <input type="hidden" name="class" value="{{ request('class') }}">
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
                        <a href="{{ request()->url(['mode' => request('mode', 'single'), 'class' => request('class')]) }}" class="btn btn-light border font-weight-bold d-flex align-items-center justify-content-center rounded-lg" style="width: 50px; height: 45px;">
                            <span class="material-symbols-outlined">close</span>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Account Selector -->
    <div class="bg-white border rounded-lg p-4 mb-5 shadow-sm no-print" style="border-radius: 20px;">
        <div class="row align-items-end">
            <div class="col-md-8 mb-3 mb-md-0">
                <label class="small font-weight-bold text-uppercase text-muted mb-2 tracking-wider">Sélection d'un compte spécifique</label>
                <select id="account_select" class="form-control font-weight-bold rounded-lg custom-select-premium" style="height: 45px;" onchange="if(this.value) window.location.href='{{ route('accounting.ledger') }}?account_id='+this.value">
                    <option value="">-- Choisir un compte --</option>
                    @foreach ($accounts as $classId => $classAccounts)
                        <optgroup label="CLASSE {{ $classId }}">
                            @foreach ($classAccounts as $acc)
                                <option value="{{ $acc->id }}" {{ $selectedAccount && $selectedAccount->id == $acc->id ? 'selected' : '' }}>{{ $acc->code_compte }} - {{ $acc->libelle }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            @if (count($data) > 0)
                <div class="col-md-4 d-flex">
                    <a href="{{ route('accounting.ledger.pdf', ['account_id' => $selectedAccount ? $selectedAccount->id : null, 'mode' => $mode, 'class' => $selectedClass]) }}" target="_blank" class="btn btn-white border shadow-sm font-weight-bold flex-grow-1 mr-2 d-flex align-items-center justify-content-center rounded-lg" style="height: 45px;">
                        <span class="material-symbols-outlined mr-2 text-danger">picture_as_pdf</span> PDF
                    </a>
                    <button onclick="exportLedgerToExcel()" class="btn btn-white border shadow-sm font-weight-bold flex-grow-1 d-flex align-items-center justify-content-center rounded-lg" style="height: 45px;">
                        <span class="material-symbols-outlined mr-2 text-success">table_view</span> EXCEL
                    </button>
                </div>
            @endif
        </div>
    </div>

    @if (count($data) > 0)
        @foreach ($data as $account)
            <div class="ledger-account-card shadow-sm page-break">
                <div class="ledger-account-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                    <div class="d-flex align-items-center mb-2 mb-md-0">
                        <span class="account-code mr-3" style="background: rgba(0, 98, 204, 0.1);">{{ str_pad($account->code_compte, 5, '0', STR_PAD_RIGHT) }}</span>
                        <h2 class="account-label mb-0">{{ $account->libelle }}</h2>
                    </div>
                    <div class="text-md-right">
                        <span class="text-uppercase small font-weight-bold text-muted" style="font-size: 9px; letter-spacing: 1px;">Consulté le</span>
                        <div class="small font-weight-bold text-dark">{{ date('d/m/Y H:i') }}</div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-ledger mb-0" data-ledger-table data-account="{{ $account->code_compte }} - {{ $account->libelle }}">
                        <thead>
                            <tr>
                                <th style="width: 140px;">DATE</th>
                                <th style="width: 120px;">N° PIÈCE</th>
                                <th>LIBELLÉ DE L'OPÉRATION</th>
                                <th class="text-right" style="width: 140px;">DÉBIT</th>
                                <th class="text-right" style="width: 140px;">CRÉDIT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $runningSolde = 0; @endphp
                            @forelse($account->entryLines as $line)
                                @php $runningSolde += ($line->debit - $line->credit); @endphp
                                <tr>
                                    <td class="ledger-date">{{ \Carbon\Carbon::parse($line->entry->date)->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('accounting.journal.show', $line->entry->id) }}" class="badge-piece">
                                            {{ str_replace('PC-', '', $line->entry->numero_piece) }}
                                        </a>
                                    </td>
                                    <td class="ledger-libelle">{{ $line->libelle }}</td>
                                    <td class="text-right ledger-amount">{{ $line->debit > 0 ? number_format($line->debit, 2, ',', ' ') : '-' }}</td>
                                    <td class="text-right ledger-amount">{{ $line->credit > 0 ? number_format($line->credit, 2, ',', ' ') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted italic">Aucun mouvement enregistré</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if (count($account->entryLines) > 0)
                            <tfoot class="bg-light-soft">
                                <tr class="font-weight-bold">
                                    <td colspan="3" class="text-right text-uppercase small text-muted p-4">Cumul de la période</td>
                                    <td class="text-right p-4 ledger-amount">{{ number_format($account->entryLines->sum('debit'), 2, ',', ' ') }}</td>
                                    <td class="text-right p-4 ledger-amount">{{ number_format($account->entryLines->sum('credit'), 2, ',', ' ') }}</td>
                                </tr>
                                <tr class="bg-white">
                                    <td colspan="3"></td>
                                    <td colspan="2" class="p-4">
                                        <div class="d-flex align-items-center justify-content-end text-nowrap">
                                            <span class="text-uppercase small font-weight-bold text-muted mr-3">SOLDE NET {{ $runningSolde >= 0 ? 'DÉBITEUR' : 'CRÉDITEUR' }} :</span>
                                            <span class="h3 mb-0 font-weight-bold {{ $runningSolde >= 0 ? 'text-success' : 'text-danger' }}" style="white-space: nowrap;">{{ number_format(abs($runningSolde), 2, ',', ' ') }} <span class="small font-weight-normal text-muted">XOF</span></span>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        @endforeach
    @else
        <div class="text-center py-5 bg-white border rounded shadow-sm" style="border-radius: 20px;">
            <span class="material-symbols-outlined mb-3" style="font-size: 60px; opacity: 0.1;">search</span>
            <h4 class="font-weight-bold text-dark">Aucun mouvement à afficher</h4>
            <p class="text-muted small px-5">Veuillez sélectionner un compte ou un mode d'affichage pour visualiser les écritures correspondantes.</p>
        </div>
    @endif

@endsection

@section('scripts')
    <script>
        function exportLedgerToExcel() {
            const sep = ';';
            const q = (v) => '"' + String(v).replace(/"/g, '""').trim() + '"';
            let csv = [];
            csv.push(['COMPTE', 'INTITULÉ', 'DATES', 'Num PC', 'LIBELLÉ DES OPERATIONS', 'DÉBIT', 'CRÉDIT'].map(h => q(h)).join(sep));

            document.querySelectorAll('[data-ledger-table]').forEach(table => {
                const accountInfo = table.getAttribute('data-account');
                const parts = accountInfo.split(' - ');
                const codeCompte = parts[0] || '';
                const libelleCompte = parts[1] || '';

                table.querySelectorAll('tbody tr').forEach(row => {
                    const cols = row.querySelectorAll('td');
                    if (cols.length < 5) return;
                    csv.push([q(codeCompte), q(libelleCompte), q(cols[0].innerText.trim()), q(cols[1].innerText.trim()), q(cols[2].innerText.trim()), q(cols[3].innerText.trim()), q(cols[4].innerText.trim())].join(sep));
                });
            });

            const csvContent = '\uFEFF' + csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.setAttribute('download', 'Grand_Livre_' + new Date().toISOString().slice(0, 10) + '.csv');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
@endsection
