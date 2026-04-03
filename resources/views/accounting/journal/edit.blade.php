@extends('layouts.accounting')

@section('title', "Modifier l'écriture")

@section('content')
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('accounting.journal.index') }}" class="p-2 bg-white border border-border hover:bg-slate-50 rounded-xl transition-all shadow-sm group">
                <i data-lucide="arrow-left" class="w-5 h-5 text-text-secondary group-hover:text-primary transition-colors"></i>
            </a>
            <div>
                <h1 class="text-3xl font-black text-text-main uppercase tracking-tight">Modifier l'écriture</h1>
                <p class="text-sm text-text-secondary mt-1 font-bold italic">Mise à jour de la pièce N° {{ str_replace('PC-', '', $entry->numero_piece) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('accounting.journal.index') }}" 
                class="px-5 py-2.5 bg-white border border-border text-text-secondary font-black rounded-xl hover:-translate-y-0.5 transition-all text-xs flex items-center gap-2 shadow-sm uppercase tracking-widest">
                <i data-lucide="history" class="w-4 h-4"></i>
                Historique
            </a>
        </div>
    </div>

    <form action="{{ route('accounting.journal.update', $entry->id) }}" method="POST" id="journalform">
        @csrf

        @if ($errors->any())
            <div class="relative bg-rose-50 border border-rose-100 rounded-2xl p-5 mb-10 animate-fade-in no-print">
                <button type="button" onclick="this.parentElement.remove()" class="absolute top-4 right-4 text-rose-300 hover:text-rose-600 transition-colors">
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
                    <label class="block text-[11px] font-bold text-text-secondary mb-2 uppercase tracking-wider px-1">N° Pièce</label>
                    <input type="text" value="{{ str_replace('PC-', '', $entry->numero_piece) }}" disabled
                        class="w-full bg-slate-50 dark:bg-white/5 border border-border rounded-xl px-4 py-3 text-text-secondary font-black cursor-not-allowed italic">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-text-secondary mb-2 uppercase tracking-wider px-1">Journal</label>
                    <select name="journal_id" required
                        class="w-full bg-white border border-border rounded-xl px-4 py-3 focus:border-primary outline-none transition-all text-sm font-black text-text-main appearance-none">
                        @foreach ($journals as $journal)
                            <option value="{{ $journal->id }}"
                                {{ old('journal_id', $entry->journal_id) == $journal->id ? 'selected' : '' }}>{{ $journal->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-text-secondary mb-2 uppercase tracking-wider px-1">Date d'opération</label>
                    <input type="date" name="date" value="{{ old('date', $entry->date) }}" required
                        class="w-full bg-white border border-border rounded-xl px-4 py-3 focus:border-primary outline-none transition-all text-sm font-black text-text-main">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-text-secondary mb-2 uppercase tracking-wider px-1">Libellé général</label>
                    <input type="text" name="libelle" placeholder="Ex: Règlement facture..." required
                        class="w-full bg-white border border-border rounded-xl px-4 py-3 focus:border-primary outline-none transition-all text-sm font-black text-text-main" 
                        value="{{ old('libelle', $entry->libelle) }}">
                </div>
            </div>
        </div>

        <div class="bg-card-bg border border-border rounded-3xl shadow-sm overflow-hidden mb-8">
            <div class="bg-white/50 px-6 py-4 border-b border-border flex items-center justify-between">
                <h3 class="text-xs font-black text-text-secondary uppercase tracking-widest italic">Lignes d'écritures</h3>
                <button type="button" id="add-line"
                    class="text-primary hover:opacity-80 font-black text-xs flex items-center gap-2 transition-all uppercase tracking-widest">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Ajouter une ligne
                </button>
            </div>

            <div class="table-responsive">
                <table class="w-full border-collapse min-w-[800px]">
                    <thead>
                        <tr class="text-text-secondary border-b border-border font-black italic">
                            <th class="px-6 py-4 text-[10px] uppercase font-black tracking-widest text-left" style="width: 30%;">Compte</th>
                            <th class="px-6 py-4 text-[10px] uppercase font-black tracking-widest text-right" style="width: 15%;">Débit</th>
                            <th class="px-6 py-4 text-[10px] uppercase font-black tracking-widest text-right" style="width: 15%;">Crédit</th>
                            <th class="px-6 py-4 text-[10px] uppercase font-black tracking-widest text-left">Libellé de ligne</th>
                            <th class="px-6 py-4" style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="lines-body" class="divide-y divide-slate-100">
                        @php
                            $lines = old('lines', $entry->lines->toArray());
                        @endphp
                        @foreach ($lines as $index => $line)
                            <tr class="line-row hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                                <td class="px-4 py-3">
                                    <select name="lines[{{ $index }}][sous_compte_id]" required
                                        class="w-full bg-white dark:bg-white/5 border border-border rounded-xl px-3 py-2 text-sm font-black text-text-main focus:ring-2 focus:ring-primary outline-none select2-account">
                                        <option value="">Choisir un sous-compte...</option>
                                        @foreach ($accounts->groupBy(fn($sc) => $sc->account->code_compte . ' - ' . $sc->account->libelle) as $parentLabel => $subAccounts)
                                            <optgroup label="{{ $parentLabel }}">
                                                @foreach ($subAccounts as $sc)
                                                    <option value="{{ $sc->id }}"
                                                        {{ (isset($line['sous_compte_id']) && $line['sous_compte_id'] == $sc->id) ? 'selected' : '' }}>
                                                        {{ $sc->numero_sous_compte }} - {{ $sc->libelle }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" inputmode="decimal" name="lines[{{ $index }}][debit]"
                                        class="debit-input w-full bg-white dark:bg-white/5 border border-border rounded-xl px-3 py-2 text-sm text-right font-black focus:ring-2 focus:ring-primary outline-none"
                                        value="{{ (isset($line['debit']) && $line['debit'] != 0) ? rtrim(rtrim(number_format((float)$line['debit'], 2, '.', ''), '0'), '.') : '' }}"
                                        placeholder="0">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" inputmode="decimal" name="lines[{{ $index }}][credit]"
                                        class="credit-input w-full bg-white dark:bg-white/5 border border-border rounded-xl px-3 py-2 text-sm text-right font-black focus:ring-2 focus:ring-primary outline-none"
                                        value="{{ (isset($line['credit']) && $line['credit'] != 0) ? rtrim(rtrim(number_format((float)$line['credit'], 2, '.', ''), '0'), '.') : '' }}"
                                        placeholder="0">
                                </td>
                                <td class="px-4 py-3">
                                    <textarea name="lines[{{ $index }}][libelle]" placeholder="Libellé spécifique (facultatif)" rows="1"
                                        class="w-full bg-white dark:bg-white/5 border border-border rounded-xl px-3 py-2 text-sm font-black focus:ring-2 focus:ring-primary outline-none resize-y min-h-[60px]">{{ $line['libelle'] ?? '' }}</textarea>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" class="delete-line-btn text-rose-400 hover:text-rose-600 transition-colors p-1"
                                        onclick="this.closest('tr').remove(); calculate();">
                                        <i data-lucide="x" class="w-5 h-5"></i>
                                    </button>
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
                            <div class="text-[10px] uppercase font-black text-text-secondary tracking-widest mb-1 italic">Total Débit</div>
                            <div class="text-3xl font-black text-text-main"><span id="total-debit">0</span> <span class="text-sm font-medium text-text-secondary opacity-60">XOF</span></div>
                        </div>
                        <div>
                            <div class="text-[10px] uppercase font-black text-text-secondary tracking-widest mb-1 italic">Total Crédit</div>
                            <div class="text-3xl font-black text-text-main"><span id="total-credit">0</span> <span class="text-sm font-medium text-text-secondary opacity-60">XOF</span></div>
                        </div>
                        <div id="balance-container">
                            <div class="text-[10px] uppercase font-black text-text-secondary tracking-widest mb-1 italic">État d'Équilibre</div>
                            <div id="balance-status" class="text-sm font-black text-rose-500 uppercase tracking-widest">Déséquilibre: 0 XOF</div>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <a href="{{ route('accounting.journal.index') }}" 
                           class="px-8 py-4 bg-white border border-border text-text-secondary font-black rounded-2xl hover:bg-slate-50 transition-all uppercase tracking-widest text-xs shadow-sm">
                           Annuler
                        </a>
                        <button type="submit" id="submit-btn" 
                            class="px-10 py-4 bg-primary text-white font-black rounded-2xl hover:bg-primary-light disabled:opacity-30 disabled:opacity-30 disabled:cursor-not-allowed transition-all shadow-xl flex items-center justify-center gap-2 uppercase tracking-widest text-xs">
                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                            Mettre à jour
                        </button>
                    </div>
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
            font-weight: 500;
            opacity: 0.8;
        }

        .magical-balance {
            color: #8B4513;
            font-weight: 900;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let lineCount = {{ count($lines) }};
            const body = document.getElementById('lines-body');
            const totalDebitEl = document.getElementById('total-debit');
            const totalCreditEl = document.getElementById('total-credit');
            const balanceStatusEl = document.getElementById('balance-status');
            const submitBtn = document.getElementById('submit-btn');

            let equilibre_first = false;

            function calculate() {
                const rows = document.querySelectorAll('.line-row');
                
                // User's Rule: Hide delete buttons if only 2 rows remain
                const deleteButtons = document.querySelectorAll('.delete-line-btn');
                if (rows.length <= 2) {
                    deleteButtons.forEach(btn => btn.style.display = 'none');
                } else {
                    deleteButtons.forEach(btn => btn.style.display = 'block');
                }

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
                        balanceStatusEl.classList.remove('text-rose-500');
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
                        balanceStatusEl.classList.add('text-rose-500');
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
                let v = input.value.replace(/,/g, '').replace(/[^0-9.]/g, '');
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
                row.querySelectorAll('select, textarea').forEach(el => {
                    el.addEventListener('change', calculate);
                });
            }

            const addLineBtn = document.getElementById('add-line');
            if (addLineBtn) {
                addLineBtn.addEventListener('click', () => {
                    const rows = document.querySelectorAll('.line-row');
                    if (rows.length === 0) return;

                    const firstRow = rows[0];
                    const newRow = firstRow.cloneNode(true);
                    
                    // Reset fields
                    newRow.querySelectorAll('input, select, textarea').forEach(input => {
                        const name = input.getAttribute('name');
                        if (name) {
                            input.setAttribute('name', name.replace(/\[\d+\]/, `[${lineCount}]`));
                        }
                        if (input.tagName === 'INPUT' || input.tagName === 'TEXTAREA') {
                            input.value = '';
                        } else if (input.tagName === 'SELECT') {
                            input.selectedIndex = 0;
                        }
                    });

                    // Ensure delete button is correctly set up
                    const deleteCell = newRow.cells[newRow.cells.length - 1];
                    deleteCell.innerHTML =
                        '<button type="button" class="delete-line-btn text-rose-400 hover:text-rose-600 transition-colors p-1" onclick="this.closest(\'tr\').remove(); calculate();"><i data-lucide="x" class="w-5 h-5"></i></button>';

                    body.appendChild(newRow);
                    lineCount++;
                    attachListeners(newRow);
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    calculate();
                });
            }

            document.querySelectorAll('.line-row').forEach(attachListeners);
            calculate();
        });
    </script>
@endsection
