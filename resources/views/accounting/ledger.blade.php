@extends('layouts.accounting')

@section('title', 'Grand Livre')

@section('content')
@if(request('show_archived') == '1' && request('start_date'))
    <div class="mb-6 bg-primary/10 border-l-4 border-primary p-4 flex items-center justify-between shadow-sm animate-fade-in no-print">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary text-white flex items-center justify-center rounded-xl">
                <i data-lucide="archive" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-lg font-black text-text-main uppercase leading-none">Archives de l'exercice {{ date('Y', strtotime(request('start_date'))) }}</h3>
                <p class="text-xs text-text-secondary font-black uppercase tracking-widest mt-1">Données scellées et définitives</p>
            </div>
        </div>
        <a href="{{ route('accounting.archive.index') }}" class="text-[10px] font-black uppercase text-primary bg-white border border-primary px-3 py-1.5 rounded-lg hover:bg-primary hover:text-white transition-all">
            Retour au Hub
        </a>
    </div>
@endif

    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6 no-print">
        <div>
            <h1 class="text-3xl font-black text-text-main uppercase tracking-tight">Grand Livre</h1>
            <p class="text-sm text-text-secondary mt-1 font-bold">Détail chronologique des mouvements par compte</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('accounting.ledger', ['mode' => 'all']) }}"
                class="px-5 py-2.5 {{ $mode === 'all' && !$selectedClass ? 'bg-primary text-white' : 'bg-white text-slate-600' }} border border-slate-200 rounded-lg text-xs font-semibold transition-all shadow-sm">
                Vue complète
            </a>
            
            <div class="relative">
                <button id="class-dropdown-btn"
                    class="px-5 py-2.5 {{ $selectedClass ? 'bg-primary text-white' : 'bg-white text-slate-600' }} border border-slate-200 rounded-lg text-xs font-semibold transition-all shadow-sm flex items-center gap-2">
                    {{ $selectedClass ? 'Classe ' . $selectedClass : 'Par Classe' }}
                    <i data-lucide="chevron-down" class="w-3 h-3"></i>
                </button>
                <div id="class-dropdown-menu"
                    class="absolute right-0 mt-2 w-48 bg-card-bg border border-border rounded-xl shadow-xl z-[2001] hidden text-[11px] overflow-hidden">
                    @foreach (range(1, 9) as $class)
                        <a href="{{ route('accounting.ledger', ['mode' => 'class', 'class' => $class]) }}"
                            class="flex items-center justify-between px-4 py-3 font-black text-text-secondary hover:bg-white/50 transition-colors border-b border-border last:border-0 uppercase">
                            Classe {{ $class }}
                            @if ($selectedClass == $class)
                                <i data-lucide="check" class="w-3 h-3 text-primary"></i>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <form action="{{ request()->url() }}" method="GET"
        class="mb-10 grid grid-cols-1 md:flex md:flex-row md:items-end gap-5 bg-white border border-slate-200 p-6 rounded-xl shadow-sm no-print">
        <input type="hidden" name="mode" value="{{ request('mode', 'single') }}">
        <input type="hidden" name="class" value="{{ request('class') }}">
        <div class="w-full md:flex-1">
            <label class="block text-[11px] font-black text-text-secondary mb-2 uppercase tracking-wider px-1">Période du</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" placeholder="JJ/MM/AAAA"
                class="w-full bg-slate-50 border border-slate-200 px-4 py-3 text-sm font-semibold outline-none focus:border-primary transition-all rounded-lg">
        </div>
        <div class="w-full md:flex-1">
            <label class="block text-[11px] font-black text-text-secondary mb-2 uppercase tracking-wider px-1">Au</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" placeholder="JJ/MM/AAAA"
                class="w-full bg-slate-50 border border-slate-200 px-4 py-3 text-sm font-semibold outline-none focus:border-primary transition-all rounded-lg">
        </div>
        <div class="flex items-center gap-3">
            <button type="submit" class="px-8 py-3 bg-primary text-white text-xs font-bold uppercase tracking-widest hover:opacity-90 transition-all shadow-sm rounded-lg flex items-center gap-2">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i> Actualiser
            </button>
            @if (request()->hasAny(['start_date', 'end_date']))
                <a href="{{ request()->url() . (request('mode') ? '?mode=' . request('mode') : '') . (request('class') ? '&class=' . request('class') : '') }}"
                    class="px-6 py-3 bg-slate-100 text-slate-500 text-xs font-bold uppercase tracking-widest hover:bg-slate-200 transition-all rounded-lg flex items-center gap-2 border border-slate-200">
                    <i data-lucide="x" class="w-4 h-4"></i> Effacer
                </a>
            @endif
        </div>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('class-dropdown-btn');
            const menu = document.getElementById('class-dropdown-menu');

            if (btn && menu) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    menu.classList.toggle('hidden');
                });

                document.addEventListener('click', function(e) {
                    if (!menu.contains(e.target) && !btn.contains(e.target)) {
                        menu.classList.add('hidden');
                    }
                });
            }
        });
    </script>

    <!-- Account Selector -->
    <div class="bg-card-bg border border-border rounded-3xl p-6 shadow-sm mb-8 no-print">
        <div class="flex flex-col md:flex-row md:items-end gap-6">
            <div class="flex-1">
                <label for="account_select" class="block text-[11px] font-black text-text-secondary mb-2 uppercase tracking-wider px-1">Filtrer par compte</label>
                <select id="account_select"
                    class="w-full bg-bg border border-border rounded-xl px-4 py-3 focus:border-primary outline-none transition-all text-sm font-black appearance-none cursor-pointer">
                    <option value="">-- Sélectionner un compte spécifique --</option>
                    @foreach ($accounts as $classId => $classAccounts)
                        <optgroup label="CLASSE {{ $classId }}">
                            @foreach ($classAccounts as $acc)
                                <option value="{{ $acc->id }}"
                                    {{ $selectedAccount && $selectedAccount->id == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->code_compte }} - {{ $acc->libelle }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            @if (count($data) > 0)
                <div class="flex items-center gap-3">
                    <a href="{{ route('accounting.ledger.pdf', ['account_id' => $selectedAccount ? $selectedAccount->id : null, 'mode' => $mode, 'class' => $selectedClass]) }}"
                        target="_blank"
                        class="px-5 py-2.5 bg-white border border-slate-200 text-rose-500 font-semibold rounded-lg hover:bg-slate-50 transition-all text-xs flex items-center gap-2 shadow-sm">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        PDF
                    </a>
                    <button onclick="exportLedgerToExcel()"
                        class="px-5 py-2.5 bg-white border border-slate-200 text-emerald-600 font-semibold rounded-lg hover:bg-slate-50 transition-all text-xs flex items-center gap-2 shadow-sm">
                        <i data-lucide="sheet" class="w-4 h-4"></i>
                        Excel
                    </button>
                </div>
            @endif
        </div>
    </div>

    @if (count($data) > 0)
        @foreach ($data as $account)
            <div class="bg-card-bg border border-border rounded-[2.5rem] shadow-sm mb-12 overflow-hidden page-break-after">
                <!-- Informations Compte -->
                <div class="grid grid-cols-1 md:grid-cols-3 bg-white/50 border-b border-border p-8 gap-6">
                    <div>
                        <span class="text-[10px] uppercase font-black text-text-secondary tracking-widest block mb-1">Intitulé du compte</span>
                        <span class="text-base font-black text-text-main uppercase">{{ $account->libelle }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-black text-text-secondary tracking-widest block mb-1">Numéro</span>
                        <span class="text-base font-black text-primary tracking-tighter">
                            {{ str_pad($account->code_compte, 9, '0', STR_PAD_RIGHT) }}
                        </span>
                    </div>
                    <div class="md:text-right">
                        <span class="text-[10px] uppercase font-black text-text-secondary tracking-widest block mb-1">Dernière consultation</span>
                        <span class="text-xs font-black text-text-secondary">{{ date('d/m/Y H:i') }}</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="w-full border-collapse min-w-[800px] sticky-thead" data-ledger-table
                        data-account="{{ $account->code_compte }} - {{ $account->libelle }}">
                        <thead>
                            <tr class="text-text-secondary border-b border-border font-black">
                                <th class="px-6 py-5 text-[10px] uppercase tracking-widest text-left" style="width: 120px;">Date</th>
                                <th class="px-6 py-5 text-[10px] uppercase tracking-widest text-left" style="width: 100px;">Journal</th>
                                <th class="px-6 py-5 text-[10px] uppercase tracking-widest text-left">Libellé de l'opération</th>
                                <th class="px-6 py-5 text-[10px] uppercase tracking-widest text-right" style="width: 140px;">Débit</th>
                                <th class="px-6 py-5 text-[10px] uppercase tracking-widest text-right" style="width: 140px;">Crédit</th>
                            </tr>
                        </thead>
                        <tbody class="text-[13px]">
                            @php $runningSolde = 0; @endphp
                            @forelse($account->entryLines as $line)
                                @php $runningSolde += ($line->debit - $line->credit); @endphp
                                <tr class="border-b border-border hover:bg-slate-50 transition-colors group">
                                    <td class="px-6 py-4 text-text-secondary font-black">
                                        {{ \Carbon\Carbon::parse($line->entry->date)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 font-black">
                                        <a href="{{ route('accounting.journal.show', $line->entry->id) }}"
                                            class="text-primary hover:text-primary-light flex items-center gap-1">
                                            {{ str_replace('PC-', '', $line->entry->numero_piece) }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-text-secondary font-black uppercase">
                                        {{ $line->libelle }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-text-main whitespace-nowrap">
                                        {{ $line->debit > 0 ? number_format($line->debit, 2, ',', ' ') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-text-main whitespace-nowrap">
                                        {{ $line->credit > 0 ? number_format($line->credit, 2, ',', ' ') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center text-slate-400">
                                        <i data-lucide="info" class="w-10 h-10 mx-auto mb-3 opacity-20"></i>
                                        <p class="font-medium">Aucun mouvement enregistré</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if (count($account->entryLines) > 0)
                            <tfoot class="bg-white/50 border-t border-border">
                                <tr>
                                    <td colspan="3" class="px-6 py-6 text-right text-[10px] font-black text-text-secondary uppercase tracking-widest">
                                        Total cumulé
                                    </td>
                                    <td class="px-6 py-6 text-right font-black text-text-main whitespace-nowrap">
                                        {{ number_format($account->entryLines->sum('debit'), 2, ',', ' ') }}
                                    </td>
                                    <td class="px-6 py-6 text-right font-black text-text-main whitespace-nowrap">
                                        {{ number_format($account->entryLines->sum('credit'), 2, ',', ' ') }}
                                    </td>
                                </tr>
                                <tr class="bg-card-bg">
                                    <td colspan="3"></td>
                                    <td colspan="2" class="px-6 py-10 text-right">
                                        <div class="flex items-center justify-end gap-6 whitespace-nowrap">
                                            <span class="text-[10px] font-black text-text-secondary uppercase tracking-[0.2em]">Solde Net {{ $runningSolde >= 0 ? 'Débité' : 'Crédité' }} :</span>
                                            <span class="text-3xl font-black {{ $runningSolde >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                                {{ number_format(abs($runningSolde), 2, ',', ' ') }} <span class="text-xs uppercase font-normal not-italic opacity-40 text-text-main">FCFA</span>
                                            </span>
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
        <div class="bg-white border-2 border-dashed border-slate-200 rounded-xl p-20 text-center">
            <div class="bg-slate-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="search" class="w-10 h-10 text-slate-300"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Aucun mouvement à afficher</h3>
            <p class="text-slate-500 max-w-sm mx-auto leading-relaxed">
                Veuillez sélectionner un compte ou un mode d'affichage pour visualiser les écritures correspondantes.
            </p>
        </div>
    @endif

    <style>
        @media print {
            .page-break-after {
                page-break-after: always;
            }

            nav,
            header,
            footer,
            button,
            .no-print {
                display: none !important;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        function exportLedgerToExcel() {
            const sep = ';';
            const q = (v) => '"' + String(v).replace(/"/g, '""').trim() + '"';
            let csv = [];

            // Headers - Unifié avec le style "Journal" qui plaît à l'utilisateur
            csv.push(['COMPTE', 'INTITULÉ', 'DATES', 'Num PC', 'LIBELLÉ DES OPERATIONS', 'DÉBIT', 'CRÉDIT'].map(h => q(h))
                .join(sep));

            document.querySelectorAll('[data-ledger-table]').forEach(table => {
                const accountInfo = table.getAttribute('data-account'); // "Code - Label"
                const parts = accountInfo.split(' - ');
                const codeCompte = parts[0] || '';
                const libelleCompte = parts[1] || '';

                table.querySelectorAll('tbody tr').forEach(row => {
                    const cols = row.querySelectorAll('td');
                    if (cols.length < 5) return; // Ignore les lignes vides ou de message

                    const rowData = [
                        q(codeCompte),
                        q(libelleCompte),
                        q(cols[0].innerText.trim()),
                        q(cols[1].innerText.trim()),
                        q(cols[2].innerText.trim()),
                        q(cols[3].innerText.trim()),
                        q(cols[4].innerText.trim())
                    ];
                    csv.push(rowData.join(sep));
                });

                // Optionnel : Ligne de totalisation pour le compte
                const foot = table.querySelector('tfoot');
                if (foot) {
                    const totalRow = foot.querySelector('tr');
                    if (totalRow) {
                        const totalCols = totalRow.querySelectorAll('td');
                        if (totalCols.length >= 5) {
                            csv.push([
                                q(codeCompte),
                                q('TOTAL ' + libelleCompte),
                                '', '', '',
                                q(totalCols[3].innerText.trim()),
                                q(totalCols[4].innerText.trim())
                            ].join(sep));
                        }
                    }
                }
            });

            const csvContent = '\uFEFF' + csv.join('\n');
            const blob = new Blob([csvContent], {
                type: 'text/csv;charset=utf-8;'
            });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.setAttribute('download', 'Grand_Livre_Complet_' + new Date().toISOString().slice(0, 10) + '.csv');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
@endsection
