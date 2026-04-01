@extends('layouts.accounting')

@section('title', 'Plan Comptable')

@section('content')
    <div>
        <div class="mb-10 lg:flex lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white">Plan Comptable & Sous-comptes</h1>
                <p class="text-sm text-gray-500">Consultez le référentiel SYSCOHADA et gérez vos sous-comptes personnalisés</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- COLONNE GAUCHE: PLAN COMPTABLE -->
            <div class="xl:col-span-2">
                <div class="bg-card-bg border border-border overflow-hidden shadow-sm rounded-none">
                    <div class="bg-gray-100 dark:bg-black/40 p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between border-b border-border gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-3">
                                <i data-lucide="layout-list" class="w-5 h-5 text-primary"></i>
                                Référentiel des comptes
                            </h2>
                            <p class="text-xs text-gray-400 font-medium mt-1">Liste officielle SYSCOHADA</p>
                        </div>
                        <div class="relative max-w-xs w-full">
                            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                            <input type="text" id="searchAccount" placeholder="Rechercher un compte..."
                                class="w-full bg-white dark:bg-black border border-border pl-10 pr-4 py-2 text-xs font-bold rounded-lg outline-none focus:border-primary transition-all">
                        </div>
                    </div>

                    <div class="max-h-[800px] overflow-y-auto" id="account-list-container">
                        @foreach ($accounts as $classe => $classAccounts)
                            <div class="classe-group">
                                <div class="bg-primary/5 px-6 py-2 border-y border-primary/10 sticky top-0 z-10 text-primary">
                                    <span class="text-[11px] font-bold uppercase tracking-widest">Classe {{ $classe }}</span>
                                </div>
                                <table class="w-full text-left text-xs">
                                    <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                                        @foreach ($classAccounts as $account)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors account-row"
                                                data-search="{{ $account->code_compte }} {{ strtolower($account->libelle) }}">
                                                <td class="px-6 py-4 font-bold text-primary tracking-widest w-32 border-r border-gray-50 dark:border-white/5">
                                                    {{ $account->code_compte }}
                                                </td>
                                                <td class="px-6 py-4 font-bold text-gray-700 dark:text-gray-300">
                                                    {{ $account->libelle }}
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <button onclick="openSubAccountModal({{ $account->id }}, '{{ $account->code_compte }}', '{{ addslashes($account->libelle) }}')" 
                                                        class="text-primary hover:text-white hover:bg-primary font-bold text-[10px] uppercase border border-primary/30 px-3 py-1.5 rounded-none transition-all whitespace-nowrap">
                                                        <i data-lucide="plus" class="w-3 h-3 inline mr-1"></i> Sous-compte
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
                <div class="bg-card-bg border border-border overflow-hidden shadow-sm rounded-none">
                    <div class="bg-primary/10 p-6 border-b border-border flex justify-between items-center">
                        <div>
                            <h2 class="text-lg font-bold text-primary flex items-center gap-3">
                                <i data-lucide="user-plus" class="w-5 h-5"></i>
                                Mes Sous-comptes
                            </h2>
                            <p class="text-[10px] text-gray-500 font-medium mt-1 uppercase tracking-widest">Personnalisation entreprise</p>
                        </div>
                        <a href="{{ route('accounting.account.import') }}" class="flex items-center gap-2 bg-white dark:bg-black border border-primary/20 text-primary px-3 py-2 rounded-none text-[10px] font-bold uppercase hover:bg-primary hover:text-white transition-all">
                            <i data-lucide="upload" class="w-4 h-4"></i>
                            Importer
                        </a>
                    </div>

                    <div class="p-0 max-h-[800px] overflow-y-auto">
                        @if($allSousComptes->count() > 0)
                            <table class="w-full text-left text-xs">
                                <thead class="bg-gray-50 dark:bg-white/5 text-[10px] uppercase text-gray-400 font-bold tracking-widest border-b border-border">
                                    <tr>
                                        <th class="px-4 py-3">Code</th>
                                        <th class="px-4 py-3">Libellé</th>
                                        <th class="px-4 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                                    @foreach($allSousComptes as $sc)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                            <td class="px-4 py-4 font-bold text-primary">{{ $sc->numero_sous_compte }}</td>
                                            <td class="px-4 py-4 font-bold text-gray-700 dark:text-gray-300">
                                                {{ $sc->libelle }}
                                                <div class="text-[9px] text-gray-400 font-normal uppercase mt-1 italic">Parent: {{ $sc->account->code_compte }}</div>
                                            </td>
                                            <td class="px-4 py-4 text-right">
                                                <div class="flex justify-end gap-2">
                                                    <button onclick="openEditModal({{ $sc->id }}, '{{ $sc->numero_sous_compte }}', '{{ addslashes($sc->libelle) }}')" 
                                                        class="p-1.5 text-blue-500 hover:bg-blue-500/10 rounded-none transition-colors">
                                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                                    </button>
                                                    <button type="button" 
                                                        onclick="confirmDelete({{ $sc->id }})"
                                                        class="p-1.5 text-red-500 hover:bg-red-500/10 rounded-none transition-colors">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                    <form id="delete-form-{{ $sc->id }}" action="{{ route('accounting.account.destroy_sous_compte', $sc->id) }}" method="POST" class="hidden">
                                                        @csrf @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="p-10 text-center">
                                <i data-lucide="folder-plus" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
                                <p class="text-sm text-gray-500 italic">Aucun sous-compte créé pour le moment.</p>
                                <p class="text-[10px] text-gray-400 mt-2 uppercase">Utilisez le bouton "+" à gauche pour commencer</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        
        <!-- Modal Ajouter Sous Compte -->
        <div id="subAccountModal" class="fixed inset-0 bg-black/50 z-[9999] hidden items-center justify-center p-4">
            <div class="bg-card-bg border border-border rounded-none shadow-2xl max-w-md w-full relative overflow-hidden animate-fade-down">
                <div class="bg-primary/5 p-6 border-b border-border flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Nouveau Sous-compte</h3>
                    <button type="button" onclick="closeSubAccountModal()" class="text-gray-400 hover:text-red-500 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                
                <form action="{{ route('accounting.account.store_sous_compte') }}" method="POST" class="p-6">
                    @csrf
                    <input type="hidden" name="account_id" id="sub_account_compte_id">
                    
                    <div class="mb-4">
                        <label class="block text-[10px] uppercase font-bold text-gray-400 mb-2 tracking-widest">Compte Parent</label>
                        <div id="modal_compte_label" class="p-3 bg-gray-50 dark:bg-white/5 rounded-none text-sm font-bold text-gray-700 dark:text-gray-300 border border-gray-100"></div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-[10px] uppercase font-bold text-gray-400 mb-2 tracking-widest">Numéro de Sous-compte</label>
                        <input type="text" name="numero_sous_compte" id="numero_sous_compte" required 
                            class="w-full bg-white dark:bg-black border border-border p-3 text-sm font-bold rounded-none outline-none focus:border-primary transition-all">
                        <p class="text-[10px] text-gray-500 mt-1 italic">Le numéro doit être unique et différent des comptes principaux.</p>
                    </div>

                    <div class="mb-6">
                        <label class="block text-[10px] uppercase font-bold text-gray-400 mb-2 tracking-widest">Libellé du Sous-compte</label>
                        <input type="text" name="libelle" required placeholder="Ex: Client X..."
                            class="w-full bg-white dark:bg-black border border-border p-3 text-sm rounded-none outline-none focus:border-primary transition-all">
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeSubAccountModal()" 
                            class="px-4 py-2 font-bold text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                            Annuler
                        </button>
                        <button type="submit" 
                            class="bg-primary text-white px-6 py-2 rounded-none font-bold hover:bg-primary-light transition-colors">
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Editer Sous Compte -->
        <div id="editSubAccountModal" class="fixed inset-0 bg-black/50 z-[9999] hidden items-center justify-center p-4">
            <div class="bg-card-bg border border-border rounded-none shadow-2xl max-w-md w-full relative overflow-hidden animate-fade-down">
                <div class="bg-primary/5 p-6 border-b border-border flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Modifier le Sous-compte</h3>
                    <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-red-500 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                
                <form id="editForm" method="POST" class="p-6">
                    @csrf @method('PUT')
                    
                    <div class="mb-4">
                        <label class="block text-[10px] uppercase font-bold text-gray-400 mb-2 tracking-widest">Numéro de Sous-compte</label>
                        <input type="text" name="numero_sous_compte" id="edit_numero_sous_compte" required 
                            class="w-full bg-white dark:bg-black border border-border p-3 text-sm font-bold rounded-none outline-none focus:border-primary transition-all">
                    </div>

                    <div class="mb-6">
                        <label class="block text-[10px] uppercase font-bold text-gray-400 mb-2 tracking-widest">Libellé du Sous-compte</label>
                        <input type="text" name="libelle" id="edit_libelle" required
                            class="w-full bg-white dark:bg-black border border-border p-3 text-sm rounded-none outline-none focus:border-primary transition-all">
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeEditModal()" 
                            class="px-4 py-2 font-bold text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                            Annuler
                        </button>
                        <button type="submit" 
                            class="bg-primary text-white px-6 py-2 rounded-none font-bold hover:bg-primary-light transition-colors">
                            Mettre à jour
                        </button>
                    </div>
                </form>
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
                if(typeof lucide !== 'undefined') lucide.createIcons();
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
                if(typeof lucide !== 'undefined') lucide.createIcons();
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
                    confirmButtonColor: '#003366',
                    cancelButtonColor: '#d33',
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
