@extends('layouts.accounting')

@section('title', 'Saisie d\'écriture')

@section('content')
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Saisie d'écriture</h1>
            <p class="text-sm text-gray-500 font-bold uppercase tracking-widest mt-1">Enregistrez une nouvelle opération dans le journal</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('accounting.journals-settings.index') }}" 
                class="px-5 py-2.5 bg-primary text-white font-bold rounded-xl hover:bg-primary-light transition-all text-xs flex items-center gap-2 shadow-lg uppercase tracking-widest">
                <i data-lucide="settings" class="w-4 h-4"></i>
                Paramétrage Journaux
            </a>
            <a href="{{ route('accounting.journal.index') }}" 
                class="px-5 py-2.5 bg-white border border-border text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-all text-xs flex items-center gap-2 shadow-sm uppercase tracking-widest">
                <i data-lucide="book-open" class="w-4 h-4"></i>
                Historique Journal
            </a>
        </div>
    </div>

    <form action="{{ route('accounting.journal.store') }}" method="POST" id="journalform">
        @csrf

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8 rounded-r-xl">
                <div class="flex items-center">
                    <i data-lucide="alert-circle" class="text-red-500 w-5 h-5 mr-3"></i>
                    <div class="text-sm text-red-700 font-bold">Certaines informations sont incorrectes :</div>
                </div>
                <ul class="mt-2 list-disc list-inside text-xs text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-card-bg border border-border rounded-2xl p-3 md:p-6 shadow-sm mb-8">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 md:gap-6">
                <div class="min-w-0">
                    <label
                        class="block text-[10px] md:text-sm font-semibold text-gray-700 mb-1 md:mb-2 px-1 uppercase tracking-wider">N°
                        Pièce</label>
                    <input type="text" value="{{ $nextPieceNumber }}" disabled
                        class="w-full max-w-full bg-gray-100 border border-border rounded-xl px-3 py-2 md:px-4 md:py-3 text-gray-500 font-bold cursor-not-allowed">
                </div>
                <div class="min-w-0">
                    <label
                        class="block text-[10px] md:text-sm font-semibold text-gray-700 mb-1 md:mb-2 px-1 uppercase tracking-wider">Journal</label>
                    <div class="relative">
                        <select name="journal_id" required
                            class="w-full max-w-full bg-gray-50 border border-border rounded-xl px-3 py-2 md:px-4 md:py-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all appearance-none cursor-pointer text-sm md:text-base">
                            @foreach ($journals as $journal)
                                <option value="{{ $journal->id }}"
                                    {{ old('journal_id') == $journal->id ? 'selected' : '' }}>{{ $journal->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-3 md:right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                            <i data-lucide="chevron-down" class="w-4 h-4 md:w-5 md:h-5"></i>
                        </div>
                    </div>
                </div>
                <div class="min-w-0">
                    <label
                        class="block text-[10px] md:text-sm font-semibold text-gray-700 mb-1 md:mb-2 px-1 uppercase tracking-wider">Date
                        d'opération</label>
                    <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}"
                        min="{{ date('Y-m-d', strtotime('-5 days')) }}" max="{{ date('Y-m-t') }}" required
                        class="w-full max-w-full bg-gray-50 border border-border rounded-xl px-3 py-2 md:px-4 md:py-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all text-sm md:text-base">
                </div>
                <div class="min-w-0 md:col-span-2">
                    <label
                        class="block text-[10px] md:text-sm font-semibold text-gray-700 mb-1 md:mb-2 px-1 uppercase tracking-wider">Libellé
                        général</label>
                    <textarea name="libelle" placeholder="Ex: Règlement..." required rows="1"
                        class="w-full max-w-full bg-gray-50 border border-border rounded-xl px-3 py-2 md:px-4 md:py-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all text-sm md:text-base resize-y min-h-[100px] flex items-center">{{ old('libelle') }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-card-bg border border-border rounded-2xl shadow-sm overflow-hidden mb-8">
            <div class="bg-gray-50 px-6 py-4 border-b border-border flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-widest">Lignes d'écritures</h3>
                <button type="button" id="add-line"
                    class="text-primary hover:text-primary-light font-bold text-sm flex items-center gap-2 transition-colors">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    Ajouter une ligne
                </button>
            </div>

            <div class="table-responsive">
                <table class="w-full border-collapse min-w-[800px]">
                    <thead>
                        <tr class="bg-white text-gray-500 border-b border-border">
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-left" style="width: 25%;">
                                Compte</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-left" style="width: 12%;">
                                Débit</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-left" style="width: 12%;">
                                Crédit</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-left">Libellé ligne</th>
                            <th class="px-6 py-4 text-center" style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="lines-body" class="divide-y divide-gray-100">
                        @php
                            $oldLines = old('lines', [null, null]); // Au moins 2 lignes
                        @endphp
                        @foreach ($oldLines as $index => $oldLine)
                            <tr class="line-row hover:bg-gray-50/50 transition-colors">
                                <td class="px-4 py-3">
                                    <select name="lines[{{ $index }}][sous_compte_id]" required
                                        class="w-full bg-white border border-border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary outline-none select2-account">
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
                                    <input type="number" step="0.01" name="lines[{{ $index }}][debit]"
                                        class="debit-input w-full bg-white border border-border rounded-lg px-3 py-2 text-sm text-right font-semibold focus:ring-2 focus:ring-primary outline-none"
                                        value="{{ isset($oldLine['debit']) && $oldLine['debit'] != 0 ? $oldLine['debit'] : '' }}"
                                        placeholder="0">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" name="lines[{{ $index }}][credit]"
                                        class="credit-input w-full bg-white border border-border rounded-lg px-3 py-2 text-sm text-right font-semibold focus:ring-2 focus:ring-primary outline-none"
                                        value="{{ isset($oldLine['credit']) && $oldLine['credit'] != 0 ? $oldLine['credit'] : '' }}"
                                        placeholder="0">
                                </td>
                                <td class="px-4 py-3">
                                    <textarea name="lines[{{ $index }}][libelle]" placeholder="Libellé spécifique (facultatif)" rows="1"
                                        class="w-full bg-white border border-border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary outline-none resize-y min-h-[60px]">{{ $oldLine['libelle'] ?? '' }}</textarea>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($index >= 2)
                                        <button type="button" class="text-red-400 hover:text-red-600 transition-colors p-1"
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

            <div class="bg-gray-50 p-3 md:p-6 border-t border-border mt-auto">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex flex-wrap gap-4 md:gap-8">
                        <div>
                            <div class="text-[10px] uppercase font-bold text-gray-400 tracking-widest mb-1">Total Débit
                            </div>
                            <div class="text-xl font-bold text-gray-900"><span id="total-debit">0</span> F</div>
                        </div>
                        <div>
                            <div class="text-[10px] uppercase font-bold text-gray-400 tracking-widest mb-1">Total Crédit
                            </div>
                            <div class="text-xl font-bold text-gray-900"><span id="total-credit">0</span> F</div>
                        </div>
                        <div id="balance-container">
                            <div class="text-[10px] uppercase font-bold text-gray-400 tracking-widest mb-1">État
                                d'Équilibre</div>
                            <div id="balance-status" class="text-sm font-bold text-red-600">Déséquilibre: 0 F
                            </div>
                        </div>
                    </div>
                    <button type="submit" id="submit-btn" disabled
                        class="w-full md:w-auto px-8 py-4 bg-primary text-white font-bold rounded-2xl hover:bg-primary-light disabled:opacity-30 disabled:cursor-not-allowed transition-all shadow-lg flex items-center justify-center gap-2">
                        <i data-lucide="save" class="w-5 h-5"></i>
                        Enregistrer l'écriture
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
            /* Gris (gray-400) */
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

                        // User's logic: if balanced once, mark it
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
                            // On doit ajouter au crédit
                            credits.forEach(i => {
                                if (!i.value || parseFloat(i.value) === 0) i.placeholder = hintValue;
                            });
                        } else if (diff < 0) {
                            // On doit ajouter au débit
                            debits.forEach(i => {
                                if (!i.value || parseFloat(i.value) === 0) i.placeholder = hintValue;
                            });
                        }
                    }
                }
                return diff;
            }

            function smartFillImbalance(input) {
                // User's logic: if balanced once, stop automatic filling
                if (equilibre_first) return;

                if (parseFloat(input.value || 0) !== 0) return;

                const diff = calculate();
                const absDiff = Math.abs(diff);
                if (absDiff < 0.01) return;

                if ((input.classList.contains('debit-input') && diff < 0) ||
                    (input.classList.contains('credit-input') && diff > 0)) {
                    input.value = absDiff.toFixed(2);
                    input.classList.add('bg-blue-50', 'dark:bg-blue-900/10');
                    setTimeout(() => input.classList.remove('bg-blue-50', 'dark:bg-blue-900/10'), 500);
                    calculate();
                }
            }

            function handleInput(e) {
                const input = e.target;
                const row = input.closest('tr');
                const val = parseFloat(input.value || 0);

                // Only zero-out the opposite side BEFORE the first total balance is reached.
                // After equilibre_first is true, we "touch nothing" automatically.
                if (!equilibre_first && val > 0) {
                    if (input.classList.contains('debit-input')) {
                        row.querySelector('.credit-input').value = '';
                    } else {
                        row.querySelector('.debit-input').value = '';
                    }
                }
                calculate();
            }

            function attachListeners(row) {
                row.querySelectorAll('input').forEach(input => {
                    input.addEventListener('input', handleInput);
                    input.addEventListener('focus', function() {
                        smartFillImbalance(this);
                    });
                });
                row.querySelectorAll('select').forEach(select => {
                    select.addEventListener('change', calculate);
                });
            }

            const addLineBtn = document.getElementById('add-line');
            if (addLineBtn) {
                addLineBtn.addEventListener('click', () => {
                    const rows = document.querySelectorAll('.line-row');
                    if (rows.length === 0) return;

                    const firstRow = rows[0];
                    const newRow = firstRow.cloneNode(true);
                    const inputs = newRow.querySelectorAll('input, select');

                    inputs.forEach(input => {
                        const name = input.getAttribute('name');
                        if (name) {
                            input.setAttribute('name', name.replace(/\[\d+\]/, `[${lineCount}]`));
                        }

                        if (input.classList.contains('debit-input') || input.classList.contains(
                                'credit-input')) {
                            input.value = '';
                        } else if (input.tagName === 'INPUT' || input.tagName === 'TEXTAREA') {
                            input.value = '';
                        } else if (input.tagName === 'SELECT') {
                            input.selectedIndex = 0;
                        }
                    });

                    const deleteCell = newRow.cells[newRow.cells.length - 1];
                    deleteCell.innerHTML =
                        '<button type="button" class="text-red-400 hover:text-red-600 transition-colors p-1" onclick="this.closest(\'tr\').remove(); calculate();"><i data-lucide="x" class="w-5 h-5"></i></button>';

                    body.appendChild(newRow);
                    lineCount++;
                    attachListeners(newRow);
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    calculate();
                });
            }

            document.querySelectorAll('.line-row').forEach(attachListeners);

            const dateInput = document.querySelector('input[name="date"]');
            if (dateInput) {
                dateInput.addEventListener('change', function() {
                    const selectedDate = new Date(this.value);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    const minDate = new Date();
                    minDate.setDate(today.getDate() - 5);
                    minDate.setHours(0, 0, 0, 0);
                    const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

                    if (selectedDate < minDate) {
                        alert('La date ne peut pas remonter à plus de 5 jours.');
                        this.value = minDate.toISOString().split('T')[0];
                    } else if (selectedDate > lastDayOfMonth) {
                        alert('La date ne peut pas dépasser le mois en cours.');
                        this.value = today.toISOString().split('T')[0];
                    }
                });
            }

            calculate();
        });
    </script>
@endsection
