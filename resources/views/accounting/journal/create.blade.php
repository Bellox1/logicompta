@extends('layouts.accounting')

@section('title', 'Saisie d\'écriture')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-5 gap-3">
        <div>
            <h1 class="h2 font-weight-bold text-dark mb-1" style="font-family: 'Manrope';">Saisie d'écriture</h1>
            <p class="text-muted small font-weight-bold mb-0 italic">Enregistrez vos flux financiers avec précision</p>
        </div>
        <div class="d-flex align-items-center">
            <a href="{{ route('accounting.journal.index') }}" class="btn btn-white shadow-sm border font-weight-bold d-flex align-items-center px-4 mr-2" style="border-radius: 12px; font-size: 12px;">
                <span class="material-symbols-outlined mr-2" style="font-size: 18px;">history</span> Historique
            </a>
            <a href="{{ route('accounting.journals-settings.index') }}" class="btn btn-primary font-weight-bold d-flex align-items-center px-4 shadow-sm" style="border-radius: 12px; font-size: 12px;">
                <span class="material-symbols-outlined mr-2" style="font-size: 18px;">settings</span> Paramètres
            </a>
        </div>
    </div>

    <!-- Zone Import OCR -->
    <div class="mb-5 p-4 bg-light border-dashed-primary rounded-lg shadow-sm position-relative overflow-hidden" id="ocr-dropzone" style="border: 2px dashed rgba(0, 91, 130, 0.3); border-radius: 20px;">
        <div class="d-flex flex-column flex-md-row align-items-center position-relative" style="z-index: 2;">
            <div class="bg-primary text-white d-flex align-items-center justify-content-center mr-md-4 mb-3 mb-md-0 shadow-lg cursor-pointer transition-all hover-scale" id="ocr-icon-zone" style="width: 55px; height: 55px; border-radius: 15px;">
                <span class="material-symbols-outlined" style="font-size: 28px;">document_scanner</span>
            </div>
            <div class="flex-grow-1 cursor-pointer text-center text-md-left mr-md-4" id="ocr-text-zone">
                <h5 class="text-primary font-weight-bold text-uppercase mb-1" style="font-family: 'Manrope'; letter-spacing: 0.5px;">Import Facture par IA</h5>
                <p class="text-muted small font-weight-bold italic mb-0">Glissez une facture ou cliquez pour remplir les champs automatiquement</p>
            </div>

            <div class="d-flex align-items-center bg-white p-1 rounded-pill shadow-sm mb-3 mb-md-0">
                <button type="button" id="btn-tesseract" class="ocr-service-btn btn btn-sm rounded-pill px-3 font-weight-bold btn-primary" data-service="tesseract" style="font-size: 10px;">
                    <span class="material-symbols-outlined mr-1" style="font-size: 14px; vertical-align: middle;">memory</span> Local
                </button>
                <button type="button" id="btn-mindee" class="ocr-service-btn btn btn-sm rounded-pill px-3 font-weight-bold text-muted border-0" data-service="mindee" style="font-size: 10px;">
                    <span class="material-symbols-outlined mr-1" style="font-size: 14px; vertical-align: middle;">cloud</span> Mindee
                </button>
            </div>

            <div id="ocr-status" class="d-none ml-md-3">
                <div class="d-flex align-items-center text-primary font-weight-bold small pulse">
                    <div class="spinner-border spinner-border-sm mr-2" role="status"></div> Analyse...
                </div>
            </div>
        </div>
        <input type="file" id="ocr-file-input" class="d-none" accept="image/*,application/pdf">
    </div>

    <!-- Modal Debug OCR -->
    <div id="ocr-debug-modal" class="d-none position-fixed w-100 h-100" style="top:0; left:0; z-index: 2000; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px);">
        <div class="d-flex align-items-center justify-content-center h-100 p-3">
            <div class="card border-0 shadow-lg w-100" style="max-width: 800px; border-radius: 25px; overflow: hidden;">
                <div class="card-header bg-white border-0 p-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white rounded-lg d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px;">
                            <span class="material-symbols-outlined">bug_report</span>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold uppercase tracking-wider" style="font-size: 14px;">Debug OCR</h5>
                            <p id="ocr-debug-service" class="mb-0 small text-muted font-weight-bold italic"></p>
                        </div>
                    </div>
                    <button class="btn btn-light rounded-circle d-flex align-items-center justify-content-center" onclick="document.getElementById('ocr-debug-modal').classList.add('d-none')" style="width: 40px; height: 40px;">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="card-body p-4 overflow-auto" style="max-height: 70vh;">
                    <p class="text-uppercase font-weight-bold text-muted mb-2" style="font-size: 10px; letter-spacing: 1px;">Données extraites</p>
                    <div class="table-responsive mb-4 shadow-sm border rounded">
                        <table class="table table-sm mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 px-3 py-2 small font-weight-bold uppercase">Champ</th>
                                    <th class="border-0 px-3 py-2 small font-weight-bold uppercase">Valeur</th>
                                </tr>
                            </thead>
                            <tbody id="ocr-debug-table" class="small"></tbody>
                        </table>
                    </div>
                    
                    <p class="text-uppercase font-weight-bold text-muted mb-2" style="font-size: 10px; letter-spacing: 1px;">Texte brut (Modifiable pour l'IA)</p>
                    <textarea id="ocr-debug-rawtext-area" class="form-control font-family-mono mb-4 bg-light border-0 p-3 small" rows="5" style="border-radius: 12px;"></textarea>
                    
                    <p class="text-uppercase font-weight-bold text-muted mb-2" style="font-size: 10px; letter-spacing: 1px;">JSON complet</p>
                    <pre id="ocr-debug-json" class="bg-dark text-success p-3 rounded small overflow-auto" style="max-height: 200px; border-radius: 12px;"></pre>
                </div>
                <div class="card-footer bg-light border-0 p-4 d-flex justify-content-between align-items-center">
                    <button type="button" id="btn-ai-process-raw" onclick="processWithAI()" class="btn btn-dark font-weight-bold px-4 d-flex align-items-center" style="border-radius: 12px;">
                        <span class="material-symbols-outlined mr-2 text-warning">auto_awesome</span> Générer via IA
                    </button>
                    <button onclick="document.getElementById('ocr-debug-modal').classList.add('d-none')" class="btn btn-outline-secondary font-weight-bold px-4" style="border-radius: 12px;">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('accounting.journal.store') }}" method="POST" id="journalform">
        @csrf

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm p-4 mb-4" style="border-radius: 15px;">
                <h6 class="font-weight-bold uppercase small mb-2"><span class="material-symbols-outlined align-middle mr-1" style="font-size: 18px;">warning</span> Erreurs détectées :</h6>
                <ul class="mb-0 small font-weight-bold">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="small text-uppercase font-weight-bold text-muted mb-2">N° Pièce</label>
                        <input type="text" id="piece-number" name="numero_piece" value="{{ $nextPieceNumber }}" readonly class="form-control font-weight-bold bg-light border-0" style="border-radius: 12px; height: 50px;">
                        <p id="archive-warning" class="d-none mt-2 small font-weight-bold text-warning mb-0">
                            <span id="archive-warning-text">⚠️ Archivage automatique détecté</span>
                        </p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="small text-uppercase font-weight-bold text-muted mb-2">Journal</label>
                        <select id="journal-select" name="journal_id" required class="form-control font-weight-bold custom-select-premium" style="border-radius: 12px; height: 50px;">
                            @foreach ($journals as $journal)
                                <option value="{{ $journal->id }}" {{ old('journal_id', $selectedJournalId) == $journal->id ? 'selected' : '' }}>{{ $journal->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="small text-uppercase font-weight-bold text-muted mb-2">Date</label>
                        <input type="date" id="entry-date" name="date" value="{{ old('date', date('Y-m-d')) }}" required class="form-control font-weight-bold" style="border-radius: 12px; height: 50px;">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="small text-uppercase font-weight-bold text-muted mb-2">Libellé Général</label>
                        <textarea name="libelle" id="libelle-general" placeholder="Détails de l'opération..." required class="form-control font-weight-bold border-0 bg-light-soft rounded-lg small py-2" style="min-height: 80px; height: auto; resize: vertical;" rows="3">{{ old('libelle') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm overflow-hidden mb-5" style="border-radius: 20px;">
            <div class="card-header bg-white border-bottom p-4 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 font-weight-bold text-uppercase tracking-wider text-muted italic" style="font-size: 12px;">Lignes d'enregistrements</h6>
                <button type="button" id="add-line" class="btn btn-link text-primary font-weight-bold p-0 d-flex align-items-center text-decoration-none h-zoom">
                    <span class="material-symbols-outlined mr-1">add_circle</span> Ajouter une ligne
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-borderless mb-0">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="px-4 py-3 small font-weight-bold text-uppercase text-muted" style="width: 35%;">Compte</th>
                            <th class="px-4 py-3 small font-weight-bold text-uppercase text-muted text-right" style="width: 15%; white-space: nowrap;">Débit</th>
                            <th class="px-4 py-3 small font-weight-bold text-uppercase text-muted text-right" style="width: 15%; white-space: nowrap;">Crédit</th>
                            <th class="px-4 py-3 small font-weight-bold text-uppercase text-muted">Libellé ligne</th>
                            <th class="px-4 py-3" style="width: 60px;"></th>
                        </tr>
                    </thead>
                    <tbody id="lines-body" class="bg-white">
                        @php $oldLines = old('lines', [null, null]); @endphp
                        @foreach ($oldLines as $index => $oldLine)
                            <tr class="line-row border-bottom">
                                <td class="p-3">
                                    <select name="lines[{{ $index }}][sous_compte_id]" required class="form-control font-weight-bold select2-account rounded-lg border-0 bg-light-soft" style="height: 45px;">
                                        <option value="">Choisir un sous-compte...</option>
                                        @foreach ($accounts->groupBy(fn($sc) => $sc->account->code_compte . ' - ' . $sc->account->libelle) as $parentLabel => $subAccounts)
                                            <optgroup label="{{ $parentLabel }}">
                                                @foreach ($subAccounts as $sc)
                                                    <option value="{{ $sc->id }}" {{ isset($oldLine['sous_compte_id']) && $oldLine['sous_compte_id'] == $sc->id ? 'selected' : '' }}>{{ $sc->numero_sous_compte }} - {{ $sc->libelle }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="p-3">
                                    <input type="text" inputmode="decimal" name="lines[{{ $index }}][debit]" class="debit-input form-control font-weight-bold text-right border-0 bg-light-soft rounded-lg" style="height: 45px;" value="{{ isset($oldLine['debit']) && $oldLine['debit'] != 0 ? rtrim(rtrim(number_format((float)$oldLine['debit'], 2, '.', ''), '0'), '.') : '' }}" placeholder="0">
                                </td>
                                <td class="p-3">
                                    <input type="text" inputmode="decimal" name="lines[{{ $index }}][credit]" class="credit-input form-control font-weight-bold text-right border-0 bg-light-soft rounded-lg" style="height: 45px;" value="{{ isset($oldLine['credit']) && $oldLine['credit'] != 0 ? rtrim(rtrim(number_format((float)$oldLine['credit'], 2, '.', ''), '0'), '.') : '' }}" placeholder="0">
                                </td>
                                <td class="p-3">
                                    <textarea name="lines[{{ $index }}][libelle]" placeholder="Facultatif" rows="2" class="form-control font-weight-bold border-0 bg-light-soft rounded-lg small py-2" style="min-height: 60px;">{{ $oldLine['libelle'] ?? '' }}</textarea>
                                </td>
                                <td class="p-3 text-center">
                                    @if ($index >= 2)
                                        <button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove(); calculate();">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-light p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="d-flex flex-nowrap align-items-center" style="gap: 3rem;">
                            <div class="text-nowrap">
                                <span class="small font-weight-bold text-muted text-uppercase d-block mb-1 italic" style="font-size: 10px;">Total Débit</span>
                                <div class="h4 font-weight-bold mb-0 text-dark"><span id="total-debit">0</span> <span class="small text-muted opacity-50">XOF</span></div>
                            </div>
                            <div class="text-nowrap">
                                <span class="small font-weight-bold text-muted text-uppercase d-block mb-1 italic" style="font-size: 10px;">Total Crédit</span>
                                <div class="h4 font-weight-bold mb-0 text-dark"><span id="total-credit">0</span> <span class="small text-muted opacity-50">XOF</span></div>
                            </div>
                            <div id="balance-container" class="text-nowrap">
                                <span class="small font-weight-bold text-muted text-uppercase d-block mb-1 italic" style="font-size: 10px;">État d'équilibre</span>
                                <div id="balance-status" class="font-weight-bold text-danger uppercase small tracking-wider shadow-none">Déséquilibre détecté</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mt-3 mt-lg-0 d-flex justify-content-lg-end">
                        <button type="submit" id="submit-btn" disabled class="btn btn-primary btn-lg font-weight-bold px-5 py-3 shadow-lg d-flex align-items-center justify-content-center" style="border-radius: 15px; text-transform: uppercase; letter-spacing: 1px; font-size: 13px;">
                            <span class="material-symbols-outlined mr-2">check_circle</span> Valider l'Écriture
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <style>
        .border-dashed-primary { border: 2px dashed rgba(0, 91, 130, 0.3) !important; }
        .bg-light-soft { background-color: #f8f9fc !important; }
        .hover-scale:hover { transform: scale(1.05); }
        .h-zoom:hover { transform: scale(1.02); }
        .font-family-mono { font-family: 'Courier New', Courier, monospace; }
        .gap-4 { gap: 1.5rem !important; }
        .magical-balance { color: #8B4513; font-weight: 900; }
        .pulse { animation: pulse-animation 2s infinite; }
        @keyframes pulse-animation { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
        @media (max-width: 768px) {
            .select2-container { min-width: 250px !important; }
            .debit-input, .credit-input { min-width: 150px !important; }
            textarea[name$="[libelle]"] { min-width: 300px !important; }
            .line-row td { padding: 15px 10px !important; }
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let lineCount = {{ count($oldLines) }};
            const body = document.getElementById('lines-body');
            const totalDebitEl = document.getElementById('total-debit');
            const totalCreditEl = document.getElementById('total-credit');
            const balanceStatusEl = document.getElementById('balance-status');
            const submitBtn = document.getElementById('submit-btn');

            let equilibre_first = false;
            const STORAGE_KEY = 'journal_draft_entry';

            function saveDraft() {
                const draft = {
                    journal_id: document.querySelector('select[name="journal_id"]').value,
                    date: document.querySelector('input[name="date"]').value,
                    libelle: document.querySelector('input[name="libelle"]').value,
                    lines: []
                };
                document.querySelectorAll('.line-row').forEach(row => {
                    const scSelect = row.querySelector('select[name$="[sous_compte_id]"]');
                    const debitInput = row.querySelector('.debit-input');
                    const creditInput = row.querySelector('.credit-input');
                    const libelleArea = row.querySelector('textarea[name$="[libelle]"]');
                    if (scSelect) draft.lines.push({ account_id: scSelect.value, debit: debitInput.value, credit: creditInput.value, libelle: libelleArea.value });
                });
                localStorage.setItem(STORAGE_KEY, JSON.stringify(draft));
            }

            function loadDraft() {
                const saved = localStorage.getItem(STORAGE_KEY);
                if (!saved) return;
                const draft = JSON.parse(saved);
                if (draft.journal_id) document.querySelector('select[name="journal_id"]').value = draft.journal_id;
                if (draft.date) document.querySelector('input[name="date"]').value = draft.date;
                if (draft.libelle) document.querySelector('input[name="libelle"]').value = draft.libelle;
                if (draft.lines && draft.lines.length > 0) {
                    const rows = document.querySelectorAll('.line-row');
                    draft.lines.forEach((line, index) => {
                        let row = index < rows.length ? rows[index] : (document.getElementById('add-line').click(), document.querySelectorAll('.line-row')[index]);
                        if (!row) return;
                        const scSelect = row.querySelector('select[name$="[sous_compte_id]"]');
                        if (scSelect) scSelect.value = line.account_id;
                        row.querySelector('.debit-input').value = line.debit;
                        row.querySelector('.credit-input').value = line.credit;
                        row.querySelector('textarea[name$="[libelle]"]').value = line.libelle;
                    });
                }
                calculate();
            }

            function calculate() {
                let totalDebit = 0, totalCredit = 0;
                document.querySelectorAll('.debit-input').forEach(i => totalDebit += parseFloat(i.value || 0));
                document.querySelectorAll('.credit-input').forEach(i => totalCredit += parseFloat(i.value || 0));
                if (totalDebitEl) totalDebitEl.innerText = totalDebit.toLocaleString('fr-FR', { maximumFractionDigits: 2 });
                if (totalCreditEl) totalCreditEl.innerText = totalCredit.toLocaleString('fr-FR', { maximumFractionDigits: 2 });
                const diff = totalDebit - totalCredit;
                const absDiff = Math.abs(diff);
                document.querySelectorAll('.debit-input, .credit-input').forEach(i => i.placeholder = "0");
                if (balanceStatusEl) {
                    if (absDiff < 0.001 && totalDebit > 0) {
                        balanceStatusEl.innerHTML = "Équilibrée ✅";
                        balanceStatusEl.className = "font-weight-bold text-success uppercase small tracking-wider";
                        submitBtn.disabled = false; equilibre_first = true;
                    } else {
                        let message = "Déséquilibre";
                        if (totalDebit > 0 || totalCredit > 0) message += ` (${diff > 0 ? 'Crédit' : 'Débit'})`;
                        balanceStatusEl.innerHTML = `${message}: <span class="magical-balance">${absDiff.toLocaleString('fr-FR', { maximumFractionDigits: 2 })}</span> F`;
                        balanceStatusEl.className = "font-weight-bold text-danger uppercase small tracking-wider";
                        submitBtn.disabled = true;
                        const hintVal = absDiff.toLocaleString('fr-FR');
                        if (diff > 0) document.querySelectorAll('.credit-input').forEach(i => { if (!i.value || parseFloat(i.value) === 0) i.placeholder = hintVal; });
                        else if (diff < 0) document.querySelectorAll('.debit-input').forEach(i => { if (!i.value || parseFloat(i.value) === 0) i.placeholder = hintVal; });
                    }
                }
                return diff;
            }

            function sanitizeAmount(input) {
                let v = input.value.replace(/,/g, '').replace(/[^0-9.]/g, '');
                const parts = v.split('.'); if (parts.length > 2) v = parts[0] + '.' + parts.slice(1).join('');
                if (input.value !== v) input.value = v;
            }

            function handleInput(e) {
                const input = e.target; sanitizeAmount(input);
                const row = input.closest('tr'); const val = parseFloat(input.value || 0);
                if (!equilibre_first && val > 0) {
                    if (input.classList.contains('debit-input')) row.querySelector('.credit-input').value = '';
                    else row.querySelector('.debit-input').value = '';
                    const rows = document.querySelectorAll('.line-row');
                    if (row === rows[rows.length - 1]) document.getElementById('add-line').click();
                }
                calculate(); saveDraft();
            }

            function attachListeners(row) {
                row.querySelectorAll('input').forEach(i => { i.addEventListener('input', handleInput); i.addEventListener('focus', function() { if (!equilibre_first && !this.value) { const d = calculate(); const a = Math.abs(d); if (a > 0.01 && ((this.classList.contains('debit-input') && d < 0) || (this.classList.contains('credit-input') && d > 0))) { this.value = Number.isInteger(a) ? a : a.toFixed(2); calculate(); } } }); });
                row.querySelectorAll('select').forEach(i => i.addEventListener('change', () => { calculate(); saveDraft(); }));
                row.querySelectorAll('textarea').forEach(i => i.addEventListener('input', saveDraft));
            }

            document.getElementById('add-line').addEventListener('click', () => {
                const rows = document.querySelectorAll('.line-row'); if (rows.length === 0) return;
                const newRow = rows[0].cloneNode(true);
                newRow.querySelectorAll('input, select, textarea').forEach(i => { const n = i.getAttribute('name'); if (n) i.setAttribute('name', n.replace(/\[\d+\]/, `[${lineCount}]`)); i.value = ''; });
                newRow.querySelector('td:last-child').innerHTML = '<button type="button" class="btn btn-link text-danger p-0" onclick="this.closest(\'tr\').remove(); calculate(); saveDraft();"><span class="material-symbols-outlined">delete</span></button>';
                body.appendChild(newRow); lineCount++; attachListeners(newRow); calculate(); saveDraft();
            });

            document.querySelectorAll('.line-row').forEach(attachListeners);
            document.querySelector('input[name="date"]').addEventListener('change', function() { calculate(); saveDraft(); refreshPieceNumber(); });
            document.querySelector('select[name="journal_id"]').addEventListener('change', function() { saveDraft(); refreshPieceNumber(); });
            document.querySelector('input[name="libelle"]').addEventListener('input', saveDraft);
            document.getElementById('journalform').addEventListener('submit', () => localStorage.removeItem(STORAGE_KEY));

            // OCR Logic
            const fileInput = document.getElementById('ocr-file-input');
            const ocrStatus = document.getElementById('ocr-status');
            let activeOcrService = 'tesseract';

            document.querySelectorAll('.ocr-service-btn').forEach(btn => btn.addEventListener('click', function() {
                activeOcrService = this.dataset.service;
                document.querySelectorAll('.ocr-service-btn').forEach(b => { b.className = "ocr-service-btn btn btn-sm rounded-pill px-3 font-weight-bold text-muted border-0"; });
                this.className = "ocr-service-btn btn btn-sm rounded-pill px-3 font-weight-bold btn-primary";
            }));

            document.getElementById('ocr-icon-zone').onclick = () => fileInput.click();
            document.getElementById('ocr-text-zone').onclick = () => fileInput.click();
            fileInput.onchange = function() { if (this.files.length) handleOcrUpload(this.files[0]); };

            function handleOcrUpload(file) {
                const fd = new FormData(); fd.append('file', file); fd.append('_token', '{{ csrf_token() }}'); fd.append('service', activeOcrService);
                ocrStatus.classList.remove('d-none');
                fetch('{{ route('accounting.journal.ocr_import') }}', { method: 'POST', body: fd })
                    .then(r => r.json()).then(data => { if (data.error) Swal.fire('Erreur OCR', data.error, 'error'); else showOcrDebug(data); })
                    .finally(() => { ocrStatus.classList.add('d-none'); fileInput.value = ''; });
            }

            function showOcrDebug(data) {
                const tbody = document.getElementById('ocr-debug-table'); tbody.innerHTML = '';
                if (data.amount) tbody.innerHTML += `<tr class="table-info font-weight-bold"><td>MONTANT TTC</td><td class="d-flex justify-content-between"><span>${parseFloat(data.amount).toLocaleString()} F</span> <button onclick="fillField('amount', '${data.amount}')" class="btn btn-primary btn-sm rounded-lg" style="font-size: 9px;">INJECTER</button></td></tr>`;
                Object.entries(data).forEach(([k, v]) => { if (!['raw_text', 'lignes', 'amount', 'libelle', 'service'].includes(k) && v) tbody.innerHTML += `<tr><td class="text-uppercase font-weight-bold text-muted" style="font-size: 9px;">${k}</td><td class="d-flex justify-content-between small"><span>${v}</span> <div><button onclick="fillField('libelle', '${v}')" class="btn btn-light btn-sm p-1 mx-1" title="Libellé"><span class="material-symbols-outlined" style="font-size: 14px;">title</span></button><button onclick="fillField('date', '${v}')" class="btn btn-light btn-sm p-1" title="Date"><span class="material-symbols-outlined" style="font-size: 14px;">calendar_today</span></button></div></td></tr>`; });
                document.getElementById('ocr-debug-rawtext-area').value = data.raw_text || '';
                document.getElementById('ocr-debug-json').textContent = JSON.stringify(data, null, 2);
                document.getElementById('ocr-debug-service').textContent = data.service === 'mindee' ? 'Cloud API' : 'OCR Local Exploration';
                document.getElementById('ocr-debug-modal').classList.remove('d-none');
            }

            window.fillField = (t, v) => {
                if (t === 'date') document.querySelector('input[name="date"]').value = v;
                else if (t === 'libelle') document.querySelector('input[name="libelle"]').value = v;
                else if (t === 'amount') { const i = document.querySelector('.debit-input'); i.value = v; i.dispatchEvent(new Event('input')); }
                Swal.fire({ toast: true, position: 'bottom-start', timer: 1000, showConfirmButton: false, title: 'Injecté !', icon: 'success' });
            };

            window.processWithAI = () => {
                const rt = document.getElementById('ocr-debug-rawtext-area').value;
                if (!rt || rt.length < 10) return Swal.fire('Erreur', 'Pas assez de texte.', 'warning');
                document.getElementById('ocr-debug-modal').classList.add('d-none');
                Swal.fire({ title: 'Analyse IA...', html: 'Construction de l\'écriture...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                fetch('{{ route('accounting.journal.ocr_ai_process') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ raw_text: rt }) })
                    .then(r => r.json()).then(data => { Swal.close(); if (data.error) Swal.fire('Erreur IA', data.error, 'error'); else applyAISuggestions(data); });
            };

            function applyAISuggestions(data) {
                if (data.date) document.querySelector('input[name="date"]').value = data.date;
                if (data.libelle) document.querySelector('input[name="libelle"]').value = data.libelle;
                document.querySelectorAll('.line-row').forEach((r, i) => { if (i >= 2) r.remove(); else { r.querySelector('select').value = ""; r.querySelector('.debit-input').value = ""; r.querySelector('.credit-input').value = ""; r.querySelector('textarea').value = ""; } });
                if (data.lignes) data.lignes.forEach((l, i) => {
                    let r = document.querySelectorAll('.line-row')[i] || (document.getElementById('add-line').click(), document.querySelectorAll('.line-row')[i]);
                    const s = l.sous_compte || l.compte; if (s) { const o = Array.from(r.querySelector('select').options).find(opt => opt.text.includes(s) || opt.value == s); if (o) { r.querySelector('select').value = o.value; r.querySelector('select').dispatchEvent(new Event('change')); } }
                    r.querySelector('.debit-input').value = l.debit || ""; r.querySelector('.credit-input').value = l.credit || "";
                    r.querySelector('textarea').value = l.libelle || data.libelle || "";
                });
                calculate(); saveDraft();
            }

            window.refreshPieceNumber = () => {
                const j = document.getElementById('journal-select').value; const d = document.getElementById('entry-date').value;
                fetch(`{{ route('accounting.journal.next-piece-number', [], false) }}?journal_id=${j}&date=${d}`)
                    .then(r => r.json()).then(data => {
                        const i = document.getElementById('piece-number'); i.value = data.next_number;
                        const w = document.getElementById('archive-warning'); if (data.will_be_archived) { document.getElementById('archive-warning-text').innerText = `⚠️ Archivage auto (Année ${data.year})`; w.classList.remove('d-none'); } else w.classList.add('d-none');
                    });
            };

            loadDraft(); calculate(); refreshPieceNumber();
        });
    </script>
@endsection
