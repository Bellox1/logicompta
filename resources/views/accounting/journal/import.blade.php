@extends('layouts.accounting')

@section('title', 'Importer des écritures')

@section('styles')
    <style>
        .drop-zone-premium {
            height: 260px;
            border: 2px dashed #cbd5e1;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding-top: 40px;
            background: #fff;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .drop-zone-premium .material-symbols-outlined {
            display: block;
            margin: 0 auto 15px;
        }

        .drop-zone-premium:hover {
            border-color: var(--primary-color);
            background: rgba(0, 91, 130, 0.02);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 91, 130, 0.08);
        }

        .premium-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            overflow: hidden;
        }

        .import-table {
            border-collapse: collapse;
            width: 100%;
        }

        .import-table thead th {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            padding: 12px 15px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            white-space: nowrap;
        }

        .import-table tbody td {
            font-size: 12px;
            padding: 10px 15px;
            font-weight: 600;
            color: #1e293b;
            border: 1px solid #cbd5e1;
            white-space: nowrap;
        }

        .check-feature {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            padding: 8px 0;
        }

        .check-feature .material-symbols-outlined {
            font-size: 16px;
            color: #22c55e;
        }
    </style>
@endsection

@section('content')
    <div class="mb-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <h1 class="h3 font-weight-bold text-dark mb-1" style="font-family: 'Manrope';">Import du Journal</h1>
            <p class="text-muted small font-weight-bold uppercase tracking-wider">Chargement massif via fichier CSV</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('accounting.journal.index') }}" class="btn btn-white btn-sm px-4 py-2 font-weight-bold border rounded-lg shadow-sm text-dark d-flex align-items-center">
                <span class="material-symbols-outlined mr-1" style="font-size: 18px;">close</span> QUITTER L'IMPORT
            </a>
        </div>
    </div>

    @if(session('needs_reindex'))
        <div class="alert border-0 shadow-sm mb-4 d-flex align-items-center justify-content-between" style="border-radius: 16px; background: #fff5f5; border-left: 4px solid #ef4444 !important;">
            <div class="d-flex align-items-center">
                <span class="material-symbols-outlined text-danger mr-3">warning</span>
                <p class="small font-weight-bold text-danger mb-0">Des doublons ont été détectés. Cliquez pour générer de nouveaux numéros.</p>
            </div>
            <form action="{{ route('accounting.journal.import.preview') }}" method="POST">
                @csrf
                <input type="hidden" name="force_reindex" value="1">
                <button type="submit" class="btn btn-danger btn-sm font-weight-bold px-4 rounded-lg d-flex align-items-center">
                    <span class="material-symbols-outlined mr-1" style="font-size: 16px;">refresh</span> Réindexer et Importer
                </button>
            </form>
        </div>
    @endif

    <form id="import-form" action="{{ route('accounting.journal.import.preview') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <!-- Zone de dépôt -->
                <div class="mb-4 position-relative">
                    <input type="file" name="file" id="file" required accept=".csv,.txt"
                        class="position-absolute w-100 h-100 cursor-pointer"
                        onchange="updateFileName(this)" style="top:0; left:0; z-index: 10; opacity: 0;">
                    <div id="drop-zone" class="drop-zone-premium">
                        <span class="material-symbols-outlined text-primary mb-3" style="font-size: 64px;">upload_file</span>
                        <h4 id="file-name" class="font-weight-bold text-dark mb-2" style="font-family: 'Manrope';">Sélectionnez votre fichier CSV</h4>
                        <p class="small text-muted font-weight-bold text-uppercase tracking-widest mb-0">ou déposez-le directement ici</p>
                    </div>
                </div>

                <!-- Aperçu JS (après sélection de fichier) -->
                <div id="js-preview-container" class="d-none mb-4">
                    <div class="premium-card">
                        <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                            <h6 class="font-weight-bold text-primary text-uppercase mb-0" style="letter-spacing: 1px;">Aperçu du fichier</h6>
                            <span id="file-row-count" class="badge bg-light text-muted font-weight-bold px-3 py-2 rounded-pill" style="font-size: 10px;"></span>
                        </div>
                        <div class="table-responsive">
                            <table class="table import-table mb-0">
                                <thead><tr id="js-preview-header"></tr></thead>
                                <tbody id="js-preview-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Aperçu PHP (données en attente si conflit) -->
                @if(session('pending_import'))
                    <div id="php-preview-container" class="mb-4">
                        <div class="premium-card">
                            <div class="d-flex justify-content-between align-items-center p-4 border-bottom" style="background: #fff5f5;">
                                <h6 class="font-weight-bold text-danger text-uppercase mb-0" style="letter-spacing: 1px;">Données en attente</h6>
                                <span class="badge bg-danger font-weight-bold px-3 py-2 rounded-pill" style="font-size: 10px;">{{ count(session('pending_import')) }} lignes</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table import-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>PIÈCE</th><th>DATE</th><th>COMPTE</th>
                                            <th class="text-right">DÉBIT</th><th class="text-right">CRÉDIT</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(array_slice(session('pending_import'), 0, 15) as $row)
                                            <tr>
                                                <td class="font-weight-bold text-danger">{{ $row['piece'] }}</td>
                                                <td class="text-muted">{{ $row['date'] }}</td>
                                                <td class="font-weight-bold">{{ $row['account'] }}</td>
                                                <td class="text-right">{{ number_format($row['debit'], 2, ',', ' ') }}</td>
                                                <td class="text-right font-weight-bold text-danger">{{ number_format($row['credit'], 2, ',', ' ') }}</td>
                                            </tr>
                                        @endforeach
                                        @if(count(session('pending_import')) > 15)
                                            <tr>
                                                <td colspan="5" class="text-center text-muted small font-weight-bold italic py-3 bg-light">
                                                    ... et {{ count(session('pending_import')) - 15 }} autres lignes ...
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <script>document.addEventListener('DOMContentLoaded', () => { document.getElementById('format-illustration').classList.add('d-none'); });</script>
                @endif

                <!-- Illustration du format -->
                <div id="format-illustration">
                    <div class="premium-card">
                        <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                            <h6 class="font-weight-bold text-dark text-uppercase mb-0" style="letter-spacing: 1px;">Structure attendue</h6>
                            <span class="badge bg-light text-muted font-weight-bold" style="font-size: 10px;">Format CSV (point-virgule)</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table import-table mb-0">
                                <thead>
                                    <tr>
                                        <th>DATE</th><th>PIÈCE</th><th>JOURNAL</th>
                                        <th>COMPTE</th><th>LIBELLÉ</th>
                                        <th class="text-right">DÉBIT</th><th class="text-right">CRÉDIT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>01/04/2026</td>
                                        <td rowspan="2" class="font-weight-bold text-primary align-middle">PC001</td>
                                        <td>AC</td><td>512000</td>
                                        <td>Client Dupont</td>
                                        <td class="text-right">1 500,00</td>
                                        <td class="text-right">0,00</td>
                                    </tr>
                                    <tr>
                                        <td>01/04/2026</td>
                                        <!-- PC001 fusionné -->
                                        <td>AC</td><td>707000</td>
                                        <td>Vente marchandises</td>
                                        <td class="text-right">0,00</td>
                                        <td class="text-right">1 500,00</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 bg-light">
                            <p class="small text-muted mb-0 font-weight-bold d-flex align-items-start text-justify">
                                <span class="material-symbols-outlined mr-2 text-primary" style="font-size: 18px;">info</span>
                                <span>Note : Le numéro de pièce (ex: PC001) doit être répété sur toutes les lignes appartenant à la même écriture pour qu'elles soient regroupées automatiquement.</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne droite -->
            <div class="col-lg-4">
                <div class="premium-card p-4 sticky-top" style="top: 20px;">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-light text-primary rounded-lg d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px; flex-shrink: 0;">
                            <span class="material-symbols-outlined">checklist</span>
                        </div>
                        <h6 class="font-weight-bold text-uppercase text-dark mb-0" style="letter-spacing: 1px;">Importation</h6>
                    </div>

                    <p class="small text-muted font-weight-bold mb-4">
                        Assurez-vous que votre fichier est équilibré. Une erreur sera affichée pour chaque pièce dont le total débit n'est pas égal au total crédit.
                    </p>

                    <div class="mb-4">
                        <div class="check-feature">
                            <span class="material-symbols-outlined">check_circle</span> Détection auto des colonnes
                        </div>
                        <div class="check-feature">
                            <span class="material-symbols-outlined">check_circle</span> Regroupement par pièce
                        </div>
                        <div class="check-feature">
                            <span class="material-symbols-outlined">check_circle</span> Vérification de l'équilibre
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold rounded-lg shadow-sm d-flex align-items-center justify-content-center" style="letter-spacing: 1px;">
                        Démarrer l'importation
                        <span class="material-symbols-outlined ml-2" style="font-size: 18px;">bolt</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        function updateFileName(input) {
            const fileNameDisplay = document.getElementById('file-name');
            const dropZone = document.getElementById('drop-zone');
            const jsPreviewContainer = document.getElementById('js-preview-container');
            const formatIllustration = document.getElementById('format-illustration');

            if (input.files.length > 0) {
                const name = input.files[0].name.toUpperCase();
                fileNameDisplay.innerHTML = `<span class="text-primary">${name}</span>`;
                dropZone.style.borderColor = 'var(--primary-color)';
                dropZone.style.background = 'rgba(0, 91, 130, 0.03)';

                const reader = new FileReader();
                reader.onload = function(e) {
                    const text = e.target.result;
                    const lines = text.split(/\r?\n/).filter(line => line.trim() !== "");
                    if (lines.length > 0) {
                        const firstLine = lines[0];
                        const delimiter = firstLine.split(';').length >= firstLine.split(',').length ? ';' : ',';

                        const header = lines[0].split(delimiter);
                        const rows = lines.slice(1, 10);

                        const headerHtml = header.map(h => `<th>${h.trim().replace(/^"(.*)"$/, '$1')}</th>`).join('');
                        document.getElementById('js-preview-header').innerHTML = headerHtml;

                        let bodyHtml = "";
                        rows.forEach(row => {
                            const cols = row.split(delimiter);
                            bodyHtml += `<tr>${cols.map(c => `<td>${c.trim().replace(/^"(.*)"$/, '$1')}</td>`).join('')}</tr>`;
                        });
                        document.getElementById('js-preview-body').innerHTML = bodyHtml;
                        document.getElementById('file-row-count').innerText = `${lines.length - 1} lignes détectées`;

                        jsPreviewContainer.classList.remove('d-none');
                        formatIllustration.classList.add('d-none');
                    }
                };
                reader.readAsText(input.files[0]);
            }
        }
    </script>
@endsection
