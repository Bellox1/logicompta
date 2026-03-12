@extends('layouts.accounting')

@section('title', 'Grand Livre')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Grand Livre</h1>
        <p class="text-sm text-gray-500 italic">Détail des mouvements par compte comptable</p>
    </div>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('accounting.ledger', ['mode' => 'all']) }}" class="px-4 py-2 {{ $mode === 'all' ? 'bg-primary text-white' : 'bg-white text-gray-700' }} border border-border rounded-xl text-xs font-bold uppercase transition-all shadow-sm">Tout le Grand Livre</a>
        <div class="relative">
            <button id="class-dropdown-btn" class="px-4 py-2 {{ $mode === 'class' ? 'bg-primary text-white' : 'bg-white text-gray-700' }} border border-border rounded-xl text-xs font-bold uppercase transition-all shadow-sm flex items-center gap-2">
                Par Classe
                <i data-lucide="chevron-down" class="w-3 h-3"></i>
            </button>
            <div id="class-dropdown-menu" class="absolute right-0 mt-2 w-48 bg-white border border-border rounded-xl shadow-xl z-20 hidden">
                @foreach(range(1, 9) as $class)
                    <a href="{{ route('accounting.ledger', ['mode' => 'class', 'class' => $class]) }}" class="block px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 transition-colors border-b border-gray-100 last:border-0 border-r-0 border-l-0 border-t-0">Classe {{ $class }}</a>
                @endforeach
            </div>
        </div>
    </div>
</div>

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
<div class="bg-card-bg border border-border rounded-2xl p-6 shadow-sm mb-8">
    <div class="flex flex-col md:flex-row md:items-end gap-6">
        <div class="flex-1">
            <label for="account_select" class="block text-sm font-semibold text-gray-700 mb-2 px-1 uppercase tracking-wider">Sélectionner un compte spécifique</label>
            <div class="relative">
                <select id="account_select" class="w-full bg-gray-50 border border-border rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all appearance-none cursor-pointer" onchange="window.location.href='/accounting/ledger/' + this.value">
                    <option value="">-- Choisir un compte dans le plan comptable --</option>
                    @foreach($accounts as $classId => $classAccounts)
                        <optgroup label="CLASSE {{ $classId }}">
                            @foreach($classAccounts as $acc)
                                <option value="{{ $acc->id }}" {{ $selectedAccount && $selectedAccount->id == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->code_compte }} - {{ $acc->libelle }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                    <i data-lucide="chevron-down" class="w-5 h-5"></i>
                </div>
            </div>
        </div>
        @if(count($data) > 0)
            <button onclick="exportLedgerToExcel()" class="bg-green-600 text-white font-semibold px-6 py-3 rounded-xl hover:bg-green-700 transition-all flex items-center justify-center gap-2 shadow-sm whitespace-nowrap">
                <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                Exporter Excel
            </button>
        @endif
    </div>
</div>

@if(count($data) > 0)
    @foreach($data as $account)
        <div class="bg-card-bg border border-border rounded-3xl shadow-sm mb-8 page-break-after">
            <!-- Informations Compte -->
            <div class="grid grid-cols-1 md:grid-cols-3 bg-gray-50 border-b border-border p-5 gap-4">
                <div class="flex flex-col">
                    <span class="text-[10px] uppercase font-bold text-gray-400">Intitulé du compte</span>
                    <span class="text-sm font-bold uppercase italic text-gray-900 leading-tight">{{ $account->libelle }}</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] uppercase font-bold text-gray-400">Numéro de compte</span>
                    <span class="text-lg font-mono font-bold tracking-widest text-primary leading-none">
                        {{ str_pad($account->code_compte, 9, '0', STR_PAD_RIGHT) }}
                    </span>
                </div>
                <div class="flex flex-col md:items-end">
                    <span class="text-[10px] uppercase font-bold text-gray-400">Date d'impression</span>
                    <span class="text-sm font-bold text-gray-700 italic">{{ date('d/m/Y H:i') }}</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="w-full border-separate border-spacing-0 min-w-[800px] sticky-thead" data-ledger-table data-account="{{ $account->code_compte }} - {{ $account->libelle }}">
                    <thead>
                        <!-- EN-TÊTE DES COLONNES STICKY -->
                        <tr class="bg-primary text-white">
                            <th class="px-6 py-4 text-xs font-extrabold tracking-wider text-left">DATES</th>
                            <th class="px-6 py-4 text-xs font-extrabold tracking-wider text-left border-r border-white/20">Num PC</th>
                            <th class="px-6 py-4 text-xs font-extrabold tracking-wider text-left border-r border-white/20">LIBELLÉ DES OPERATIONS</th>
                            <th class="px-6 py-4 text-xs font-extrabold tracking-wider text-right">DÉBIT</th>
                            <th class="px-6 py-4 text-xs font-extrabold tracking-wider text-right">CRÉDIT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @php $runningSolde = 0; @endphp
                        @forelse($account->entryLines as $line)
                            @php $runningSolde += ($line->debit - $line->credit); @endphp
                            <tr class="hover:bg-gray-50/80 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                    {{ \Carbon\Carbon::parse($line->entry->date)->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-bold border-r border-gray-100">
                                    <a href="{{ route('accounting.journal.show', $line->entry->id) }}" class="text-primary hover:underline flex items-center gap-1">
                                        {{ str_replace('PC-', '', $line->entry->numero_piece) }}
                                        <i data-lucide="external-link" class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-gray-700 italic text-xs border-r border-gray-100">
                                    {{ $line->libelle }}
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-900 bg-gray-50/5 whitespace-nowrap">
                                    {{ number_format($line->debit, 2, ',', ' ') }}
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-900 bg-gray-50/5 whitespace-nowrap">
                                    {{ number_format($line->credit, 2, ',', ' ') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic font-medium">
                                    <i data-lucide="info" class="w-10 h-10 mx-auto mb-3 opacity-20"></i>
                                    Aucun mouvement enregistré pour ce compte.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($account->entryLines) > 0)
                        <tfoot class="bg-gray-50/50">
                            <tr class="border-t-2 border-primary/20 font-bold bg-primary/5">
                                <td colspan="3" class="px-6 py-5 text-right text-[10px] uppercase tracking-widest text-primary">Sous-total {{ $account->code_compte }}</td>
                                <td class="px-6 py-5 text-right text-base text-primary whitespace-nowrap">{{ number_format($account->entryLines->sum('debit'), 2, ',', ' ') }}</td>
                                <td class="px-6 py-5 text-right text-base text-primary whitespace-nowrap">{{ number_format($account->entryLines->sum('credit'), 2, ',', ' ') }}</td>
                            </tr>
                            <tr class="bg-white border-t border-gray-100">
                                <td colspan="3"></td>
                                <td colspan="2" class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-2 md:gap-4 whitespace-nowrap">
                                        <span class="text-[9px] md:text-[10px] uppercase font-bold text-gray-400">Solde Net :</span>
                                        <span class="text-sm md:text-xl font-black {{ $runningSolde >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ number_format(abs($runningSolde), 2, ',', ' ') }} {{ $runningSolde >= 0 ? 'D' : 'C' }}
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
    <div class="bg-white border-2 border-dashed border-gray-200 rounded-3xl p-20 text-center">
        <div class="bg-gray-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
            <i data-lucide="search" class="w-10 h-10 text-gray-300"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Aucune donnée à afficher</h3>
        <p class="text-gray-500 max-w-sm mx-auto leading-relaxed">
            Veuillez choisir un compte, une classe ou afficher le Grand Livre complet pour visualiser les mouvements.
        </p>
    </div>
@endif

<style>
    @media print {
        .page-break-after {
            page-break-after: always;
        }
        nav, header, footer, button, .no-print {
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
    csv.push(['COMPTE', 'INTITULÉ', 'DATES', 'Num PC', 'LIBELLÉ DES OPERATIONS', 'DÉBIT', 'CRÉDIT'].map(h => q(h)).join(sep));
    
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
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.setAttribute('download', 'Grand_Livre_Complet_' + new Date().toISOString().slice(0, 10) + '.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endsection
