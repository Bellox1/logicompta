@extends('layouts.accounting')

@section('title', 'Pièce Comptable ' . str_replace('PC-', '', $entry->numero_piece))

@section('content')
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="javascript:history.back()" class="p-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    <i data-lucide="arrow-left" class="w-5 h-5 text-gray-600"></i>
                </a>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Pièce Comptable :
                    {{ str_replace('PC-', '', $entry->numero_piece) }}</h1>
            </div>
            <p class="text-sm text-gray-500 font-medium">Détail complet de l'opération enregistrée dans le journal <span
                    class="text-primary font-bold">{{ $entry->journal->name }}</span></p>
        </div>
        <div class="flex gap-4">
            <button onclick="exportTableToExcel('piece-table', 'Piece_{{ $entry->numero_piece }}')"
                class="inline-flex items-center justify-center px-6 py-3 bg-green-600 text-white font-bold rounded-2xl hover:bg-green-700 transition-all shadow-sm">
                <i data-lucide="file-spreadsheet" class="w-5 h-5 mr-3"></i>
                Exporter Excel
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
        <div class="lg:col-span-2">
            <div class="bg-card-bg border border-border rounded-none shadow-sm overflow-hidden overflow-x-auto"
                id="piece-table">
                <table class="w-full border-collapse min-w-[800px]">
                    <thead>
                        <tr class="bg-primary text-white">
                            <th class="px-6 py-5 text-xs font-bold uppercase tracking-widest text-center border-r border-white/10"
                                style="width: 100px;">DATE</th>
                            <th class="px-6 py-5 text-xs font-bold uppercase tracking-widest text-left border-r border-white/10"
                                style="width: 110px;">N° DE COMPTE</th>
                            <th
                                class="px-6 py-5 text-xs font-bold uppercase tracking-widest text-left border-r border-white/10">
                                INTITULÉ / LIBELLÉ</th>
                            <th class="px-6 py-5 text-xs font-bold uppercase tracking-widest text-right border-r border-white/10"
                                style="width: 120px;">DÉBIT</th>
                            <th class="px-6 py-5 text-xs font-bold uppercase tracking-widest text-right"
                                style="width: 120px;">CRÉDIT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 italic">
                        @foreach ($entry->lines as $index => $line)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                @if ($index === 0)
                                    <td rowspan="{{ $entry->lines->count() }}"
                                        class="px-6 py-6 text-sm text-gray-900 font-bold text-center border-r border-gray-100 align-middle not-italic bg-gray-50/30">
                                        {{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}
                                    </td>
                                @endif
                                <td class="px-6 py-5 text-sm font-bold text-gray-800 border-r border-gray-100 not-italic">
                                    {{ $line->sousCompte->numero_sous_compte }}
                                </td>
                                <td class="px-6 py-5 text-sm text-gray-600 border-r border-gray-100">
                                    <div class="font-bold text-gray-800 not-italic mb-1">{{ $line->sousCompte->libelle }}</div>
                                    <div class="text-xs opacity-70">{{ $line->libelle }}</div>
                                </td>
                                <td
                                    class="px-6 py-5 text-sm text-right font-bold text-gray-900 border-r border-gray-100 bg-gray-50/10 not-italic whitespace-nowrap">
                                    {{ number_format($line->debit, 2, ',', ' ') }}
                                </td>
                                <td class="px-6 py-5 text-sm text-right font-bold text-gray-900 bg-gray-50/10 not-italic whitespace-nowrap">
                                    {{ number_format($line->credit, 2, ',', ' ') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t-2 border-primary/20">
                        <tr class="font-bold text-gray-900">
                            <td colspan="3" class="px-6 py-5 text-right uppercase tracking-widest text-xs opacity-50">
                                Totaux Équilibrés</td>
                            <td class="px-6 py-5 text-right text-lg border-r border-gray-200 whitespace-nowrap">
                                {{ number_format($entry->lines->sum('debit'), 2, ',', ' ') }}</td>
                            <td class="px-6 py-5 text-right text-lg whitespace-nowrap">
                                {{ number_format($entry->lines->sum('credit'), 2, ',', ' ') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="space-y-8">

            <div class="bg-primary/5 border border-primary/20 rounded-none p-8">
                <div class="flex items-center gap-4 mb-4">
                    <div class="bg-primary text-white p-2 rounded-xl">
                        <i data-lucide="info" class="w-5 h-5"></i>
                    </div>
                    <h4 class="font-bold text-primary text-sm uppercase tracking-widest">Information Système</h4>
                </div>
                <p class="text-xs text-primary/70 leading-relaxed italic">
                    Cette pièce comptable a été générée automatiquement lors de la saisie. Elle est immuable et sert de base
                    de calcul pour le Grand Livre, la Balance et les États Financiers.
                </p>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function exportTableToExcel(tableWrapperId, filename) {
            const wrapper = document.getElementById(tableWrapperId);
            const table = wrapper ? wrapper.querySelector('table') : null;
            if (!table) return;

            let csv = [];
            const rows = table.querySelectorAll('tr');
            rows.forEach(row => {
                const cols = row.querySelectorAll('th, td');
                const rowData = [];
                cols.forEach(col => {
                    let text = col.innerText.replace(/\n/g, ' ').trim();
                    text = '"' + text.replace(/"/g, '""') + '"';
                    rowData.push(text);
                });
                csv.push(rowData.join(';'));
            });

            const csvContent = '\uFEFF' + csv.join('\n');
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
