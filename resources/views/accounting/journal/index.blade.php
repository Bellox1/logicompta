@extends('layouts.accounting')

@section('title', 'Journal des écritures')

@section('styles')
    <style>
        .journal-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .table-premium thead th {
            background-color: #f8f9fc;
            color: #1e293b;
            font-family: 'Manrope';
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            border-bottom: 2px solid #e2e8f0;
            padding: 18px 15px;
            vertical-align: middle;
        }

        .th-highlight {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
            border-bottom: 2px solid var(--primary-color) !important;
        }

        .table-premium tbody td {
            padding: 15px;
            vertical-align: middle;
            font-size: 13px;
            border-bottom: 1px solid #f0f0f0;
        }

        .table-premium tbody tr:hover {
            background-color: #f8f9fc;
        }

        .amount {
            font-family: 'Inter';
            font-weight: 800;
            white-space: nowrap !important;
        }

        .btn-action {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: 0.3s;
        }

        .filter-card {
            background: #fff;
            border-radius: 15px;
            border: 1px solid #eee;
            padding: 25px;
            margin-bottom: 30px;
        }

        .stats-badge {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #eee;
        }

        .sort-icon {
            font-size: 14px !important;
            opacity: 0.5;
        }

        .sort-icon.active {
            opacity: 1;
        }

        .btn-primary-custom {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px;
            font-weight: 700;
            padding: 10px 20px;
            transition: 0.3s;
            border: none;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-primary-custom:hover {
            background-color: #004a6b;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 91, 130, 0.2);
        }
    </style>
@endsection

@section('content')
    @if (request('show_archived') == '1' && request('start_date'))
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center justify-content-between p-3 mb-4"
            style="border-radius: 12px; background: rgba(0, 91, 130, 0.05);">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white rounded-lg d-flex align-items-center justify-content-center mr-3"
                    style="width: 45px; height: 45px;">
                    <span class="material-symbols-outlined">archive</span>
                </div>
                <div>
                    <h5 class="mb-0 font-weight-bold uppercase" style="font-size: 14px;">Archives de l'exercice
                        {{ date('Y', strtotime(request('start_date'))) }}</h5>
                    <p class="mb-0 text-muted small uppercase font-weight-bold tracking-wider">Lecture seule uniquement</p>
                </div>
            </div>
            <a href="{{ route('accounting.archive.show', date('Y', strtotime(request('start_date')))) }}"
                class="btn btn-sm btn-outline-primary font-weight-bold px-3">Retour à l'année
                {{ date('Y', strtotime(request('start_date'))) }}</a>
        </div>
    @endif

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4">
        <div>
            <h1 class="h3 font-weight-bold mb-1" style="font-family: 'Manrope';">Journal des écritures</h1>
            <p class="text-muted small mb-0">Historique complet des opérations comptables</p>
        </div>
        <div class="mt-3 mt-md-0 d-flex flex-wrap">
            <div class="dropdown mr-2 mb-2">
                <button class="btn btn-dark font-weight-bold d-flex align-items-center px-4" type="button"
                    data-toggle="dropdown" style="border-radius: 10px; font-size: 12px;">
                    <span class="material-symbols-outlined mr-2" style="font-size: 20px;">file_download</span> EXPORTS &
                    OPTIONS
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow border-0" style="border-radius: 12px;">
                    <a class="dropdown-item font-weight-bold small py-3" href="{{ route('accounting.journal.export.pdf') }}"
                        target="_blank">
                        <i class="fas fa-file-pdf text-danger mr-2"></i> Exporter en PDF
                    </a>
                    <button class="dropdown-item font-weight-bold small py-3" onclick="exportJournalToCSV()">
                        <i class="fas fa-file-excel text-success mr-2"></i> Exporter en EXCEL (CSV)
                    </button>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item font-weight-bold small py-3 text-primary"
                        href="{{ route('accounting.journal.import') }}">
                        <i class="fas fa-upload mr-2"></i> Importer des données
                    </a>
                </div>
            </div>
            <a href="{{ route('accounting.journal.create') }}"
                class="btn btn-primary-custom d-flex align-items-center mb-2 shadow-sm">
                <span class="material-symbols-outlined mr-2" style="font-size: 20px;">add_circle</span> NOUVELLE ÉCRITURE
            </a>
        </div>
    </div>

    <!-- Filtre par période -->
    <div class="filter-card shadow-sm mb-5">
        <form action="{{ request()->url() }}" method="GET">
            <div class="row align-items-end">
                <div class="col-md-3 mb-3 mb-md-0">
                    <label class="small font-weight-bold text-uppercase text-muted mb-2 tracking-wider"
                        style="font-size: 9px;">Période du</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                        class="form-control font-weight-bold shadow-none"
                        style="border-radius: 10px; height: 45px; border: 1px solid #eee;">
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <label class="small font-weight-bold text-uppercase text-muted mb-2 tracking-wider"
                        style="font-size: 9px;">Au</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                        class="form-control font-weight-bold shadow-none"
                        style="border-radius: 10px; height: 45px; border: 1px solid #eee;">
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="d-flex h-100">
                        <button type="submit"
                            class="btn btn-primary shadow-sm font-weight-bold flex-grow-1 mr-2 d-flex align-items-center justify-content-center"
                            style="border-radius: 12px; height: 45px;">
                            <span class="material-symbols-outlined mr-2" style="font-size: 18px;">sync</span> Actualiser
                        </button>
                        @if (request()->hasAny(['start_date', 'end_date', 'show_archived']))
                            <a href="{{ request()->url() }}"
                                class="btn btn-light border font-weight-bold d-flex align-items-center justify-content-center shadow-none"
                                style="border-radius: 12px; width: 50px; height: 45px;" title="Reset">
                                <span class="material-symbols-outlined">close</span>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-center justify-content-end mb-2 mb-md-0">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="show_archived" value="1" id="switchArchived"
                            class="custom-control-input" {{ request('show_archived') ? 'checked' : '' }}
                            onchange="this.form.submit()">
                        <label class="custom-control-label small font-weight-bold text-muted uppercase" for="switchArchived"
                            style="padding-top: 4px; font-size: 10px;">Archives</label>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @if ($entries->count() > 0)
        <div class="stats-badge mb-5">
            <div class="row align-items-center">
                <div class="col-md-4 text-center text-md-left border-right border-light py-2">
                    <span class="text-uppercase font-weight-bold text-muted d-block mb-1"
                        style="font-size: 10px; letter-spacing: 1px;">Récapitulatif</span>
                    <h6 class="mb-0 font-weight-bold text-dark italic">
                        {{ request('show_archived') ? 'Session archive' : 'Exercice en cours' }}</h6>
                </div>
                <div class="col-md-4 text-center border-right border-light py-2">
                    <span class="text-uppercase font-weight-bold text-muted d-block mb-1"
                        style="font-size: 9px; letter-spacing: 1px;">Total Débit</span>
                    <span
                        class="h4 mb-0 font-weight-bold text-success">{{ number_format($globalTotalDebit, 2, ',', ' ') }}</span>
                </div>
                <div class="col-md-4 text-center py-2">
                    <span class="text-uppercase font-weight-bold text-muted d-block mb-1"
                        style="font-size: 9px; letter-spacing: 1px;">Total Crédit</span>
                    <span
                        class="h4 mb-0 font-weight-bold text-danger">{{ number_format($globalTotalCredit, 2, ',', ' ') }}</span>
                </div>
            </div>
        </div>
    @endif

    <div class="journal-container shadow-sm border mb-5">
        <div class="table-responsive">
            <table class="table table-premium mb-0">
                <thead>
                    <tr>
                        <th class="text-center th-highlight" style="width: 120px;">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'date', 'order' => request('order') == 'asc' ? 'desc' : 'asc']) }}"
                                class="text-dark d-flex align-items-center justify-content-center text-decoration-none">
                                DATE
                                <span
                                    class="material-symbols-outlined ml-1 sort-icon text-dark {{ request('sort', 'date') == 'date' ? 'active' : '' }}">
                                    {{ request('order') == 'asc' && request('sort') == 'date' ? 'arrow_upward' : 'arrow_downward' }}
                                </span>
                            </a>
                        </th>
                        <th class="text-center th-highlight" style="width: 110px;">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'numero_piece', 'order' => request('order') == 'asc' ? 'desc' : 'asc']) }}"
                                class="text-dark d-flex align-items-center justify-content-center text-decoration-none">
                                PIÈCE
                                <span
                                    class="material-symbols-outlined ml-1 sort-icon text-dark {{ request('sort') == 'numero_piece' ? 'active' : '' }}">
                                    {{ request('order') == 'asc' && request('sort') == 'numero_piece' ? 'arrow_upward' : 'arrow_downward' }}
                                </span>
                            </a>
                        </th>
                        <th style="width: 120px;">COMPTE</th>
                        <th>INTITULÉ</th>
                        <th>LIBELLÉ</th>
                        <th class="text-right" style="width: 130px;">DÉBIT</th>
                        <th class="text-right" style="width: 130px;">CRÉDIT</th>
                        <th class="text-center" style="width: 100px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="">
                    @forelse($entries as $entry)
                        @foreach ($entry->lines as $index => $line)
                            <tr>
                                @if ($index === 0)
                                    <td rowspan="{{ $entry->lines->count() }}"
                                        class="text-center font-weight-bold not-italic border-right"
                                        style="background: #fafafa;">
                                        {{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}
                                    </td>
                                    <td rowspan="{{ $entry->lines->count() }}"
                                        class="text-center not-italic border-right">
                                        <a href="{{ route('accounting.journal.show', $entry->id) }}"
                                            class="text-primary font-weight-bold">
                                            {{ str_replace('PC-', '', $entry->numero_piece) }}
                                        </a>
                                    </td>
                                @endif

                                <td class="font-weight-bold text-dark not-italic">
                                    {{ $line->sousCompte?->numero_sous_compte ?? 'N/A' }}</td>
                                <td class="small font-weight-bold text-uppercase text-muted not-italic">
                                    {{ $line->sousCompte?->libelle ?? 'Compte inconnu' }}</td>
                                <td class="text-dark">{{ $line->libelle ?: $entry->libelle }}</td>
                                <td class="text-right amount text-success">{{ number_format($line->debit, 2, ',', ' ') }}
                                </td>
                                <td class="text-right amount text-danger">{{ number_format($line->credit, 2, ',', ' ') }}
                                </td>

                                @if ($index === 0)
                                    <td rowspan="{{ $entry->lines->count() }}"
                                        class="text-center not-italic border-left">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <a href="{{ route('accounting.journal.show', $entry->id) }}"
                                                class="btn-action text-muted hover-primary mr-1" title="Détails">
                                                <span class="material-symbols-outlined"
                                                    style="font-size: 18px;">visibility</span>
                                            </a>
                                            <a href="{{ route('accounting.journal.edit', $entry->id) }}"
                                                class="btn-action text-muted mr-1" title="Modifier"
                                                style="color: #f59e0b !important;">
                                                <span class="material-symbols-outlined"
                                                    style="font-size: 18px;">edit</span>
                                            </a>
                                            <button type="button" class="btn-action text-muted border-0 bg-transparent"
                                                title="Supprimer" style="color: #ef4444 !important;"
                                                onclick="confirmDelete({{ $entry->id }})">
                                                <span class="material-symbols-outlined"
                                                    style="font-size: 18px;">delete</span>
                                            </button>
                                            <form id="delete-entry-{{ $entry->id }}"
                                                action="{{ route('accounting.journal.destroy', $entry->id) }}"
                                                method="POST" class="d-none">
                                                @csrf @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 not-italic text-muted"
                                style="background: #fff; border-radius: 0 0 12px 12px;">
                                <div class="py-5">
                                    <span class="material-symbols-outlined d-block mb-3"
                                        style="font-size: 60px; opacity: 0.1;">search_off</span>
                                    <p class="font-weight-bold h5 mb-1">Aucune écriture trouvée</p>
                                    <p class="small opacity-75">Essayez d'ajuster vos filtres de période ou la recherche.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center py-4">
        {{ $entries->appends(request()->query())->links('pagination::bootstrap-4') }}
    </div>

@endsection

@section('scripts')
    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Supprimer cette écriture ?',
                text: 'Cette action est irréversible.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#005b82',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) document.getElementById('delete-entry-' + id).submit();
            });
        }

        function exportJournalToCSV() {
            const sep = ';';
            const q = (v) => '"' + String(v).replace(/"/g, '""') + '"';
            let rows = [];
            rows.push(['DATE', 'PIECE', 'N° DE COMPTE', 'INTITULE', 'LIBELLES', 'DEBIT', 'CREDIT'].join(sep));
            let currentDate = '';
            let currentPC = '';
            document.querySelectorAll('tbody tr').forEach(tr => {
                const cells = tr.querySelectorAll('td');
                if (cells.length < 5) return;
                let rowData = [];
                if (cells.length >= 7) {
                    currentDate = cells[0].innerText.trim();
                    currentPC = cells[1].innerText.trim();
                    rowData.push(q(currentDate), q(currentPC), q(cells[2].innerText.trim()), q(cells[3].innerText
                        .trim()), q(cells[4].innerText.trim()), q(cells[5].innerText.trim()), q(cells[6]
                        .innerText.trim()));
                } else {
                    rowData.push(q(currentDate), q(currentPC), q(cells[0].innerText.trim()), q(cells[1].innerText
                        .trim()), q(cells[2].innerText.trim()), q(cells[3].innerText.trim()), q(cells[4]
                        .innerText.trim()));
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
