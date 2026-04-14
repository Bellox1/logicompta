@extends('layouts.accounting')

@section('title', 'Balance Générale')

@section('styles')
    <style>
        div#balance-table,
        div#balance-table div,
        div#balance-table table,
        div#balance-table th,
        div#balance-table td {
            border-color: #000000 !important;
        }
    </style>
@endsection

@section('content')
    @if (request('show_archived') == '1' && request('start_date'))
        <div
            class="mb-6 bg-primary/10 border-l-4 border-primary p-4 flex items-center justify-between shadow-sm animate-fade-in no-print">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary text-white flex items-center justify-center rounded-xl">
                    <i data-lucide="archive" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-text-main uppercase leading-none">Archives de l'exercice
                        {{ date('Y', strtotime(request('start_date'))) }}</h3>
                    <p class="text-xs text-text-secondary font-black uppercase tracking-widest mt-1">Données scellées
                        et définitives</p>
                </div>
            </div>
            <a href="{{ route('accounting.archive.index') }}"
                class="text-[10px] font-black uppercase text-primary bg-white border border-primary px-3 py-1.5 rounded-lg hover:bg-primary hover:text-white transition-all">
                Retour au Hub
            </a>
        </div>
    @endif

    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6 no-print">
        <div>
            <h1 class="text-3xl font-black text-text-main uppercase tracking-tight">Balance Générale</h1>
            <p class="text-sm text-text-secondary mt-1 font-bold">Vérification de l'équilibre arithmétique de vos
                comptes</p>
        </div>
        <div class="flex items-center gap-3 no-print">
            <a href="{{ route('accounting.balance.pdf') }}" target="_blank"
                class="px-5 py-2.5 bg-card-bg border border-border text-text-main font-black rounded-xl hover:-translate-y-0.5 transition-all text-xs flex items-center gap-2 shadow-sm">
                <i data-lucide="file-text" class="w-4 h-4 text-rose-500"></i>
                Format PDF
            </a>
            <button onclick="exportTableToExcel('balance-table', 'Balance_Generale')"
                class="px-5 py-2.5 bg-card-bg border border-border text-text-main font-black rounded-xl hover:-translate-y-0.5 transition-all text-xs flex items-center gap-2 shadow-sm">
                <i data-lucide="sheet" class="w-4 h-4 text-emerald-500"></i>
                Excel
            </button>
        </div>
    </div>

    <!-- Filtre par période -->
    <div class="mb-10 no-print">
        <form action="{{ request()->url() }}" method="GET"
            class="mb-10 grid grid-cols-1 md:flex md:flex-row md:items-end gap-5 bg-card-bg border border-border p-6 rounded-3xl shadow-sm no-print">
            <div class="w-full md:flex-1">
                <label class="block text-[11px] font-black text-text-secondary mb-2 uppercase tracking-wider px-1">Période
                    du</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" placeholder="JJ/MM/AAAA"
                    class="w-full bg-bg border border-border px-4 py-3 text-sm font-black outline-none focus:border-primary transition-all rounded-xl dark:text-white">
            </div>
            <div class="w-full md:flex-1">
                <label
                    class="block text-[11px] font-black text-text-secondary mb-2 uppercase tracking-wider px-1">Au</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" placeholder="JJ/MM/AAAA"
                    class="w-full bg-bg border border-border px-4 py-3 text-sm font-black outline-none focus:border-primary transition-all rounded-xl dark:text-white">
            </div>
            <div class="flex flex-col md:flex-row gap-3">
                <button type="submit"
                    class="w-full md:w-auto px-5 py-2.5 bg-primary text-white text-xs font-black uppercase tracking-widest hover:bg-primary-light hover:-translate-y-0.5 transition-all shadow-lg rounded-xl flex items-center justify-center gap-2">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i> Actualiser
                </button>
                @if (request()->hasAny(['start_date', 'end_date']))
                    <a href="{{ request()->url() }}"
                        class="w-full md:w-auto px-5 py-2.5 bg-white text-text-secondary text-xs font-black uppercase tracking-widest hover:bg-slate-50 transition-all rounded-xl flex items-center justify-center gap-2 border border-border shadow-sm">
                        <i data-lucide="x" class="w-4 h-4"></i> Effacer
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-card-bg border border-border rounded-[2.5rem] shadow-sm overflow-hidden mb-12" id="balance-table">
        <div class="bg-white/50 border-b border-border px-8 py-8 text-center">
            <h2 class="text-xl font-black text-text-main uppercase tracking-[0.2em]">État de la Balance au
                {{ date('d/m/Y') }}</h2>
        </div>

        <div class="table-responsive">
            <table class="w-full border-collapse min-w-[1000px] sticky-thead">
                <thead>
                    <tr
                        class="bg-primary text-white text-[10px] uppercase font-black tracking-widest border-b border-black">
                        <th rowspan="2" class="px-4 py-4 text-left border-r border-black" style="width: 120px;">NUMÉRO DE
                            COMPTES</th>
                        <th rowspan="2" class="px-4 py-4 text-left border-r border-black">INTITULÉ DES COMPTES</th>
                        <th colspan="2" class="px-4 py-3 text-center border-r border-black">SOLDES EN DÉBUT DE PERIODE
                        </th>
                        <th colspan="2" class="px-4 py-3 text-center border-r border-black">MOUVEMENTS DE LA PERIODE</th>
                        <th colspan="2" class="px-4 py-3 text-center">SOLDES EN FIN DE PERIODE</th>
                    </tr>
                    <tr class="bg-primary text-white text-[9px] uppercase font-bold tracking-widest border-b border-black">
                        <!-- Soldes Début -->
                        <th class="px-4 py-2 text-right border-r border-black">DÉBIT</th>
                        <th class="px-4 py-2 text-right border-r border-black">CRÉDIT</th>
                        <!-- Mouvements -->
                        <th class="px-4 py-2 text-right border-r border-black">DÉBIT</th>
                        <th class="px-4 py-2 text-right border-r border-black">CRÉDIT</th>
                        <!-- Soldes Fin -->
                        <th class="px-4 py-2 text-right border-r border-black">DÉBIT</th>
                        <th class="px-4 py-2 text-right">CRÉDIT</th>
                    </tr>
                </thead>
                <tbody class="text-xs">
                    @forelse($balanceData as $classId => $class)
                        <!-- PARCOURS DES GROUPES D'UNE CLASSE (ex: 10, 11, 12...) -->
                        @foreach ($class['groups'] as $prefix => $group)
                            <!-- Comptes individuels du groupe -->
                            @foreach ($group['accounts'] as $acc)
                                <tr class="border-b border-black hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                                    <td class="px-4 py-3 font-mono font-bold text-text-main border-r border-black">
                                        {{ $acc['code'] }}</td>
                                    <td class="px-4 py-3 text-text-secondary border-r border-black font-medium uppercase">
                                        {{ $acc['libelle'] }}</td>

                                    <!-- Soldes Début (Hypothétique/Fixe pour correspondre au design V1) -->
                                    <td
                                        class="px-4 py-3 text-right font-medium text-slate-400 border-r border-black whitespace-nowrap">
                                        -</td>
                                    <td
                                        class="px-4 py-3 text-right font-medium text-slate-400 border-r border-black whitespace-nowrap">
                                        -</td>

                                    <!-- Mouvements -->
                                    <td
                                        class="px-4 py-3 text-right font-black text-text-main border-r border-black whitespace-nowrap">
                                        {{ $acc['mouv_debit'] > 0 ? number_format($acc['mouv_debit'], 2, ',', ' ') : '0,00' }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right font-black text-text-main border-r border-black whitespace-nowrap">
                                        {{ $acc['mouv_credit'] > 0 ? number_format($acc['mouv_credit'], 2, ',', ' ') : '0,00' }}
                                    </td>

                                    <!-- Soldes Fin -->
                                    <td
                                        class="px-4 py-3 text-right font-black text-primary dark:text-primary-light border-r border-black whitespace-nowrap bg-primary/5">
                                        {{ $acc['fin_debit'] > 0 ? number_format($acc['fin_debit'], 2, ',', ' ') : '0,00' }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right font-black text-primary dark:text-primary-light whitespace-nowrap bg-primary/5">
                                        {{ $acc['fin_credit'] > 0 ? number_format($acc['fin_credit'], 2, ',', ' ') : '0,00' }}
                                    </td>
                                </tr>
                            @endforeach

                            <!-- Sous-Total du Groupe -->
                            <tr class="bg-slate-50 dark:bg-white/5 font-bold border-b border-black">
                                <td colspan="2"
                                    class="px-4 py-3 text-[10px] uppercase tracking-widest border-r border-black text-text-secondary">
                                    Sous-Total {{ $prefix }}</td>
                                <td class="px-4 py-3 text-right border-r border-black whitespace-nowrap">-</td>
                                <td class="px-4 py-3 text-right border-r border-black whitespace-nowrap">-</td>
                                <td class="px-4 py-3 text-right border-r border-black whitespace-nowrap text-text-main">
                                    {{ number_format($group['group_totals']['mouv_debit'], 2, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right border-r border-black whitespace-nowrap text-text-main">
                                    {{ number_format($group['group_totals']['mouv_credit'], 2, ',', ' ') }}</td>
                                <td
                                    class="px-4 py-3 text-right border-r border-black text-primary whitespace-nowrap bg-primary/5">
                                    {{ number_format($group['group_totals']['fin_debit'], 2, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right text-primary whitespace-nowrap bg-primary/5">
                                    {{ number_format($group['group_totals']['fin_credit'], 2, ',', ' ') }}</td>
                            </tr>
                        @endforeach

                        <!-- Total de la Classe complète -->
                        <tr class="bg-primary text-white font-black border-b border-black">
                            <td colspan="2"
                                class="px-4 py-5 text-[11px] uppercase tracking-[0.2em] border-r border-white/20">Total
                                Classe {{ $classId }}</td>
                            <td
                                class="px-4 py-5 text-right border-r border-white/20 whitespace-nowrap font-normal opacity-50">
                                -</td>
                            <td
                                class="px-4 py-5 text-right border-r border-white/20 whitespace-nowrap font-normal opacity-50">
                                -</td>
                            <td class="px-4 py-5 text-right border-r border-white/20 whitespace-nowrap">
                                {{ number_format($class['class_totals']['mouv_debit'], 2, ',', ' ') }}</td>
                            <td class="px-4 py-5 text-right border-r border-white/20 whitespace-nowrap">
                                {{ number_format($class['class_totals']['mouv_credit'], 2, ',', ' ') }}</td>
                            <td class="px-4 py-5 text-right border-r border-white/20 whitespace-nowrap">
                                {{ number_format($class['class_totals']['fin_debit'], 2, ',', ' ') }}</td>
                            <td class="px-4 py-5 text-right whitespace-nowrap">
                                {{ number_format($class['class_totals']['fin_credit'], 2, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-20 text-center">
                                <i data-lucide="file-warning" class="w-12 h-12 mx-auto mb-4 text-slate-200"></i>
                                <p class="text-slate-500 font-medium">Aucune donnée disponible pour établir la balance.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if (!empty($balanceData))
                    <tfoot class="border-t-2 border-black">
                        <tr class="bg-primary text-white font-black uppercase text-xs">
                            <td colspan="2" class="px-6 py-6 tracking-[0.2em] border-r border-white/10">Total Balance
                                Générale</td>
                            <td class="px-4 py-6 text-right border-r border-white/10 font-normal opacity-50">-</td>
                            <td class="px-4 py-6 text-right border-r border-white/10 font-normal opacity-50">-</td>
                            <td
                                class="px-4 py-6 text-right border-r border-white/10 font-mono text-white whitespace-nowrap">
                                {{ number_format($grandTotal['mouv_debit'], 2, ',', ' ') }}</td>
                            <td
                                class="px-4 py-6 text-right border-r border-white/10 font-mono text-white whitespace-nowrap">
                                {{ number_format($grandTotal['mouv_credit'], 2, ',', ' ') }}</td>
                            <td
                                class="px-4 py-6 text-right border-r border-white/10 font-mono text-white whitespace-nowrap">
                                {{ number_format($grandTotal['fin_debit'], 2, ',', ' ') }}</td>
                            <td class="px-4 py-6 text-right font-mono text-white whitespace-nowrap">
                                {{ number_format($grandTotal['fin_credit'], 2, ',', ' ') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    @php
        $isEquilibre = abs($grandTotal['mouv_debit'] - $grandTotal['mouv_credit']) < 0.001;
    @endphp

    {{-- @if (!$isEquilibre)
        <div
            class="mt-8 bg-red-50 border-2 border-red-200 rounded-2xl p-6 flex flex-col md:flex-row items-center gap-6 text-red-700 animate-pulse no-print">
            <div class="bg-red-100 p-4 rounded-full">
                <i data-lucide="octagon-alert" class="w-8 h-8"></i>
            </div>
            <div class="flex-1 text-center md:text-left">
                <h4 class="text-lg font-bold uppercase tracking-wider leading-none mb-1">Déséquilibre détecté !</h4>
                <p class="text-sm font-medium opacity-80 leading-relaxed italic">
                    Une différence de
                    <strong>{{ number_format(abs($grandTotal['mouv_debit'] - $grandTotal['mouv_credit']), 2, ',', ' ') }}
                        F</strong> a été identifiée dans les mouvements. Vérifiez l'égalité Débit/Crédit.
                </p>
            </div>
        </div>
    @endif --}}

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
            const blob = new Blob([csvContent], {
                type: 'text/csv;charset=utf-8;'
            });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.setAttribute('download', filename + '_' + new Date().toISOString().slice(0, 10) + '.csv');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
@endsection
