@extends('layouts.accounting')

@section('title', 'Plan Comptable')

@section('content')
    <div>
        <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black text-text-main uppercase tracking-tight">Plan Comptable & Sous-comptes</h1>
                <p class="text-sm text-text-secondary mt-1 font-bold">Consultez le référentiel SYSCOHADA et gérez vos
                    sous-comptes personnalisés</p>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <a href="{{ route('accounting.journals-settings.index') }}"
                    class="w-full md:w-auto px-5 py-2.5 bg-white border border-border text-text-main font-black rounded-xl hover:-translate-y-0.5 transition-all text-xs flex items-center justify-center gap-2 shadow-sm uppercase scroll-smooth tracking-widest">
                    <i data-lucide="settings-2" class="w-4 h-4 text-primary"></i>
                    Configuration Journaux
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- COLONNE GAUCHE: PLAN COMPTABLE -->
            <div class="xl:col-span-2">
                <div class="bg-card-bg border border-border rounded-3xl shadow-sm overflow-hidden h-full flex flex-col">
                    <div class="p-6 border-b border-border flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-black text-text-main flex items-center gap-2 uppercase tracking-widest">
                                <i data-lucide="book-open" class="w-5 h-5 text-primary"></i>
                                Référentiel SYSCOHADA
                            </h2>
                            <p class="text-[10px] text-text-secondary font-black uppercase tracking-wider">Plan comptable
                                officiel</p>
                        </div>
                        <div class="relative max-w-xs w-full">
                            <i data-lucide="search"
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                            <input type="text" id="searchAccount" placeholder="Rechercher par numéro ou intitulé..."
                                class="w-full bg-bg border border-border pl-10 pr-4 py-2.5 text-xs font-bold rounded-xl outline-none focus:border-primary transition-all dark:text-white">
                        </div>
                    </div>

                    <div class="overflow-y-auto custom-scrollbar" style="max-height: 800px;" id="account-list-container">
                        @foreach ($accounts as $classe => $classAccounts)
                            <div class="classe-group">
                                <div class="bg-white px-6 py-2 border-y border-border sticky top-0 z-10">
                                    <span
                                        class="text-[10px] font-black text-text-secondary uppercase tracking-widest">Classe
                                        {{ $classe }}</span>
                                </div>
                                <table class="w-full text-left">
                                    <thead class="bg-slate-50 border-b border-border">
                                        <tr class="text-[10px] uppercase font-black text-text-secondary tracking-widest">
                                            <th class="px-6 py-2">Numéro</th>
                                            <th class="px-6 py-2">Intitulé</th>
                                            <th class="px-6 py-2 text-right">Sous-compte</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50 text-[13px]">
                                        @foreach ($classAccounts as $account)
                                            <tr class="hover:bg-slate-50/50 transition-colors account-row"
                                                data-search="{{ $account->code_compte }} {{ strtolower($account->libelle) }}">
                                                <td class="px-6 py-4 font-mono font-bold text-primary w-32">
                                                    {{ $account->code_compte }}
                                                </td>
                                                <td class="px-6 py-4 font-black text-text-main truncate max-w-md">
                                                    {{ $account->libelle }}
                                                </td>
                                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                                    <button
                                                        onclick="openSubAccountModal({{ $account->id }}, '{{ $account->code_compte }}', '{{ addslashes($account->libelle) }}')"
                                                        class="text-primary hover:text-primary-light font-black text-[11px] uppercase flex items-center gap-1.5 ml-auto transition-colors whitespace-nowrap">
                                                        <i data-lucide="plus-circle" class="w-4 h-4"></i> Sous-compte
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- COLONNE DROITE: MES SOUS-COMPTES -->
            <div class="xl:col-span-1">
                <div class="bg-card-bg border border-border rounded-3xl shadow-sm overflow-hidden h-full flex flex-col">
                    <div class="p-6 border-b border-border flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-black text-text-main flex items-center gap-2 uppercase tracking-widest">
                                <i data-lucide="layers" class="w-5 h-5 text-primary"></i>
                                Mes Sous-comptes
                            </h2>
                            <p class="text-[10px] text-text-secondary font-black uppercase tracking-wider">Interface de
                                saisie</p>
                        </div>
                        <a href="{{ route('accounting.account.import') }}"
                            class="px-5 py-2.5 bg-primary text-white font-bold rounded-lg hover:opacity-90 transition-all text-xs flex items-center justify-center gap-2">
                            <i data-lucide="upload" class="w-4 h-4"></i> Importer
                        </a>
                    </div>

                    <div class="p-0 max-h-[800px] overflow-y-auto">
                        @if ($allSousComptes->count() > 0)
                            <table class="w-full text-left">
                                <thead
                                    class="bg-slate-50 text-[10px] uppercase text-slate-400 font-bold tracking-widest border-b border-slate-100">
                                    <tr>
                                        <th class="px-4 py-3">Numéro</th>
                                        <th class="px-4 py-3">Intitulé</th>
                                        <th class="px-4 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 text-[13px]">
                                    @foreach ($allSousComptes as $sc)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-4 font-mono font-bold text-primary">
                                                {{ $sc->numero_sous_compte }}</td>
                                            <td class="px-4 py-4 font-semibold text-slate-700">
                                                {{ $sc->libelle }}
                                                <div class="text-[9px] text-slate-400 font-bold uppercase mt-1">Parent:
                                                    {{ $sc->account->code_compte }}</div>
                                            </td>
                                            <td class="px-4 py-4 text-right">
                                                <div class="flex justify-end gap-1">
                                                    <button
                                                        onclick="openEditModal({{ $sc->id }}, '{{ $sc->numero_sous_compte }}', '{{ addslashes($sc->libelle) }}')"
                                                        class="p-2 text-slate-400 hover:text-primary hover:bg-slate-50 rounded-lg transition-all">
                                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                                    </button>
                                                    <button type="button" onclick="confirmDelete({{ $sc->id }})"
                                                        class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-all">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                    <form id="delete-form-{{ $sc->id }}"
                                                        action="{{ route('accounting.account.destroy_sous_compte', $sc->id) }}"
                                                        method="POST" class="hidden">
                                                        @csrf @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="p-16 text-center">
                                <div
                                    class="bg-slate-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i data-lucide="layers" class="w-8 h-8 text-slate-200"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-500">Aucun sous-compte</p>
                                <p class="text-[10px] text-slate-400 mt-1 uppercase font-bold tracking-wider">Configurez vos
                                    comptes auxiliaires</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->

        <!-- Modal Ajouter Sous Compte -->
        <div id="subAccountModal"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] hidden items-center justify-center p-4">
            <div
                class="bg-card-bg rounded-3xl shadow-2xl max-w-md w-full relative overflow-hidden animate-in fade-in zoom-in duration-200 border border-border">
                <div class="p-8 border-b border-border flex justify-between items-center bg-white/50">
                    <h3 class="text-xl font-black text-text-main uppercase tracking-widest">Nouveau Sous-compte</h3>
                    <button type="button" onclick="closeSubAccountModal()"
                        class="text-text-secondary hover:text-rose-500 transition-colors">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <form action="{{ route('accounting.account.store_sous_compte') }}" method="POST" class="p-8">
                    @csrf
                    <input type="hidden" name="account_id" id="sub_account_compte_id">

                    <div class="mb-6">
                        <label class="block text-[11px] font-bold text-slate-400 mb-2 uppercase tracking-wider">Compte de
                            rattachement</label>
                        <div id="modal_compte_label"
                            class="p-4 bg-slate-50 border border-slate-100 rounded-lg text-sm font-bold text-slate-600">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-[11px] font-bold text-slate-400 mb-2 uppercase tracking-wider">Numéro
                            personnalisé</label>
                        <input type="text" name="numero_sous_compte" id="numero_sous_compte" required
                            class="w-full bg-white border border-slate-200 p-4 text-sm font-bold rounded-lg outline-none focus:border-primary transition-all">
                        <p class="text-[10px] text-slate-400 mt-2 font-medium">Doit être unique dans votre comptabilité.
                        </p>
                    </div>

                    <div class="mb-8">
                        <label class="block text-[11px] font-bold text-slate-400 mb-2 uppercase tracking-wider">Libellé
                            descriptif</label>
                        <input type="text" name="libelle" required placeholder="Ex: Client Dupuis, Banque BOA..."
                            class="w-full bg-white border border-slate-200 p-4 text-sm font-semibold rounded-lg outline-none focus:border-primary transition-all">
                    </div>

                    <div class="flex flex-col gap-3 mt-4">
                        <button type="submit"
                            class="w-full bg-primary text-white py-2.5 rounded-lg font-bold hover:opacity-90 transition-all shadow-sm">
                            Créer le sous-compte
                        </button>
                        <button type="button" onclick="closeSubAccountModal()"
                            class="w-full py-2.5 font-bold text-slate-400 text-xs uppercase tracking-widest hover:text-slate-600 transition-all">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Editer Sous Compte -->
        <div id="editSubAccountModal"
            class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[9999] hidden items-center justify-center p-4">
            <div
                class="bg-white rounded-xl shadow-2xl max-w-md w-full relative overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-lg font-bold text-slate-800">Modifier le Sous-compte</h3>
                    <button type="button" onclick="closeEditModal()"
                        class="text-slate-400 hover:text-rose-500 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form id="editForm" method="POST" class="p-8">
                    @csrf @method('PUT')

                    <div class="mb-6">
                        <label class="block text-[11px] font-bold text-slate-400 mb-2 uppercase tracking-wider">Numéro de
                            Sous-compte</label>
                        <input type="text" name="numero_sous_compte" id="edit_numero_sous_compte" required
                            class="w-full bg-white border border-slate-200 p-4 text-sm font-bold rounded-lg outline-none focus:border-primary transition-all">
                    </div>

                    <div class="mb-8">
                        <label class="block text-[11px] font-bold text-slate-400 mb-2 uppercase tracking-wider">Libellé du
                            Sous-compte</label>
                        <input type="text" name="libelle" id="edit_libelle" required
                            class="w-full bg-white border border-slate-200 p-4 text-sm font-semibold rounded-lg outline-none focus:border-primary transition-all">
                    </div>

                    <div class="flex flex-col gap-3 mt-4">
                        <button type="submit"
                            class="w-full bg-primary text-white py-2.5 rounded-lg font-bold hover:opacity-90 transition-all shadow-sm">
                            Mettre à jour
                        </button>
                        <button type="button" onclick="closeEditModal()"
                            class="w-full py-2.5 font-bold text-slate-400 text-xs uppercase tracking-widest hover:text-slate-600 transition-all">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('searchAccount').addEventListener('input', function(e) {
            const search = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.account-row');
            const groups = document.querySelectorAll('.classe-group');

            rows.forEach(row => {
                const text = row.getAttribute('data-search');
                if (text.includes(search)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            groups.forEach(group => {
                const visibleRows = group.querySelectorAll('.account-row[style=""]');
                if (visibleRows.length === 0 && search !== '') {
                    group.style.display = 'none';
                } else {
                    group.style.display = '';
                }
            });
        });

        function openSubAccountModal(accountId, accountCode, accountLabel) {
            document.getElementById('sub_account_compte_id').value = accountId;
            document.getElementById('modal_compte_label').innerText = accountCode + ' - ' + accountLabel;
            document.getElementById('numero_sous_compte').value = accountCode;

            document.getElementById('subAccountModal').classList.remove('hidden');
            document.getElementById('subAccountModal').classList.add('flex');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function closeSubAccountModal() {
            document.getElementById('subAccountModal').classList.add('hidden');
            document.getElementById('subAccountModal').classList.remove('flex');
        }

        function openEditModal(id, numero, libelle) {
            const form = document.getElementById('editForm');
            form.action = `/accounting/compte/sous-compte/${id}`;
            document.getElementById('edit_numero_sous_compte').value = numero;
            document.getElementById('edit_libelle').value = libelle;

            document.getElementById('editSubAccountModal').classList.remove('hidden');
            document.getElementById('editSubAccountModal').classList.add('flex');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function closeEditModal() {
            document.getElementById('editSubAccountModal').classList.add('hidden');
            document.getElementById('editSubAccountModal').classList.remove('flex');
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Supprimer ce sous-compte ?',
                text: "Cette action est irréversible !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#005b82',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                background: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#161615' : '#fff',
                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#fff' : '#000'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>
    </div>
@endsection
