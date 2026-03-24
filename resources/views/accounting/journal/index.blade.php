@extends('layouts.accounting')

@section('title', 'Journal des écritures')

@section('styles')
    <style>
        .journal-table {
            width: 100%;
            border-collapse: collapse;
        }

        .journal-table th,
        .journal-table td {
            padding: 1rem;
            border: 1px solid #e5e7eb;
            /* border-gray-200 */
        }

        .journal-table th {
            background: var(--primary);
            color: white;
            text-align: left;
            font-weight: 800;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        /* Trait de séparation épais pour la fin d'une pièce */
        .entry-separator td {
            border-bottom: 3px solid #374151 !important;
            /* gray-700 */
        }

        .amount {
            text-align: right;
            font-weight: 700;
            white-space: nowrap;
        }

        .footer-pagination {
            margin-top: 2rem;
        }

        .piece-info {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .piece-info span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
    </style>
@endsection

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Journal des écritures</h1>
            <p class="text-sm text-gray-500">Historique complet des opérations comptables</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('accounting.journal.export.pdf') }}" target="_blank"
                class="flex-1 sm:flex-none inline-flex items-center justify-center px-5 py-2.5 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition-all shadow-sm text-xs">
                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>
                PDF
            </a>
            <button onclick="exportJournalToCSV()"
                class="flex-1 sm:flex-none inline-flex items-center justify-center px-5 py-2.5 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-all shadow-sm text-xs">
                <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                EXCEL
            </button>
            <a href="{{ route('accounting.journal.import') }}"
                class="flex-1 sm:flex-none inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-all shadow-sm text-xs">
                <i data-lucide="upload" class="w-4 h-4 mr-2"></i>
                IMPORTER
            </a>
            <a href="{{ route('accounting.journal.create') }}"
                class="flex-1 sm:flex-none inline-flex items-center justify-center px-5 py-2.5 bg-primary text-white font-semibold rounded-xl hover:bg-primary-light transition-all shadow-sm text-xs">
                <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>
                NOUVELLE
            </a>
        </div>
    </div>

    <div class="bg-card-bg border border-border rounded-2xl shadow-sm mb-8">
        <div class="table-responsive">
            <table class="w-full text-left border-collapse min-w-[1000px] sticky-thead">
                <thead>
                    <tr class="bg-primary text-white">
                        <th class="px-4 py-4 font-bold text-[11px] uppercase tracking-widest text-center border-r border-white/10"
                            style="width: 100px;">DATE</th>
                        <th class="px-4 py-4 font-bold text-[11px] uppercase tracking-widest text-center border-r border-white/10"
                            style="width: 100px;">Num PC</th>
                        <th class="px-4 py-4 font-bold text-[11px] uppercase tracking-widest border-r border-white/10"
                            style="width: 120px;">N° DE COMPTE</th>
                        <th class="px-4 py-4 font-bold text-[11px] uppercase tracking-widest border-r border-white/10">
                            INTITULE</th>
                        <th class="px-4 py-4 font-bold text-[11px] uppercase tracking-widest border-r border-white/10">
                            LIBELLES</th>
                        <th class="px-4 py-4 font-bold text-[11px] uppercase tracking-widest text-right border-r border-white/10"
                            style="width: 120px;">DEBIT</th>
                        <th class="px-4 py-4 font-bold text-[11px] uppercase tracking-widest text-right border-r border-white/10"
                            style="width: 120px;">CREDIT</th>
                        <th class="px-4 py-4 font-bold text-[11px] uppercase tracking-widest text-center"
                            style="width: 100px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="italic">
                    @forelse($entries as $entry)
                        @foreach ($entry->lines as $index => $line)
                            <tr class="hover:bg-gray-50 transition-colors">
                                @if ($index === 0)
                                    <td rowspan="{{ $entry->lines->count() }}"
                                        class="px-5 py-6 text-sm text-gray-950 font-black text-center align-middle not-italic transition-all border-b-2 border-gray-400">
                                        {{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}
                                    </td>
                                    <td rowspan="{{ $entry->lines->count() }}"
                                        class="px-5 py-6 text-sm text-center align-middle not-italic transition-all border-b-2 border-gray-400">
                                        <a href="{{ route('accounting.journal.show', $entry->id) }}"
                                            class="text-primary font-black hover:underline tracking-tighter text-base">
                                            {{ str_replace('PC-', '', $entry->numero_piece) }}
                                        </a>
                                    </td>
                                @endif

                                <td
                                    class="px-5 py-6 text-sm font-bold text-gray-800 not-italic transition-all {{ $loop->last ? 'border-b-2 border-gray-400' : '' }}">
                                    {{ $line->account->code_compte }}
                                </td>
                                <td
                                    class="px-5 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400 not-italic w-[230px] transition-all {{ $loop->last ? 'border-b-2 border-gray-400' : '' }}">
                                    {{ $line->account->libelle }}
                                </td>
                                <td
                                    class="px-5 py-6 text-sm text-gray-600 font-medium transition-all {{ $loop->last ? 'border-b-2 border-gray-400' : '' }}">
                                    {{ $line->libelle ?: $entry->libelle }}
                                </td>
                                <td
                                    class="px-5 py-6 text-sm text-right font-black text-gray-900 not-italic whitespace-nowrap transition-all {{ $loop->last ? 'border-b-2 border-gray-400' : '' }}">
                                    {{ number_format($line->debit, 2, ',', ' ') }}
                                </td>
                                <td
                                    class="px-5 py-6 text-sm text-right font-black text-gray-900 not-italic whitespace-nowrap transition-all {{ $loop->last ? 'border-b-2 border-gray-400' : '' }}">
                                    {{ number_format($line->credit, 2, ',', ' ') }}
                                </td>

                                @if ($index === 0)
                                    <td rowspan="{{ $entry->lines->count() }}"
                                        class="px-4 py-4 text-center align-middle not-italic transition-all border-b-2 border-gray-400">
                                        <div class="flex items-center justify-center gap-4">
                                            <a href="{{ route('accounting.journal.show', $entry->id) }}"
                                                class="p-2 text-gray-300 hover:text-primary transition-all" title="Détails">
                                                <i data-lucide="eye" class="w-6 h-6"></i>
                                            </a>
                                            <a href="{{ route('accounting.journal.show.pdf', $entry->id) }}"
                                                target="_blank" class="p-2 text-gray-300 hover:text-red-500 transition-all"
                                                title="PDF">
                                                <i data-lucide="file-text" class="w-6 h-6"></i>
                                            </a>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-20 text-center not-italic">
                                <i data-lucide="search-x" class="mx-auto w-12 h-12 text-gray-300 mb-4 opacity-50"></i>
                                <p class="text-gray-500 font-medium">Aucune écriture trouvée dans le journal.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8 flex justify-center">
        {{ $entries->links() }}
    </div>
    @section('scripts')
        <script>
            function exportJournalToCSV() {
                const sep = ';';
                const q = (v) => '"' + String(v).replace(/"/g, '""') + '"';
                let rows = [];

                // Headers matching the view exactly
                rows.push(['DATE', 'Num PC', 'N° DE COMPTE', 'INTITULE', 'LIBELLES', 'DEBIT', 'CREDIT'].join(sep));

                let currentDate = '';
                let currentPC = '';

                document.querySelectorAll('tbody tr').forEach(tr => {
                    const cells = tr.querySelectorAll('td');
                    if (cells.length < 5) return; // skip empty state or special rows

                    let rowData = [];
                    if (cells.length >= 7) {
                        // This is the first line of an entry (has Date, PC and Actions)
                        // In HTML it has 8 cells if we count the Actions column
                        currentDate = cells[0].innerText.trim();
                        currentPC = cells[1].innerText.trim();

                        rowData.push(q(currentDate));
                        rowData.push(q(currentPC));
                        rowData.push(q(cells[2].innerText.trim())); // Account Code
                        rowData.push(q(cells[3].innerText.trim())); // Account Label
                        rowData.push(q(cells[4].innerText.trim())); // Libelle
                        rowData.push(q(cells[5].innerText.trim())); // Debit
                        rowData.push(q(cells[6].innerText.trim())); // Credit
                    } else {
                        // Subsequent lines of the same entry (index 0 is Account Code)
                        rowData.push(q(currentDate));
                        rowData.push(q(currentPC));
                        rowData.push(q(cells[0].innerText.trim())); // Account Code
                        rowData.push(q(cells[1].innerText.trim())); // Account Label
                        rowData.push(q(cells[2].innerText.trim())); // Libelle
                        rowData.push(q(cells[3].innerText.trim())); // Debit
                        rowData.push(q(cells[4].innerText.trim())); // Credit
                    }
                    rows.push(rowData.join(sep));
                });

                const csvContent = '\uFEFF' + rows.join('\n');
                const blob = new Blob([csvContent], {
                    type: 'text/csv;charset=utf-8;'
                });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.setAttribute('download', 'Journal_Comptable_' + new Date().toISOString().slice(0, 10) + '.csv');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        </script>
    @endsection
@endsection
