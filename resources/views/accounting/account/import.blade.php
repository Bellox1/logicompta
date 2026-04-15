@extends('layouts.accounting')

@section('title', 'Importer des sous-comptes')

@section('styles')
    <style>
        .drop-zone-premium {
            height: 250px;
            border: 2px dashed #cbd5e1;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #fff;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        }

        .drop-zone-premium:hover {
            border-color: var(--primary-color);
            background: rgba(0, 91, 130, 0.02);
            transform: translateY(-5px);
        }

        .premium-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            overflow: hidden;
            padding: 30px;
        }

        .premium-card-no-padding {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            overflow: hidden;
        }

        .import-table thead th {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            padding: 15px;
            border-bottom: 2px solid #f1f5f9;
            background: #f8fafc;
        }

        .import-table tbody td {
            font-size: 13px;
            padding: 12px 15px;
            font-weight: 600;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
        }
    </style>
@endsection

@section('content')
    <div class="mb-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <h1 class="h3 font-weight-bold text-dark mb-1" style="font-family: 'Manrope';">Import de Sous-comptes</h1>
            <p class="text-muted small font-weight-bold uppercase tracking-wider">Chargement massif et rapide de vos auxiliaires via fichier CSV.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('accounting.account.index') }}" class="btn btn-white btn-sm px-4 py-2 font-weight-bold border rounded-lg shadow-sm text-dark align-items-center d-flex">
                <span class="material-symbols-outlined mr-1" style="font-size: 18px;">close</span> QUITTER L'IMPORT
            </a>
        </div>
    </div>

    <form id="import-form" action="{{ route('accounting.account.import.preview') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <!-- Drop Zone -->
                <div class="mb-5 position-relative">
                    <input type="file" name="file" id="file" required accept=".csv,.txt"
                        class="position-absolute w-100 h-100 cursor-pointer"
                        onchange="updateFileName(this)" style="top:0; left:0; z-index: 10; opacity: 0;">
                    
                    <div id="drop-zone" class="drop-zone-premium">
                        <span class="material-symbols-outlined mb-3 text-primary" style="font-size: 60px;">cloud_upload</span>
                        <h4 id="file-name" class="font-weight-bold text-dark mb-2" style="font-family: 'Manrope';">Sélectionnez votre fichier .CSV</h4>
                        <p class="small text-muted font-weight-bold text-uppercase tracking-widest mb-0">ou glissez-déposez le fichier ici</p>
                    </div>
                </div>

                
                <!-- Preview (Dynamic) -->
                <div id="js-preview-container" class="d-none mb-5 animate-fade-in">
                    <div class="premium-card p-0">
                        <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                            <h6 class="font-weight-bold text-primary text-uppercase mb-0" style="letter-spacing: 1px;">Aperçu des données</h6>
                            <span id="file-row-count" class="badge badge-primary-light font-weight-bold text-primary px-3 py-2 rounded-pill bg-light"></span>
                        </div>
                        <div class="table-responsive">
                            <table class="table import-table mb-0">
                                <thead id="js-preview-header"></thead>
                                <tbody id="js-preview-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Structure Illustration -->
                <div id="format-illustration">
                    <div class="premium-card p-0">
                        <div class="p-4 border-bottom">
                            <h6 class="font-weight-bold text-dark text-uppercase mb-0" style="letter-spacing: 1px;">Structure de fichier attendue</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table import-table mb-0">
                                <thead>
                                    <tr>
                                        <th>NUMÉRO (COMPTE)</th>
                                        <th>LIBELLÉ / INTITULÉ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-primary font-weight-bold font-mono">411101</td>
                                        <td class="font-weight-bold">Client DUPONT SAS</td>
                                    </tr>
                                    <tr>
                                        <td class="text-primary font-weight-bold font-mono">521201</td>
                                        <td class="font-weight-bold">BANQUE BOA BENIN</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 bg-light">
                            <p class="small text-muted mb-0 font-weight-bold d-flex align-items-center">
                                <span class="material-symbols-outlined mr-2 text-primary">info</span>
                                Séparateur point-virgule (;) ou virgule (,). Le compte parent est identifié automatiquement.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="premium-card sticky-top" style="top: 20px;">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-light text-primary rounded-lg d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px;">
                            <span class="material-symbols-outlined">checklist</span>
                        </div>
                        <h6 class="font-weight-bold text-uppercase text-dark mb-0" style="letter-spacing: 1px;">Validation</h6>
                    </div>
                    
                    <p class="small text-muted font-weight-bold mb-5 leading-relaxed">
                        À l'étape suivante, vous pourrez vérifier l'intégrité de chaque ligne et corriger les erreurs de rattachement avant l'enregistrement définitif.
                    </p>
                    
                    <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold rounded-lg shadow-sm text-uppercase d-flex justify-content-center align-items-center" style="letter-spacing: 1px;">
                        Continuer l'aperçu <span class="material-symbols-outlined ml-2" style="font-size: 18px;">arrow_forward</span>
                    </button>
                    
                    <hr class="my-4">
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
                dropZone.style.borderColor = '#0062cc';
                dropZone.style.background = '#f1f5f9';
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const text = e.target.result;
                    const lines = text.split(/\r?\n/).filter(line => line.trim() !== "");
                    if (lines.length > 0) {
                        const firstLine = lines[0];
                        const delimiter = firstLine.split(';').length >= firstLine.split(',').length ? ';' : ',';
                        
                        const header = lines[0].split(delimiter);
                        const rows = lines.slice(1, 6); // Top 5
                        
                        let headerHtml = "<tr>";
                        headerHtml += header.map(h => `<th>${h.trim().replace(/^"(.*)"$/, '$1')}</th>`).join('');
                        headerHtml += "</tr>";
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

