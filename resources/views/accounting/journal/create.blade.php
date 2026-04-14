@extends('layouts.accounting')

@section('title', 'Saisie d\'écriture')

@section('content')
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-bold text-text-main uppercase tracking-tight">Saisie d'écriture</h1>
            <p class="text-sm text-text-secondary mt-1 font-bold italic">Enregistrez vos flux financiers avec précision</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('accounting.journal.index') }}"
                class="px-5 py-2.5 bg-white border border-border text-text-secondary font-black rounded-xl hover:-translate-y-0.5 transition-all text-xs flex items-center gap-2 shadow-sm">
                <i data-lucide="history" class="w-4 h-4"></i>
                Historique
            </a>
            <a href="{{ route('accounting.journal.create') }}"
                class="px-5 py-2.5 bg-primary text-white font-semibold rounded-lg hover:opacity-90 transition-all text-xs flex items-center gap-2 shadow-sm">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Nouvelle saisie
            </a>
        </div>
    </div>

    <!-- Zone Import OCR -->
    <div class="mb-8 p-4 md:p-6 bg-primary/5 border-2 border-dashed border-primary/30 rounded-3xl group hover:border-primary/60 transition-all relative overflow-hidden"
        id="ocr-dropzone">
        <div class="flex flex-col md:flex-row items-center gap-4 md:gap-6 relative z-10">
            <div class="w-12 h-12 md:w-14 md:h-14 bg-primary text-white flex items-center justify-center rounded-2xl shadow-lg group-hover:scale-110 transition-transform cursor-pointer shrink-0"
                id="ocr-icon-zone">
                <i data-lucide="scan-text" class="w-6 h-6 md:w-7 md:h-7"></i>
            </div>
            <div class="flex-1 cursor-pointer text-center md:text-left" id="ocr-text-zone">
                <h3 class="text-primary font-black uppercase tracking-tight text-base md:text-lg">Import Facture</h3>
                <p class="text-primary/60 text-[11px] md:text-sm font-bold italic">Glissez une facture ou cliquez pour
                    remplir les champs</p>
            </div>

            <div class="flex items-center gap-1 bg-white border border-primary/20 rounded-xl p-1 shadow-sm shrink-0"
                onclick="event.stopPropagation()">
                <button type="button" id="btn-tesseract"
                    class="ocr-service-btn px-2.5 py-1.5 md:px-3 md:py-1.5 rounded-lg text-[10px] md:text-xs font-black transition-all bg-primary text-white"
                    data-service="tesseract">
                    <i data-lucide="cpu" class="w-3 h-3 inline mr-1"></i> Local
                </button>
                <button type="button" id="btn-mindee"
                    class="ocr-service-btn px-2.5 py-1.5 md:px-3 md:py-1.5 rounded-lg text-[10px] md:text-xs font-black transition-all text-primary/40 hover:text-primary"
                    data-service="mindee">
                    <i data-lucide="cloud" class="w-3 h-3 inline mr-1"></i> Mindee
                </button>
            </div>

            <div id="ocr-status" class="hidden">
                <div class="flex items-center gap-2 text-primary font-bold text-sm animate-pulse">
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                    Analyse...
                </div>
            </div>
        </div>
        <input type="file" id="ocr-file-input" class="hidden" accept="image/*,application/pdf">
    </div>

    <!-- Modal Debug OCR -->
    <div id="ocr-debug-modal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                        <i data-lucide="bug" class="w-4 h-4 text-white"></i>
                    </div>
                    <div>
                        <h4 class="font-black text-text-main text-sm uppercase tracking-wider">Debug OCR</h4>
                        <p id="ocr-debug-service" class="text-xs text-text-secondary font-bold italic"></p>
                    </div>
                </div>
                <button onclick="document.getElementById('ocr-debug-modal').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-700 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <!-- Contenu scrollable -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                <!-- Tableau des champs parsés -->
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-2">Données extraites
                    </p>
                    <table class="w-full text-sm border border-slate-100 rounded-xl overflow-hidden">
                        <thead class="bg-slate-50">
                            <tr>
                                <th
                                    class="px-4 py-2 text-left text-[10px] font-black uppercase text-text-secondary tracking-widest">
                                    Champ</th>
                                <th
                                    class="px-4 py-2 text-left text-[10px] font-black uppercase text-text-secondary tracking-widest">
                                    Valeur</th>
                            </tr>
                        </thead>
                        <tbody id="ocr-debug-table" class="divide-y divide-slate-100"></tbody>
                    </table>
                </div>
                <!-- Texte brut -->
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-2">Texte brut extrait
                        (Modifiable pour l'IA)</p>
                    <textarea id="ocr-debug-rawtext-area"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs text-slate-700 whitespace-pre-wrap h-48 overflow-y-auto font-mono focus:border-primary outline-none transition-all"></textarea>
                </div>
                <!-- JSON complet -->
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-text-secondary mb-2">Réponse JSON
                        complète</p>
                    <pre id="ocr-debug-json"
                        class="bg-slate-900 text-green-400 rounded-xl p-4 text-xs overflow-x-auto font-mono max-h-48 overflow-y-auto"></pre>
                </div>
            </div>
            <div class="px-6 py-3 border-t border-slate-100 flex justify-between items-center bg-slate-50/50">
                <button type="button" id="btn-ai-process-raw" onclick="processWithAI()"
                    class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-black hover:bg-slate-800 transition-all flex items-center gap-2 shadow-md">
                    <i data-lucide="sparkles" class="w-4 h-4 text-primary"></i>
                    Magique : Appliquer l'IA
                </button>
                <button onclick="document.getElementById('ocr-debug-modal').classList.add('hidden')"
                    class="px-5 py-2.5 bg-white border border-border text-text-secondary rounded-xl text-xs font-black hover:bg-slate-50 transition-all">
                    Fermer
                </button>
            </div>
        </div>
    </div>

    <form action="{{ route('accounting.journal.store') }}" method="POST" id="journalform">
        @csrf

        @if ($errors->any())
            <div class="relative bg-rose-50 border border-rose-100 rounded-2xl p-5 mb-10 animate-fade-in no-print">
                <button type="button" onclick="this.parentElement.remove()"
                    class="absolute top-4 right-4 text-rose-300 hover:text-rose-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
                <ul class="space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="text-xs text-rose-700 font-bold italic flex items-center gap-2">
                            <span class="w-1 h-1 bg-rose-400 rounded-full"></span>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-card-bg border border-border rounded-3xl p-8 shadow-sm mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label class="block text-[11px] font-bold text-text-secondary mb-2 uppercase tracking-wider">N°
                        Pièce</label>
                    <input type="text" id="piece-number" name="numero_piece" value="{{ $nextPieceNumber }}" readonly
                        class="w-full bg-slate-50 border border-border rounded-xl px-4 py-3 text-sm font-black text-text-secondary cursor-not-allowed">
                    <p id="archive-warning"
                        class="hidden mt-1.5 text-[10px] font-bold text-amber-600 flex items-center gap-1">
                        <span id="archive-warning-text">⚠️ Cette écriture sera archivée automatiquement (année
                            différente)</span>
                    </p>
                </div>
                <div>
                    <label
                        class="block text-[11px] font-bold text-text-secondary mb-2 uppercase tracking-wider">Journal</label>
                    <select id="journal-select" name="journal_id" required
                        class="w-full bg-white border border-border rounded-xl px-4 py-3 focus:border-primary outline-none transition-all text-sm font-black text-text-main">
                        @foreach ($journals as $journal)
                            <option value="{{ $journal->id }}"
                                {{ old('journal_id', $selectedJournalId) == $journal->id ? 'selected' : '' }}>
                                {{ $journal->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label
                        class="block text-[11px] font-bold text-text-secondary mb-2 uppercase tracking-wider">Date</label>
                    <input type="date" id="entry-date" name="date" value="{{ old('date', date('Y-m-d')) }}"
                        required placeholder="JJ/MM/AAAA"
                        class="w-full bg-white border border-border rounded-xl px-4 py-3 focus:border-primary outline-none transition-all text-sm font-black text-text-main">
                </div>
                <div>
                    <label
                        class="block text-[11px] font-bold text-text-secondary mb-2 uppercase tracking-wider">Libellé</label>
                    <input type="text" name="libelle" placeholder="Ex: Achat fournitures..." required
                        class="w-full bg-white border border-border rounded-xl px-4 py-3 focus:border-primary outline-none transition-all text-sm font-black text-text-main"
                        value="{{ old('libelle') }}">
                </div>
            </div>
        </div>

        <div class="bg-card-bg border border-border rounded-3xl shadow-sm overflow-hidden mb-8">
            <div class="bg-white/50 px-6 py-4 border-b border-border flex items-center justify-between">
                <h3 class="text-xs font-black text-text-secondary uppercase tracking-widest italic">Lignes d'écritures</h3>
                <button type="button" id="add-line"
                    class="text-primary hover:opacity-80 font-black text-xs flex items-center gap-2 transition-all">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Ajouter une ligne
                </button>
            </div>

            <div class="table-responsive">
                <table class="w-full border-collapse min-w-[800px]">
                    <thead>
                        <tr class="text-text-secondary border-b border-border font-black italic">
                            <th class="px-6 py-4 text-[10px] uppercase font-black tracking-widest text-left"
                                style="width: 30%;">Compte</th>
                            <th class="px-6 py-4 text-[10px] uppercase font-black tracking-widest text-right"
                                style="width: 15%;">Débit</th>
                            <th class="px-6 py-4 text-[10px] uppercase font-black tracking-widest text-right"
                                style="width: 15%;">Crédit</th>
                            <th class="px-6 py-4 text-[10px] uppercase font-black tracking-widest text-left">Libellé de
                                ligne</th>
                            <th class="px-6 py-4" style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="lines-body" class="divide-y divide-slate-100">
                        @php
                            $oldLines = old('lines', [null, null]); // Au moins 2 lignes
                        @endphp
                        @foreach ($oldLines as $index => $oldLine)
                            <tr class="line-row hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                                <td class="px-4 py-3">
                                    <select name="lines[{{ $index }}][sous_compte_id]" required
                                        class="w-full bg-white dark:bg-white/5 border border-border rounded-xl px-3 py-2 text-sm font-bold focus:ring-2 focus:ring-primary outline-none select2-account">
                                        <option value="">Choisir un sous-compte...</option>
                                        @foreach ($accounts->groupBy(fn($sc) => $sc->account->code_compte . ' - ' . $sc->account->libelle) as $parentLabel => $subAccounts)
                                            <optgroup label="{{ $parentLabel }}">
                                                @foreach ($subAccounts as $sc)
                                                    <option value="{{ $sc->id }}"
                                                        {{ isset($oldLine['sous_compte_id']) && $oldLine['sous_compte_id'] == $sc->id ? 'selected' : '' }}>
                                                        {{ $sc->numero_sous_compte }} - {{ $sc->libelle }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" inputmode="decimal" name="lines[{{ $index }}][debit]"
                                        class="debit-input w-full bg-white dark:bg-white/5 border border-border rounded-xl px-3 py-2 text-sm text-right font-black focus:ring-2 focus:ring-primary outline-none"
                                        value="{{ isset($oldLine['debit']) && $oldLine['debit'] != 0 ? rtrim(rtrim(number_format((float) $oldLine['debit'], 2, '.', ''), '0'), '.') : '' }}"
                                        placeholder="0">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" inputmode="decimal" name="lines[{{ $index }}][credit]"
                                        class="credit-input w-full bg-white dark:bg-white/5 border border-border rounded-xl px-3 py-2 text-sm text-right font-black focus:ring-2 focus:ring-primary outline-none"
                                        value="{{ isset($oldLine['credit']) && $oldLine['credit'] != 0 ? rtrim(rtrim(number_format((float) $oldLine['credit'], 2, '.', ''), '0'), '.') : '' }}"
                                        placeholder="0">
                                </td>
                                <td class="px-4 py-3">
                                    <textarea name="lines[{{ $index }}][libelle]" placeholder="Libellé spécifique (facultatif)" rows="1"
                                        class="w-full bg-white dark:bg-white/5 border border-border rounded-xl px-3 py-2 text-sm font-bold focus:ring-2 focus:ring-primary outline-none resize-y min-h-[60px]">{{ $oldLine['libelle'] ?? '' }}</textarea>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($index >= 2)
                                        <button type="button"
                                            class="text-rose-400 hover:text-rose-600 transition-colors p-1"
                                            onclick="this.closest('tr').remove(); calculate();">
                                            <i data-lucide="x" class="w-5 h-5"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-slate-50/50 p-6 border-t border-slate-100">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-8">
                    <div class="flex flex-wrap gap-10">
                        <div>
                            <div class="text-[10px] uppercase font-black text-text-secondary tracking-widest mb-1 italic">
                                Total Débit</div>
                            <div class="text-3xl font-black text-text-main"><span id="total-debit">0</span> <span
                                    class="text-sm font-medium text-text-secondary opacity-60">XOF</span></div>
                        </div>
                        <div>
                            <div class="text-[10px] uppercase font-black text-text-secondary tracking-widest mb-1 italic">
                                Total Crédit</div>
                            <div class="text-3xl font-black text-text-main"><span id="total-credit">0</span> <span
                                    class="text-sm font-medium text-text-secondary opacity-60">XOF</span></div>
                        </div>
                        <div id="balance-container">
                            <div class="text-[10px] uppercase font-black text-text-secondary tracking-widest mb-1 italic">
                                État d'Équilibre</div>
                            <div id="balance-status" class="text-sm font-black text-rose-500 uppercase tracking-widest">
                                Déséquilibre: 0 XOF</div>
                        </div>
                    </div>
                    <button type="submit" id="submit-btn" disabled
                        class="w-full md:w-auto px-5 py-2.5 bg-primary text-white font-black rounded-xl hover:bg-primary-light disabled:opacity-30 disabled:cursor-not-allowed transition-all shadow-xl flex items-center justify-center gap-2 uppercase tracking-widest text-xs">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                        Valider l'écriture
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <style>
        .debit-input::placeholder,
        .credit-input::placeholder {
            color: #9ca3af !important;
            /* Gris (slate-400) */
            font-weight: 500;
            opacity: 0.8;
        }

        .magical-balance {
            color: #8B4513;
            /* Retour au marron d'origine */
            font-weight: 900;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let lineCount = {{ count($oldLines) }};
            const body = document.getElementById('lines-body');
            const totalDebitEl = document.getElementById('total-debit');
            const totalCreditEl = document.getElementById('total-credit');
            const balanceStatusEl = document.getElementById('balance-status');
            const submitBtn = document.getElementById('submit-btn');

            let equilibre_first = false;

            // --- PERSISTENCE LOGIC (localStorage) ---
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

                    if (scSelect) {
                        draft.lines.push({
                            account_id: scSelect.value,
                            debit: debitInput.value,
                            credit: creditInput.value,
                            libelle: libelleArea.value
                        });
                    }
                });

                localStorage.setItem(STORAGE_KEY, JSON.stringify(draft));
            }

            function loadDraft() {
                const saved = localStorage.getItem(STORAGE_KEY);
                if (!saved) return;

                // Only load if the form is currently "fresh"
                const currentLibelle = document.querySelector('input[name="libelle"]').value;
                if (currentLibelle && currentLibelle.trim() !== "") return;

                const draft = JSON.parse(saved);

                if (draft.journal_id) document.querySelector('select[name="journal_id"]').value = draft.journal_id;
                if (draft.date) document.querySelector('input[name="date"]').value = draft.date;
                if (draft.libelle) document.querySelector('input[name="libelle"]').value = draft.libelle;

                if (draft.lines && draft.lines.length > 0) {
                    // Start from line 0
                    const rows = document.querySelectorAll('.line-row');

                    draft.lines.forEach((line, index) => {
                        let row;
                        if (index < rows.length) {
                            row = rows[index];
                        } else {
                            // Add new line
                            const addLineBtn = document.getElementById('add-line');
                            if (addLineBtn) addLineBtn.click();
                            row = document.querySelectorAll('.line-row')[index];
                        }

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
                let totalDebit = 0;
                let totalCredit = 0;

                const debits = document.querySelectorAll('.debit-input');
                const credits = document.querySelectorAll('.credit-input');

                debits.forEach(input => {
                    totalDebit += parseFloat(input.value || 0);
                });
                credits.forEach(input => {
                    totalCredit += parseFloat(input.value || 0);
                });

                if (totalDebitEl) totalDebitEl.innerText = totalDebit.toLocaleString('fr-FR', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                });
                if (totalCreditEl) totalCreditEl.innerText = totalCredit.toLocaleString('fr-FR', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                });

                const diff = totalDebit - totalCredit;
                const absDiff = Math.abs(diff);

                debits.forEach(i => i.placeholder = "0");
                credits.forEach(i => i.placeholder = "0");

                if (balanceStatusEl) {
                    if (absDiff < 0.001 && totalDebit > 0) {
                        balanceStatusEl.innerHTML = "Écriture équilibrée ✅";
                        balanceStatusEl.classList.remove('text-red-600');
                        balanceStatusEl.classList.add('text-green-600');
                        submitBtn.disabled = false;
                        equilibre_first = true;
                    } else {
                        let message = "Déséquilibre";
                        if (totalDebit > 0 || totalCredit > 0) {
                            const sideNeeded = (diff > 0) ? 'Crédit' : 'Débit';
                            message += ` (${sideNeeded})`;
                        }
                        balanceStatusEl.innerHTML =
                            `${message}: <span class="magical-balance">${absDiff.toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}</span> F`;
                        balanceStatusEl.classList.remove('text-green-600');
                        balanceStatusEl.classList.add('text-red-600');
                        submitBtn.disabled = true;

                        const hintValue = absDiff.toLocaleString('fr-FR', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 2
                        });
                        if (diff > 0) {
                            credits.forEach(i => {
                                if (!i.value || parseFloat(i.value) === 0) i.placeholder = hintValue;
                            });
                        } else if (diff < 0) {
                            debits.forEach(i => {
                                if (!i.value || parseFloat(i.value) === 0) i.placeholder = hintValue;
                            });
                        }
                    }
                }
                return diff;
            }

            function formatAmount(n) {
                // Affiche l'entier si pas de décimale utile (80000 et non 80000.00)
                return Number.isInteger(n) ? String(n) : parseFloat(n.toFixed(2)).toString();
            }

            function smartFillImbalance(input) {
                if (equilibre_first) return;
                if (parseFloat(input.value || 0) !== 0) return;
                const diff = calculate();
                const absDiff = Math.abs(diff);
                if (absDiff < 0.01) return;

                if ((input.classList.contains('debit-input') && diff < 0) ||
                    (input.classList.contains('credit-input') && diff > 0)) {
                    input.value = formatAmount(absDiff);
                    input.classList.add('bg-primary/5', 'dark:bg-primary/10');
                    setTimeout(() => input.classList.remove('bg-primary/5', 'dark:bg-primary/10'), 500);
                    calculate();
                }
            }

            function sanitizeAmount(input) {
                // Retire les virgules, n'autorise que chiffres et point
                let v = input.value.replace(/,/g, '').replace(/[^0-9.]/g, '');
                // Un seul point décimal max
                const parts = v.split('.');
                if (parts.length > 2) v = parts[0] + '.' + parts.slice(1).join('');
                if (input.value !== v) input.value = v;
            }

            function handleInput(e) {
                const input = e.target;
                sanitizeAmount(input);
                const row = input.closest('tr');
                const val = parseFloat(input.value || 0);

                if (!equilibre_first && val > 0) {
                    if (input.classList.contains('debit-input')) {
                        row.querySelector('.credit-input').value = '';
                    } else {
                        row.querySelector('.debit-input').value = '';
                    }

                    // Logique d'ajout automatique de ligne
                    const rows = document.querySelectorAll('.line-row');
                    const lastRow = rows[rows.length - 1];
                    if (row === lastRow) {
                        document.getElementById('add-line').click();
                    }
                }
                calculate();
                saveDraft();
            }

            function attachListeners(row) {
                row.querySelectorAll('input').forEach(input => {
                    input.addEventListener('input', handleInput);
                    input.addEventListener('focus', function() {
                        smartFillImbalance(this);
                    });
                });
                row.querySelectorAll('select').forEach(select => {
                    select.addEventListener('change', () => {
                        calculate();
                        saveDraft();
                    });
                });
                row.querySelectorAll('textarea').forEach(area => {
                    area.addEventListener('input', saveDraft);
                });
            }

            const addLineBtn = document.getElementById('add-line');
            if (addLineBtn) {
                addLineBtn.addEventListener('click', () => {
                    const rows = document.querySelectorAll('.line-row');
                    if (rows.length === 0) return;

                    const firstRow = rows[0];
                    const newRow = firstRow.cloneNode(true);
                    const inputs = newRow.querySelectorAll('input, select, textarea');

                    inputs.forEach(input => {
                        const name = input.getAttribute('name');
                        if (name) {
                            input.setAttribute('name', name.replace(/\[\d+\]/, `[${lineCount}]`));
                        }

                        if (input.classList.contains('debit-input') || input.classList.contains(
                                'credit-input')) {
                            input.value = '';
                        } else {
                            input.value = '';
                        }
                    });

                    const deleteCell = newRow.cells[newRow.cells.length - 1];
                    deleteCell.innerHTML =
                        '<button type="button" class="text-red-400 hover:text-red-600 transition-colors p-1" onclick="this.closest(\'tr\').remove(); calculate(); saveDraft();"><i data-lucide="x" class="w-5 h-5"></i></button>';

                    body.appendChild(newRow);
                    lineCount++;
                    attachListeners(newRow);
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    calculate();
                    saveDraft();
                });
            }

            document.querySelectorAll('.line-row').forEach(attachListeners);

            const dateInput = document.querySelector('input[name="date"]');
            if (dateInput) {
                dateInput.addEventListener('change', function() {
                    const selectedDate = new Date(this.value);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

                    if (selectedDate > lastDayOfMonth) {
                        alert('La date ne peut pas dépasser le mois en cours.');
                        this.value = today.toISOString().split('T')[0];
                    }
                    saveDraft();
                    if (typeof refreshPieceNumber === 'function') refreshPieceNumber();
                });
            }

            document.querySelector('input[name="libelle"]').addEventListener('input', saveDraft);

            const journalSelect = document.querySelector('select[name="journal_id"]');
            journalSelect.addEventListener('change', function() {
                saveDraft();
                if (typeof refreshPieceNumber === 'function') refreshPieceNumber();
            });

            // Clear storage on submit
            document.getElementById('journalform').addEventListener('submit', function() {
                localStorage.removeItem(STORAGE_KEY);
            });

            // --- OCR Logic ---
            let lastOcrData = null;
            const dropzone = document.getElementById('ocr-dropzone');
            const fileInput = document.getElementById('ocr-file-input');
            const ocrStatus = document.getElementById('ocr-status');

            // Service actif (tesseract par défaut)
            let activeOcrService = 'tesseract';

            // Gestion du toggle Tesseract / Mindee
            document.querySelectorAll('.ocr-service-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    activeOcrService = this.dataset.service;
                    document.querySelectorAll('.ocr-service-btn').forEach(b => {
                        b.classList.remove('bg-primary', 'text-white');
                        b.classList.add('text-primary/40', 'hover:text-primary');
                    });
                    this.classList.add('bg-primary', 'text-white');
                    this.classList.remove('text-primary/40', 'hover:text-primary');
                });
            });

            // Clic sur la zone ou l'icône/texte ouvre le fichier
            document.getElementById('ocr-icon-zone').addEventListener('click', () => fileInput.click());
            document.getElementById('ocr-text-zone').addEventListener('click', () => fileInput.click());

            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) handleOcrUpload(this.files[0]);
            });

            dropzone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropzone.classList.add('bg-primary/10', 'border-primary/50');
            });

            dropzone.addEventListener('dragleave', () => {
                dropzone.classList.remove('bg-primary/10', 'border-primary/50');
            });

            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.classList.remove('bg-primary/10', 'border-primary/50');
                if (e.dataTransfer.files.length > 0) handleOcrUpload(e.dataTransfer.files[0]);
            });

            function handleOcrUpload(file) {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('service', activeOcrService); // Tesseract ou Mindee

                ocrStatus.classList.remove('hidden');

                fetch('{{ route('accounting.journal.ocr_import') }}', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.error) {
                            Swal.fire({
                                title: 'Erreur OCR',
                                text: data.error,
                                icon: 'error'
                            });
                        } else {
                            // -----------------------------------------------
                            // PHASE EXPLORATION : formulaire non rempli
                            // On analyse d'abord les données disponibles
                            // avant de décider quoi mapper où.
                            // -----------------------------------------------
                            // if (data.date)   document.querySelector('input[name="date"]').value = data.date;
                            // if (data.libelle) document.querySelector('input[name="libelle"]').value = data.libelle;
                            // if (data.amount) { ... }

                            // Afficher toutes les données brutes dans la modal
                            showOcrDebug(data);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Erreur', 'Impossible de contacter le service OCR.', 'error');
                    })
                    .finally(() => {
                        ocrStatus.classList.add('hidden');
                        fileInput.value = '';
                    });
            }

            // --- Fonction Debug OCR ---
            function showOcrDebug(data) {
                lastOcrData = data;
                const tbody = document.getElementById('ocr-debug-table');
                tbody.innerHTML = '';

                let hasValidFields = false;

                // -------------------------------------------------
                // 1. Champs analysés par l'IA (On n'affiche que ce qui a été TROUVÉ)
                // -------------------------------------------------
                const skip = new Set(['raw_text', 'lignes', 'service', 'amount', 'libelle', 'mindee']);

                // Cas spécial : Montant principal
                if (data.amount) {
                    hasValidFields = true;
                    tbody.innerHTML += `
                        <tr class="bg-primary/5 border-l-4 border-primary group">
                            <td class="px-3 py-2 text-[10px] font-black text-primary uppercase tracking-wider">MONTANT TTC</td>
                            <td class="px-3 py-2 text-sm font-black text-primary flex flex-wrap items-center justify-between gap-2">
                                <span>${parseFloat(data.amount).toLocaleString()} XOF</span>
                                <button onclick="fillField('amount', '${data.amount}')" class="bg-primary text-white px-3 py-2 md:px-2 md:py-1 rounded text-[10px] md:text-[9px] hover:scale-105 transition-transform uppercase font-black shadow-sm">
                                    Injecter
                                </button>
                            </td>
                        </tr>`;
                }

                Object.entries(data).forEach(([key, val]) => {
                    if (skip.has(key)) return;
                    const isValide = val !== null && val !== undefined && val !== '' && val !== '—';
                    if (!isValide) return; // ON CACHE LES VIDES

                    hasValidFields = true;
                    tbody.innerHTML += `
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-3 py-2 text-[10px] font-black text-text-secondary uppercase tracking-wider w-1/3">${key.replace('_', ' ')}</td>
                            <td class="px-3 py-2 text-xs flex flex-wrap items-center justify-between gap-2">
                                <span class="font-bold text-text-main break-all">${val}</span>
                                <div class="flex gap-1 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button onclick="fillField('libelle', '${val.toString().replace(/'/g, "\\'")}')" class="p-1.5 bg-primary/5 md:bg-transparent hover:bg-primary/10 rounded text-primary transition-colors" title="Vers Libellé">
                                        <i data-lucide="type" class="w-4 h-4 md:w-3 md:h-3"></i>
                                    </button>
                                    <button onclick="fillField('date', '${val.toString().replace(/'/g, "\\'")}')" class="p-1.5 bg-primary/5 md:bg-transparent hover:bg-primary/10 rounded text-primary transition-colors" title="Vers Date">
                                        <i data-lucide="calendar" class="w-4 h-4 md:w-3 md:h-3"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                });

                if (!hasValidFields) {
                    tbody.innerHTML =
                        `<tr><td colspan="2" class="px-4 py-8 text-center text-xs text-slate-400 italic">Aucune donnée structurée détectée. Utilisez le texte brut ci-dessous.</td></tr>`;
                }

                // -------------------------------------------------
                // 3. Texte brut dans le textarea (Modifiable)
                // -------------------------------------------------
                document.getElementById('ocr-debug-rawtext-area').value = data.raw_text || '';

                // -------------------------------------------------
                // 4. JSON complet
                // -------------------------------------------------
                document.getElementById('ocr-debug-json').textContent =
                    JSON.stringify(data, null, 2);

                // Header
                document.getElementById('ocr-debug-service').textContent =
                    data.service === 'mindee' ? 'Mindee Cloud API' : 'Tesseract OCR local — Mode exploration';

                // Affichage
                document.getElementById('ocr-debug-modal').classList.remove('hidden');
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }

            // --- Injection manuelle depuis Debug ---
            window.fillField = function(target, value) {
                if (!value || value === 'null') return;

                if (target === 'date') {
                    const input = document.querySelector('input[name="date"]');
                    if (input) input.value = value;
                } else if (target === 'libelle') {
                    const input = document.querySelector('input[name="libelle"]');
                    if (input) input.value = value;
                } else if (target === 'amount') {
                    const firstDebit = document.querySelector('.debit-input');
                    if (firstDebit) {
                        // Nettoyer la valeur si c'est un montant (enlever espaces, etc)
                        const cleanValue = parseFloat(value.toString().replace(/[^0-9.]/g, ''));
                        if (!isNaN(cleanValue)) {
                            firstDebit.value = formatAmount(cleanValue);
                            firstDebit.dispatchEvent(new Event('input'));
                        }
                    }
                }

                // Petit feedback visuel
                Swal.fire({
                    title: 'Injecté !',
                    icon: 'success',
                    toast: true,
                    position: 'bottom-start',
                    timer: 1500,
                    showConfirmButton: false
                });
            };

            // --- IA Process Logic ---
            window.processWithAI = function() {
                const rawText = document.getElementById('ocr-debug-rawtext-area').value;
                if (!rawText || rawText.trim().length < 10) {
                    Swal.fire('Erreur', 'Pas assez de texte à analyser.', 'warning');
                    return;
                }

                // Close modals and show loader
                document.getElementById('ocr-debug-modal').classList.add('hidden');

                Swal.fire({
                    title: 'L\'IA analyse...',
                    html: 'Construction de l\'écriture comptable...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('{{ route('accounting.journal.ocr_ai_process') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            raw_text: rawText
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        Swal.close();
                        console.log('Gemini Data:', data); // Debug
                        if (data.error) {
                            Swal.fire('Erreur IA', data.error, 'error');
                        } else {
                            applyAISuggestions(data);
                        }
                    })
                    .catch(err => {
                        Swal.close();
                        Swal.fire('Erreur', 'Impossible de contacter l\'IA.', 'error');
                    });
            };

            function applyAISuggestions(data) {
                // Mapping des données générales
                if (data.date) document.querySelector('input[name="date"]').value = data.date;
                if (data.libelle) document.querySelector('input[name="libelle"]').value = data.libelle;

                // On vide les lignes actuelles (sauf les 2 premières)
                const rows = document.querySelectorAll('.line-row');
                rows.forEach((row, i) => {
                    if (i >= 2) row.remove();
                });

                // Reset first 2 lines
                const firstRows = document.querySelectorAll('.line-row');
                firstRows.forEach(row => {
                    row.querySelector('select').value = "";
                    row.querySelector('.debit-input').value = "";
                    row.querySelector('.credit-input').value = "";
                    row.querySelector('textarea').value = "";
                });

                // Remplissage des lignes IA
                if (data.lignes && data.lignes.length > 0) {
                    data.lignes.forEach((line, index) => {
                        let row = document.querySelectorAll('.line-row')[index];
                        if (!row) {
                            document.getElementById('add-line').click();
                            row = document.querySelectorAll('.line-row')[index];
                        }

                        // Trouver le compte le plus proche
                        const searchAccount = line.sous_compte || line.compte || line.code;
                        if (searchAccount) {
                            const options = Array.from(row.querySelector('select').options);
                            const bestMatch = options.find(opt =>
                                opt.text.startsWith(searchAccount) ||
                                opt.text.includes(searchAccount) ||
                                opt.value == searchAccount
                            );
                            if (bestMatch) {
                                row.querySelector('select').value = bestMatch.value;
                                row.querySelector('select').dispatchEvent(new Event('change'));
                            }
                        }

                        row.querySelector('.debit-input').value = line.debit || "";
                        row.querySelector('.credit-input').value = line.credit || "";
                        if (line.libelle) row.querySelector('textarea').value = line.libelle;
                        else if (data.libelle) row.querySelector('textarea').value = data.libelle;
                    });
                }

                calculate();
                saveDraft();

                Swal.fire({
                    title: 'Écriture générée !',
                    text: 'Vérifiez les comptes et les montants avant de valider.',
                    icon: 'success',
                    timer: 3000,
                    timerProgressBar: true
                });
            }

            // Initial load
            loadDraft();
            calculate();

            // ═══════════════════════════════════════════════════
            // Rafraîchissement dynamique du N° de pièce
            // ═══════════════════════════════════════════════════
            const pieceNumberInput = document.getElementById('piece-number');
            const archiveWarning = document.getElementById('archive-warning');

            // On exporte en global pour l'appeler facilement d'en haut
            window.refreshPieceNumber = function() {
                const journalId = journalSelect ? journalSelect.value : null;
                const date = dateInput ? dateInput.value : null;
                if (!journalId || !date) return;

                const fetchUrl =
                    `{{ route('accounting.journal.next-piece-number', [], false) }}?journal_id=${journalId}&date=${date}`;

                fetch(fetchUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (pieceNumberInput) {
                            pieceNumberInput.value = data.next_number;
                            // Animation flash
                            pieceNumberInput.classList.add('ring-2', 'ring-primary');
                            setTimeout(() => pieceNumberInput.classList.remove('ring-2', 'ring-primary'),
                                600);
                        }
                        if (archiveWarning) {
                            if (data.will_be_archived) {
                                const warningText = document.getElementById('archive-warning-text');
                                if (warningText) {
                                    warningText.innerText =
                                        `⚠️ Cette écriture sera archivée automatiquement (année ${data.year})`;
                                }
                                archiveWarning.classList.remove('hidden');
                            } else {
                                archiveWarning.classList.add('hidden');
                            }
                        }
                    })
                    .catch(err => {
                        console.error("Erreur mise à jour N° pièce:", err);
                    });
            };

            // Appel initial si un journal est déjà sélectionné
            if (journalSelect && journalSelect.value) refreshPieceNumber();
        });
    </script>
@endsection
