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
            border: 1px solid var(--border-color);
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
@if(request('show_archived') == '1' && request('start_date'))
    <div class="mb-6 bg-primary/10 border-l-4 border-primary p-4 flex items-center justify-between shadow-sm animate-fade-in">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary text-white flex items-center justify-center rounded-xl">
                <i data-lucide="archive" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-lg font-black text-gray-800 uppercase leading-none">Archives de l'exercice {{ date('Y', strtotime(request('start_date'))) }}</h3>
                <p class="text-xs text-gray-700 font-bold uppercase tracking-widest mt-1">Écritures scellées et protégées (Lecture seule)</p>
            </div>
        </div>
        <a href="{{ route('accounting.archive.index') }}" class="text-[10px] font-black uppercase text-primary bg-white border border-primary px-3 py-1.5 rounded-lg hover:bg-primary hover:text-white transition-all">
            Retour au Hub
        </a>
    </div>
@endif

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Journal des écritures</h1>
            <p class="text-sm text-gray-700">Historique complet des opérations comptables</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 no-print">
            <div class="relative group">
                <button id="actions-dropdown-btn"
                    class="flex-1 sm:flex-none inline-flex items-center justify-center px-5 py-2.5 bg-gray-800 text-white font-semibold shadow-sm text-xs gap-2">
                    <i data-lucide="settings-2" class="w-4 h-4"></i>
                    EXPORTS & OPTIONS
                    <i data-lucide="chevron-down" class="w-3 h-3"></i>
                </button>
                <div id="actions-dropdown-menu"
                    class="absolute right-0 mt-2 w-56 bg-white border border-border shadow-xl z-[2000] hidden">
                    <a href="{{ route('accounting.journal.export.pdf') }}" target="_blank"
                        class="flex items-center gap-3 px-4 py-3 text-xs font-bold text-gray-700 hover:bg-gray-50 border-b border-gray-100">
                        <i data-lucide="file-text" class="w-4 h-4 text-red-600"></i>
                        Exporter en PDF
                    </a>
                    <button onclick="exportJournalToCSV()"
                        class="flex items-center gap-3 px-4 py-3 text-xs font-bold text-gray-700 hover:bg-gray-50 border-b border-gray-100 w-full text-left">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-green-600"></i>
                        Exporter en EXCEL
                    </button>
                    <a href="{{ route('accounting.journal.import') }}"
                        class="flex items-center gap-3 px-4 py-3 text-xs font-bold text-gray-700 hover:bg-gray-50">
                        <i data-lucide="upload" class="w-4 h-4 text-indigo-600"></i>
                        Importer données
                    </a>
                </div>
            </div>

            <a href="{{ route('accounting.journal.create') }}"
                class="flex-1 sm:flex-none inline-flex items-center justify-center px-5 py-2.5 bg-primary text-white font-semibold shadow-sm text-xs">
                <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>
                NOUVELLE ÉCRITURE
            </a>
        </div>
    </div>

    <!-- Filtre par période -->
    <form action="{{ request()->url() }}" method="GET"
        class="mb-10 grid grid-cols-1 md:flex md:flex-row md:items-end gap-3 md:gap-5 bg-card-bg p-4 md:p-8 border border-border shadow-sm no-print overflow-hidden max-w-full">
        <div class="w-full md:flex-1">
            <label class="block text-[11px] font-black text-text-secondary mb-2 uppercase tracking-wider px-1 italic">Période du</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" placeholder="JJ/MM/AAAA"
                   class="w-full bg-bg border border-border px-4 py-3 text-sm font-black outline-none focus:border-primary transition-all rounded-xl dark:text-white">
        </div>
        <div class="w-full md:flex-1">
            <label class="block text-[11px] font-black text-text-secondary mb-2 uppercase tracking-wider px-1 italic">Au</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" placeholder="JJ/MM/AAAA"
                   class="w-full bg-bg border border-border px-4 py-3 text-sm font-black outline-none focus:border-primary transition-all rounded-xl dark:text-white">
        </div>
        <div class="w-full md:w-auto flex flex-col md:flex-row gap-3">
            <button type="submit"
                class="w-full md:px-10 py-3 bg-primary text-white text-[11px] font-black uppercase tracking-[0.2em] hover:bg-primary-light transition-all shadow-lg flex items-center justify-center gap-3">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i> Actualiser
            </button>
            @if (request()->hasAny(['start_date', 'end_date', 'show_archived']))
                <a href="{{ request()->url() }}"
                    class="w-full md:px-8 py-3 bg-white text-text-secondary text-[11px] font-black uppercase tracking-[0.2em] hover:bg-slate-50 transition-all flex items-center justify-center gap-2 border border-border rounded-xl">
                    <i data-lucide="x" class="w-4 h-4"></i> Effacer
                </a>
            @endif
        </div>

        <div class="w-full md:w-auto flex items-center h-full pt-6 md:pt-0">
            <label class="flex items-center gap-3 cursor-pointer group">
                <div class="relative">
                    <input type="checkbox" name="show_archived" value="1" {{ request('show_archived') ? 'checked' : '' }} 
                           onchange="this.form.submit()" class="sr-only">
                    <div class="w-10 h-5 bg-gray-200 rounded-full transition-colors group-hover:bg-gray-300 {{ request('show_archived') ? '!bg-primary' : '' }}"></div>
                    <div class="absolute left-1 top-1 w-3 h-3 bg-white rounded-full transition-transform {{ request('show_archived') ? 'translate-x-5' : '' }}"></div>
                </div>
                <span class="text-[10px] uppercase font-bold text-gray-500 tracking-wider">Afficher les archives</span>
            </label>
        </div>
    </form>

    @if($entries->count() > 0)
    <div class="flex items-center justify-between bg-card-bg border border-border px-8 py-5 mb-8 shadow-md rounded-[1.5rem]">
        <div class="flex flex-col">
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Récapitulatif Global</span>
            <span class="text-xs font-bold text-text-secondary italic">{{ request('show_archived') ? 'Session archive' : 'Exercice en cours' }}</span>
        </div>
        <div class="flex items-center gap-12">
            <div class="flex flex-col items-end">
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 opacity-60">Total Débit</span>
                <span class="text-2xl font-black text-emerald-500 dark:text-emerald-400 tabular-nums">{{ number_format($globalTotalDebit, 2, ',', ' ') }}</span>
            </div>
            <div class="w-px h-10 bg-border opacity-50"></div>
            <div class="flex flex-col items-end">
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 opacity-60">Total Crédit</span>
                <span class="text-2xl font-black text-rose-500 dark:text-rose-400 tabular-nums">{{ number_format($globalTotalCredit, 2, ',', ' ') }}</span>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-card-bg border border-border rounded-none shadow-sm mb-8">
        <div class="table-responsive">
            <table class="w-full text-left border-collapse min-w-[1000px] sticky-thead">
                <thead>
                    <tr class="bg-primary text-white">
                        <th class="group p-0 font-bold text-[11px] uppercase tracking-widest text-center border-r border-white/10"
                            style="width: 100px;">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'date', 'order' => request('order') == 'asc' ? 'desc' : 'asc']) }}"
                                class="flex items-center justify-center gap-2 w-full px-4 py-4 text-white hover:bg-white/5 transition-colors">
                                <span>DATE</span>
                                <div class="flex flex-col opacity-20 group-hover:opacity-100 transition-opacity">
                                    <i data-lucide="chevron-up" class="w-3 h-3 {{ request('sort', 'date') == 'date' && request('order') == 'asc' ? 'text-white opacity-100 scale-125' : '' }}"></i>
                                    <i data-lucide="chevron-down" class="w-3 h-3 -mt-1 {{ (request('sort', 'date') == 'date' && request('order', 'desc') == 'desc') ? 'text-white opacity-100 scale-125' : '' }}"></i>
                                </div>
                            </a>
                        </th>
                        <th class="group p-0 font-bold text-[11px] uppercase tracking-widest text-center border-r border-white/10"
                            style="width: 90px;">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'numero_piece', 'order' => request('order') == 'asc' ? 'desc' : 'asc']) }}"
                                class="flex items-center justify-center gap-2 w-full px-4 py-4 text-white hover:bg-white/5 transition-colors">
                                <span>Num PC</span>
                                <div class="flex flex-col opacity-20 group-hover:opacity-100 transition-opacity">
                                    <i data-lucide="chevron-up" class="w-3 h-3 {{ request('sort') == 'numero_piece' && request('order') == 'asc' ? 'text-white opacity-100 scale-125' : '' }}"></i>
                                    <i data-lucide="chevron-down" class="w-3 h-3 -mt-1 {{ request('sort') == 'numero_piece' && request('order') == 'desc' ? 'text-white opacity-100 scale-125' : '' }}"></i>
                                </div>
                            </a>
                        </th>
                        <th class="px-4 py-4 font-bold text-[11px] uppercase tracking-widest border-r border-white/10"
                            style="width: 100px;">N° DE COMPTE</th>
                        <th class="px-4 py-4 font-bold text-[11px] uppercase tracking-widest border-r border-white/10">
                            INTITULE</th>
                        <th class="px-4 py-4 font-bold text-[11px] uppercase tracking-widest border-r border-white/10">
                            LIBELLES</th>
                        <th class="px-4 py-4 font-bold text-[11px] uppercase tracking-widest text-right border-r border-white/10"
                            style="width: 110px;">DEBIT</th>
                        <th class="px-4 py-4 font-bold text-[11px] uppercase tracking-widest text-right border-r border-white/10"
                            style="width: 110px;">CREDIT</th>
                        <th class="px-4 py-4 font-bold text-[11px] uppercase tracking-widest text-center"
                            style="width: 80px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="italic">
                    @forelse($entries as $entry)
                        @foreach ($entry->lines as $index => $line)
                            <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                                @if ($index === 0)
                                    <td rowspan="{{ $entry->lines->count() }}"
                                        class="px-5 py-6 text-sm text-gray-950 font-black text-center align-middle not-italic transition-all border-b-2 border-slate-300 dark:border-slate-700">
                                        {{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}
                                    </td>
                                    <td rowspan="{{ $entry->lines->count() }}"
                                        class="px-5 py-6 text-sm text-center align-middle not-italic transition-all border-b-2 border-slate-300 dark:border-slate-700">
                                        <a href="{{ route('accounting.journal.show', $entry->id) }}"
                                            class="text-primary font-black hover:underline tracking-tighter text-base">
                                            {{ str_replace('PC-', '', $entry->numero_piece) }}
                                        </a>
                                    </td>
                                @endif

                                <td
                                    class="px-5 py-6 text-sm font-bold text-text-main not-italic transition-all {{ $loop->last ? 'border-b-2 border-slate-300 dark:border-slate-700' : '' }}">
                                    {{ $line->sousCompte?->numero_sous_compte ?? 'N/A' }}
                                    @if($line->sousCompte?->trashed())
                                        <span class="ml-1 text-[8px] bg-rose-500 text-white px-1 rounded uppercase">Supprimé</span>
                                    @endif
                                </td>
                                <td
                                    class="px-5 py-6 text-[10px] font-black uppercase tracking-widest text-text-secondary not-italic w-[180px] transition-all {{ $loop->last ? 'border-b-2 border-slate-300 dark:border-slate-700' : '' }}">
                                    {{ $line->sousCompte?->libelle ?? 'Compte inconnu' }}
                                </td>
                                <td
                                    class="px-5 py-6 text-sm text-text-secondary font-medium transition-all {{ $loop->last ? 'border-b-2 border-slate-300 dark:border-slate-700' : '' }}">
                                    {{ $line->libelle ?: $entry->libelle }}
                                </td>
                                <td
                                    class="px-5 py-6 text-sm text-right font-black text-text-main not-italic whitespace-nowrap transition-all {{ $loop->last ? 'border-b-2 border-slate-300 dark:border-slate-700' : '' }}">
                                    {{ number_format($line->debit, 2, ',', ' ') }}
                                </td>
                                <td
                                    class="px-5 py-6 text-sm text-right font-black text-text-main not-italic whitespace-nowrap transition-all {{ $loop->last ? 'border-b-2 border-slate-300 dark:border-slate-700' : '' }}">
                                    {{ number_format($line->credit, 2, ',', ' ') }}
                                </td>

                                @if ($index === 0)
                                    <td rowspan="{{ $entry->lines->count() }}"
                                        class="px-4 py-4 text-center align-middle not-italic transition-all border-b-2 border-slate-300 dark:border-slate-700">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('accounting.journal.show', $entry->id) }}"
                                                class="p-1.5 text-slate-400 hover:text-primary transition-all rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
                                                title="Détails">
                                                <i data-lucide="eye" class="w-5 h-5"></i>
                                            </a>
                                            <a href="{{ route('accounting.journal.edit', $entry->id) }}"
                                                class="p-1.5 text-gray-400 hover:text-amber-500 transition-all rounded-lg hover:bg-gray-100"
                                                title="Modifier">
                                                <i data-lucide="edit-3" class="w-5 h-5"></i>
                                            </a>
                                            <form id="delete-entry-{{ $entry->id }}" action="{{ route('accounting.journal.destroy', $entry->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" 
                                                    onclick="Swal.fire({
                                                        title: 'Supprimer cette écriture ?',
                                                        text: 'L\'opération sera définitivement supprimée.',
                                                        icon: 'warning',
                                                        showCancelButton: true,
                                                        confirmButtonColor: '#005b82',
                                                        cancelButtonColor: '#94a3b8',
                                                        confirmButtonText: 'Oui, supprimer',
                                                        cancelButtonText: 'Annuler'
                                                    }).then((result) => {
                                                        if (result.isConfirmed) {
                                                            document.getElementById('delete-entry-{{ $entry->id }}').submit();
                                                        }
                                                    })"
                                                    class="p-1.5 text-gray-400 hover:text-red-500 transition-all rounded-lg hover:bg-gray-100" title="Supprimer">
                                                    <i data-lucide="x" class="w-5 h-5"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('accounting.journal.show.pdf', $entry->id) }}"
                                                target="_blank"
                                                class="p-1.5 text-gray-400 hover:text-red-500 transition-all rounded-lg hover:bg-gray-100"
                                                title="PDF">
                                                <i data-lucide="file-text" class="w-5 h-5"></i>
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
