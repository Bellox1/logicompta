@extends('layouts.accounting')

@section('title', 'Balance Générale')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6 no-print">
    <div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Balance Générale</h1>
        <p class="text-sm text-gray-500 italic">Vérification de l'équilibre arithmétique des comptes</p>
    </div>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('accounting.balance.pdf') }}" target="_blank" class="px-4 py-2 bg-red-600 text-white border border-red-700 rounded-xl text-xs font-bold uppercase transition-all shadow-sm flex items-center gap-2 hover:bg-red-700">
            <i data-lucide="file-text" class="w-4 h-4"></i>
            Exporter PDF
        </a>
        <button onclick="exportTableToExcel('balance-table', 'Balance_Generale')" class="px-4 py-2 bg-green-600 text-white border border-green-700 rounded-xl text-xs font-bold uppercase transition-all shadow-sm flex items-center gap-2 hover:bg-green-700">
            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
            Exporter Excel
        </button>
    </div>
</div>

<div class="bg-card-bg border border-border rounded-2xl shadow-sm overflow-hidden mb-12">
    
    <!-- En-tête de l'état -->
    <div class="bg-primary/5 border-b border-primary/10 px-4 md:px-8 py-4 md:py-8 text-center">
        <h1 class="text-base md:text-2xl font-black text-gray-900 uppercase">BALANCE GÉNÉRALE DES COMPTES AU {{ date('d/m/y') }}</h1>
    </div>

    <div class="table-responsive" id="balance-table">
        <table class="w-full border-separate border-spacing-0 min-w-[1000px] sticky-thead">
            <thead>
                <!-- Groupement des colonnes - Tout en bleu primaire -->
                <tr class="bg-primary text-white text-[10px] uppercase font-black tracking-widest border-b border-white/10 row-sticky-1">
                    <th rowspan="2" class="px-4 py-4 text-left border-r border-white/10">NUM DE COMPTES</th>
                    <th rowspan="2" class="px-4 py-4 text-left border-r border-white/10">INTITULÉ DES COMPTES</th>
                    <th colspan="2" class="px-4 py-3 text-center border-r border-white/10">MOUVEMENTS DE LA PERIODE</th>
                    <th colspan="2" class="px-4 py-3 text-center">SOLDES EN FIN DE PERIODE</th>
                </tr>
                <tr class="bg-primary text-white text-[9px] uppercase font-bold tracking-widest row-sticky-2">
                    <!-- Mouvements -->
                    <th class="px-4 py-2 text-right border-r border-white/20">DÉBIT</th>
                    <th class="px-4 py-2 text-right border-r border-white/20">CRÉDIT</th>
                    <!-- Soldes Fin -->
                    <th class="px-4 py-2 text-right border-r border-white/20">DÉBIT</th>
                    <th class="px-4 py-2 text-right">CRÉDIT</th>
                </tr>
            </thead>
            <tbody class="text-xs">
                @forelse($balanceData as $classId => $class)
                    <!-- PARCOURS DES GROUPES D'UNE CLASSE (ex: 10, 11, 12...) -->
                    @foreach($class['groups'] as $prefix => $group)
                        <!-- Comptes individuels du groupe -->
                        @foreach($group['accounts'] as $acc)
                            <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                <td class="px-4 py-3 font-mono font-bold text-gray-900 border-r border-gray-200 italic">{{ $acc['code'] }}</td>
                                <td class="px-4 py-3 text-gray-700 border-r border-gray-200 font-medium uppercase">{{ $acc['libelle'] }}</td>
                                
                                <td class="px-4 py-3 text-right font-semibold text-gray-900 border-r border-gray-200 whitespace-nowrap">
                                    {{ $acc['mouv_debit'] > 0 ? number_format($acc['mouv_debit'], 2, ',', ' ') : '0,00' }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900 border-r border-gray-200 whitespace-nowrap">
                                    {{ $acc['mouv_credit'] > 0 ? number_format($acc['mouv_credit'], 2, ',', ' ') : '0,00' }}
                                </td>
                                
                                <td class="px-4 py-3 text-right font-bold text-green-700 bg-green-50/10 border-r border-gray-200 whitespace-nowrap">
                                    {{ $acc['fin_debit'] > 0 ? number_format($acc['fin_debit'], 2, ',', ' ') : '0,00' }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-red-700 bg-red-50/10 border-r border-gray-200 whitespace-nowrap">
                                    {{ $acc['fin_credit'] > 0 ? number_format($acc['fin_credit'], 2, ',', ' ') : '0,00' }}
                                </td>
                            </tr>
                        @endforeach

                        <!-- Sous-Total du Groupe (S'affiche après chaque groupe) -->
                        <tr class="bg-gray-100/50 border-b border-gray-200 font-bold italic text-gray-800">
                            <td colspan="2" class="px-4 py-3 border-r border-gray-200 uppercase text-[10px]">Sous Total {{ $group['prefix'] }}</td>
                            <td class="px-4 py-3 text-right border-r border-gray-200 whitespace-nowrap">{{ number_format($group['group_totals']['mouv_debit'], 2, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right border-r border-gray-200 whitespace-nowrap">{{ number_format($group['group_totals']['mouv_credit'], 2, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right border-r border-gray-200 text-green-800 whitespace-nowrap">{{ number_format($group['group_totals']['fin_debit'], 2, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right border-r border-gray-200 text-red-800 whitespace-nowrap">{{ number_format($group['group_totals']['fin_credit'], 2, ',', ' ') }}</td>
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
                            <i data-lucide="file-warning" class="w-12 h-12 mx-auto mb-4 text-gray-200"></i>
                            <p class="text-gray-500 font-medium italic">Aucune donnée disponible pour établir la balance.</p>
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

