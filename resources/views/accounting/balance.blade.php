@extends('layouts.accounting')

@section('title', 'Balance Générale')

@section('content')
@if(request('show_archived') == '1' && request('start_date'))
    <div class="mb-6 bg-primary/10 border-l-4 border-primary p-4 flex items-center justify-between shadow-sm animate-fade-in no-print">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary text-white flex items-center justify-center rounded-xl">
                <i data-lucide="archive" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-lg font-black text-slate-800 uppercase leading-none">Archives de l'exercice {{ date('Y', strtotime(request('start_date'))) }}</h3>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">Données scellées et définitives</p>
            </div>
        </div>
        <a href="{{ route('accounting.archive.index') }}" class="text-[10px] font-black uppercase text-primary bg-white border border-primary px-3 py-1.5 rounded-lg hover:bg-primary hover:text-white transition-all">
            Retour au Hub
        </a>
    </div>
@endif

<div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6 no-print">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Balance Générale</h1>
        <p class="text-sm text-slate-500 mt-1">Vérification de l'équilibre arithmétique de vos comptes</p>
    </div>
    <div class="flex items-center gap-3 no-print">
        <a href="{{ route('accounting.balance.pdf') }}" target="_blank" 
            class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-semibold rounded-lg hover:bg-slate-50 transition-all text-xs flex items-center gap-2">
            <i data-lucide="file-text" class="w-4 h-4 text-rose-500"></i>
            Format PDF
        </a>
        <button onclick="exportTableToExcel('balance-table', 'Balance_Generale')" 
            class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-semibold rounded-lg hover:bg-slate-50 transition-all text-xs flex items-center gap-2">
            <i data-lucide="sheet" class="w-4 h-4 text-emerald-500"></i>
            Excel
        </button>
    </div>
</div>

<!-- Filtre par période -->
<div class="mb-10 no-print">
<form action="{{ request()->url() }}" method="GET" class="mb-10 grid grid-cols-1 md:flex md:flex-row md:items-end gap-5 bg-white border border-slate-200 p-6 rounded-xl shadow-sm no-print">
    <div class="w-full md:flex-1">
        <label class="block text-[11px] font-bold text-slate-400 mb-2 uppercase tracking-wider px-1">Période du</label>
        <input type="date" name="start_date" value="{{ request('start_date') }}" 
               class="w-full bg-slate-50 border border-slate-200 px-4 py-3 text-sm font-semibold outline-none focus:border-primary transition-all rounded-lg">
    </div>
    <div class="w-full md:flex-1">
        <label class="block text-[11px] font-bold text-slate-400 mb-2 uppercase tracking-wider px-1">Au</label>
        <input type="date" name="end_date" value="{{ request('end_date') }}" 
               class="w-full bg-slate-50 border border-slate-200 px-4 py-3 text-sm font-semibold outline-none focus:border-primary transition-all rounded-lg">
    </div>
    <div class="flex items-center gap-3">
        <button type="submit" class="px-8 py-3 bg-primary text-white text-xs font-bold uppercase tracking-widest hover:opacity-90 transition-all shadow-sm rounded-lg flex items-center gap-2">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i> Actualiser
        </button>
        @if(request()->hasAny(['start_date', 'end_date']))
            <a href="{{ request()->url() }}" class="px-6 py-3 bg-slate-100 text-slate-500 text-xs font-bold uppercase tracking-widest hover:bg-slate-200 transition-all rounded-lg flex items-center gap-2 border border-slate-200">
                <i data-lucide="x" class="w-4 h-4"></i> Effacer
            </a>
        @endif
    </div>
</form>
</div>

<div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-12" id="balance-table">
    <div class="bg-slate-50/50 border-b border-slate-100 px-8 py-8 text-center">
        <h2 class="text-xl font-bold text-slate-800 uppercase tracking-wide">État de la Balance au {{ date('d/m/Y') }}</h2>
    </div>

    <div class="table-responsive">
        <table class="w-full border-collapse min-w-[1000px]">
            <thead>
                <tr class="text-slate-400 border-b border-slate-100 italic">
                    <th rowspan="2" class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-left" style="width: 140px;">Compte</th>
                    <th rowspan="2" class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-left">Intitulé</th>
                    <th colspan="2" class="px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-center border-l border-slate-50">Flux de la période</th>
                    <th colspan="2" class="px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-center border-l border-slate-50">Solde final</th>
                </tr>
                <tr class="text-slate-400 border-b border-slate-100 italic">
                    <th class="px-6 py-2 text-right text-[10px] uppercase font-bold border-l border-slate-50">Débit</th>
                    <th class="px-6 py-2 text-right text-[10px] uppercase font-bold">Crédit</th>
                    <th class="px-6 py-2 text-right text-[10px] uppercase font-bold border-l border-slate-50">Débit</th>
                    <th class="px-6 py-2 text-right text-[10px] uppercase font-bold">Crédit</th>
                </tr>
            </thead>
            <tbody class="text-xs">
                @forelse($balanceData as $classId => $class)
                    <!-- PARCOURS DES GROUPES D'UNE CLASSE (ex: 10, 11, 12...) -->
                    @foreach($class['groups'] as $prefix => $group)
                        <!-- Comptes individuels du groupe -->
                        @foreach($group['accounts'] as $acc)
                            <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-3 font-mono font-bold text-slate-900 border-r border-slate-200 italic">{{ $acc['code'] }}</td>
                                <td class="px-4 py-3 text-slate-700 border-r border-slate-200 font-medium uppercase">{{ $acc['libelle'] }}</td>
                                
                                <td class="px-4 py-3 text-right font-semibold text-slate-900 border-r border-slate-200 whitespace-nowrap">
                                    {{ $acc['mouv_debit'] > 0 ? number_format($acc['mouv_debit'], 2, ',', ' ') : '0,00' }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900 border-r border-slate-200 whitespace-nowrap">
                                    {{ $acc['mouv_credit'] > 0 ? number_format($acc['mouv_credit'], 2, ',', ' ') : '0,00' }}
                                </td>
                                
                                <td class="px-4 py-3 text-right font-bold text-green-700 bg-green-50/10 border-r border-slate-200 whitespace-nowrap">
                                    {{ $acc['fin_debit'] > 0 ? number_format($acc['fin_debit'], 2, ',', ' ') : '0,00' }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-red-700 bg-red-50/10 border-r border-slate-200 whitespace-nowrap">
                                    {{ $acc['fin_credit'] > 0 ? number_format($acc['fin_credit'], 2, ',', ' ') : '0,00' }}
                                </td>
                            </tr>
                        @endforeach

                        <!-- Sous-Total du Groupe (S'affiche après chaque groupe) -->
                        <tr class="bg-slate-100/50 border-b border-slate-200 font-bold italic text-slate-800">
                            <td colspan="2" class="px-4 py-3 border-r border-slate-200 uppercase text-[10px]">Sous Total {{ $group['prefix'] }}</td>
                            <td class="px-4 py-3 text-right border-r border-slate-200 whitespace-nowrap">{{ number_format($group['group_totals']['mouv_debit'], 2, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right border-r border-slate-200 whitespace-nowrap">{{ number_format($group['group_totals']['mouv_credit'], 2, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right border-r border-slate-200 text-green-800 whitespace-nowrap">{{ number_format($group['group_totals']['fin_debit'], 2, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right border-r border-slate-200 text-red-800 whitespace-nowrap">{{ number_format($group['group_totals']['fin_credit'], 2, ',', ' ') }}</td>
                        </tr>
                    @endforeach

                    <!-- Total de la Classe complète -->
                    <tr class="bg-primary/10 border-b-2 border-primary/30 font-black">
                        <td colspan="2" class="px-4 py-5 text-primary text-[11px] uppercase tracking-[0.2em] border-r border-primary/20">Total Classe {{ $classId }}</td>
                        
                        <td class="px-4 py-5 text-right text-primary border-r border-primary/20 whitespace-nowrap">{{ number_format($class['class_totals']['mouv_debit'], 2, ',', ' ') }}</td>
                        <td class="px-4 py-5 text-right text-primary border-r border-primary/20 whitespace-nowrap">{{ number_format($class['class_totals']['mouv_credit'], 2, ',', ' ') }}</td>
                        
                        <td class="px-4 py-5 text-right text-green-900 bg-green-600/10 border-r border-primary/20 whitespace-nowrap">{{ number_format($class['class_totals']['fin_debit'], 2, ',', ' ') }}</td>
                        <td class="px-4 py-5 text-right text-red-900 bg-red-600/10 border-r border-primary/20 whitespace-nowrap">{{ number_format($class['class_totals']['fin_credit'], 2, ',', ' ') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">
                            <i data-lucide="file-warning" class="w-12 h-12 mx-auto mb-4 text-slate-200"></i>
                            <p class="text-slate-500 font-medium italic">Aucune donnée disponible pour établir la balance.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            
            @if(!empty($balanceData))
                <tfoot class="border-t-4 border-primary">
                    <tr class="bg-primary text-white font-black uppercase text-xs">
                        <td colspan="2" class="px-6 py-6 tracking-[0.2em] border-r border-white/10">Total Balance Générale</td>
                        
                        <!-- Totaux Mouvements -->
                        <td class="px-4 py-6 text-right border-r border-white/10 font-mono text-white whitespace-nowrap">{{ number_format($grandTotal['mouv_debit'], 2, ',', ' ') }}</td>
                        <td class="px-4 py-6 text-right border-r border-white/10 font-mono text-white whitespace-nowrap">{{ number_format($grandTotal['mouv_credit'], 2, ',', ' ') }}</td>
                        
                        <!-- Totaux Fin -->
                        <td class="px-4 py-6 text-right border-r border-white/10 font-mono text-white whitespace-nowrap">{{ number_format($grandTotal['fin_debit'], 2, ',', ' ') }}</td>
                        <td class="px-4 py-6 text-right font-mono text-white whitespace-nowrap">{{ number_format($grandTotal['fin_credit'], 2, ',', ' ') }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

@php
    $isEquilibre = abs($grandTotal['mouv_debit'] - $grandTotal['mouv_credit']) < 0.001;
@endphp

@if(!$isEquilibre)
<div class="mt-8 bg-red-50 border-2 border-red-200 rounded-2xl p-6 flex flex-col md:flex-row items-center gap-6 text-red-700 animate-pulse no-print">
    <div class="bg-red-100 p-4 rounded-full">
        <i data-lucide="octagon-alert" class="w-8 h-8"></i>
    </div>
    <div class="flex-1 text-center md:text-left">
        <h4 class="text-lg font-bold uppercase tracking-wider leading-none mb-1">Déséquilibre détecté !</h4>
        <p class="text-sm font-medium opacity-80 leading-relaxed italic">
            Une différence de <strong>{{ number_format(abs($grandTotal['mouv_debit'] - $grandTotal['mouv_credit']), 2, ',', ' ') }} F</strong> a été identifiée dans les mouvements. Vérifiez l'égalité Débit/Crédit.
        </p>
    </div>
</div>
@endif

{{-- Données JSON pour l'export Excel propre --}}
<script>
const balanceDataJson = @json($balanceData);
const grandTotalJson = @json($grandTotal);

function exportTableToExcel(tableWrapperId, filename) {
    const sep = ';';
    const q = (v) => '"' + String(v).replace(/"/g, '""') + '"';

    let rows = [];

    // En-tête (Exactement comme la vue)
    rows.push([
        q('NUM DE COMPTES'), 
        q('INTITULÉ DES COMPTES'), 
        q('MOUVEMENTS DE LA PERIODE (DÉBIT)'), 
        q('MOUVEMENTS DE LA PERIODE (CRÉDIT)'), 
        q('SOLDES EN FIN DE PERIODE (DÉBIT)'), 
        q('SOLDES EN FIN DE PERIODE (CRÉDIT)')
    ].join(sep));

    for (const [classId, classData] of Object.entries(balanceDataJson)) {
        for (const [prefix, group] of Object.entries(classData.groups)) {
            // Lignes de comptes
            for (const acc of group.accounts) {
                rows.push([
                    q(acc.code),
                    q(acc.libelle),
                    q(acc.mouv_debit > 0 ? acc.mouv_debit.toFixed(2).replace('.', ',') : '0,00'),
                    q(acc.mouv_credit > 0 ? acc.mouv_credit.toFixed(2).replace('.', ',') : '0,00'),
                    q(acc.fin_debit > 0 ? acc.fin_debit.toFixed(2).replace('.', ',') : '0,00'),
                    q(acc.fin_credit > 0 ? acc.fin_credit.toFixed(2).replace('.', ',') : '0,00'),
                ].join(sep));
            }
            // Sous-total groupe
            const gt = group.group_totals;
            rows.push([
                q('SOUS TOTAL ' + prefix),
                q(''),
                q(gt.mouv_debit.toFixed(2).replace('.', ',')),
                q(gt.mouv_credit.toFixed(2).replace('.', ',')),
                q(gt.fin_debit.toFixed(2).replace('.', ',')),
                q(gt.fin_credit.toFixed(2).replace('.', ',')),
            ].join(sep));
        }
        // Total classe
        const ct = classData.class_totals;
        rows.push([
            q('TOTAL CLASSE ' + classId),
            q(''),
            q(ct.mouv_debit.toFixed(2).replace('.', ',')),
            q(ct.mouv_credit.toFixed(2).replace('.', ',')),
            q(ct.fin_debit.toFixed(2).replace('.', ',')),
            q(ct.fin_credit.toFixed(2).replace('.', ',')),
        ].join(sep));
        // Ligne vide entre classes
        rows.push(['', '', '', '', '', ''].join(sep));
    }

    // Total général
    rows.push([
        q('TOTAL BALANCE GÉNÉRALE'), q(''),
        q(grandTotalJson.mouv_debit.toFixed(2).replace('.', ',')),
        q(grandTotalJson.mouv_credit.toFixed(2).replace('.', ',')),
        q(grandTotalJson.fin_debit.toFixed(2).replace('.', ',')),
        q(grandTotalJson.fin_credit.toFixed(2).replace('.', ',')),
    ].join(sep));

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

