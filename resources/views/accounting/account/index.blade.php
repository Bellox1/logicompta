@extends('layouts.accounting')

@section('title', 'Plan Comptable')

@section('styles')
    <style>
        .account-table thead th {
            background: #f8fafc;
            border-top: none;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            padding: 15px 25px;
        }

        .account-table tbody td {
            padding: 15px 25px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .classe-header {
            background: #f1f5f9;
            padding: 10px 25px;
            font-weight: 800;
            font-size: 11px;
            color: var(--primary-color);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .premium-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            overflow: hidden;
        }

        .search-container {
            position: relative;
            display: flex;
            align-items: center;
            max-width: 350px;
        }

        .search-container input::placeholder {
            color: #94a3b8;
            font-weight: 500;
        }

        .modal-premium .modal-content {
            border-radius: 25px;
            border: none;
            box-shadow: 0 25px 50px rgba(0,0,0,0.1);
        }

        .modal-premium .modal-header {
            border-bottom: 1px solid #f1f5f9;
            padding: 25px 35px;
        }

        .modal-premium .modal-body {
            padding: 35px;
        }
    </style>
@endsection

@section('content')
    <div class="mb-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <h1 class="h3 font-weight-bold text-dark mb-1" style="font-family: 'Manrope';">Plan Comptable & Sous-comptes</h1>
            <p class="text-muted small font-weight-bold">Consultez le référentiel SYSCOHADA et gérez vos comptes auxiliaires.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('accounting.journals-settings.index') }}" class="btn btn-white btn-sm font-weight-bold px-3 py-2 border shadow-sm rounded-pill text-uppercase" style="font-size: 10px; letter-spacing: 1px;">
                <span class="material-symbols-outlined align-middle mr-1" style="font-size: 18px;">settings</span> Configuration Journaux
            </a>
        </div>
    </div>

    <div class="row">
        <!-- PLAN COMPTABLE -->
        <div class="col-xl-8 mb-4">
            <div class="premium-card h-100 d-flex flex-column">
                <div class="p-4 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                    <h5 class="font-weight-bold text-dark mb-3 mb-md-0 d-flex align-items-center" style="font-family: 'Manrope';">
                        <span class="material-symbols-outlined mr-2 text-primary">book</span> RÉFÉRENTIEL SYSCOHADA
                    </h5>
                    <div class="search-container w-100 px-3" style="background: #f1f5f9; border-radius: 10px; height: 40px; display: flex; align-items: center;">
                        <span class="material-symbols-outlined" style="font-size: 20px; color: #64748b; flex-shrink: 0;">search</span>
                        <input type="text" id="searchAccount" class="border-0 bg-transparent ml-2 flex-grow-1" style="box-shadow: none; font-size: 12px; font-weight: 600; outline: none; height: 100%;" placeholder="Rechercher dans le plan...">
                    </div>
                </div>

                <div class="table-responsive flex-grow-1" style="max-height: 700px; overflow-y: auto;" id="account-list-container">
                    <table class="table account-table mb-0">
                        @foreach ($accounts as $classe => $classAccounts)
                            <thead>
                                <tr>
                                    <th colspan="3" class="classe-header">CLASSE {{ $classe }}</th>
                                </tr>
                                <tr>
                                    <th style="width: 100px;">N°</th>
                                    <th>INTITULÉ</th>
                                    <th class="text-right">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($classAccounts as $account)
                                    <tr class="account-row" data-search="{{ $account->code_compte }} {{ strtolower($account->libelle) }}">
                                        <td class="font-weight-bold text-primary font-mono" style="width: 100px;">{{ $account->code_compte }}</td>
                                        <td class="font-weight-bold text-dark text-truncate" style="max-width: 300px;">{{ $account->libelle }}</td>
                                        <td class="text-right">
                                            <button onclick="openSubAccountModal({{ $account->id }}, '{{ $account->code_compte }}', '{{ addslashes($account->libelle) }}')" 
                                                class="btn btn-link text-primary font-weight-bold text-uppercase p-0" style="font-size: 10px; letter-spacing: 1px;">
                                                <span class="material-symbols-outlined align-middle mr-1" style="font-size: 16px;">add_circle</span> Sous-compte
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>

        <!-- MES SOUS-COMPTES -->
        <div class="col-xl-4 mb-4">
            <div class="premium-card h-100 d-flex flex-column">
                <div class="p-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="font-weight-bold text-dark mb-0 d-flex align-items-center" style="font-family: 'Manrope';">
                            <span class="material-symbols-outlined mr-2 text-primary">layers</span> MES AUXILIAIRES
                        </h5>
                        <a href="{{ route('accounting.account.import') }}" class="btn btn-primary btn-sm px-3 font-weight-bold rounded-lg shadow-sm">
                            <span class="material-symbols-outlined align-middle mr-1" style="font-size: 18px;">upload</span> Import
                        </a>
                    </div>
                    <div class="search-container w-100 px-3" style="background: #f1f5f9; border-radius: 10px; height: 35px; display: flex; align-items: center;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #64748b; flex-shrink: 0;">person_search</span>
                        <input type="text" id="searchSubAccount" class="border-0 bg-transparent ml-2 flex-grow-1" style="box-shadow: none; font-size: 11px; font-weight: 600; outline: none; height: 100%;" placeholder="Filtrer mes auxiliaires...">
                    </div>
                </div>

                <div class="flex-grow-1" style="max-height: 700px; overflow-y: auto;">
                    @if ($allSousComptes->count() > 0)
                        <table class="table account-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">N°</th>
                                    <th>LIBELLÉ</th>
                                    <th class="text-right">ACT.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($allSousComptes as $sc)
                                    <tr class="sub-account-row" data-search="{{ $sc->numero_sous_compte }} {{ strtolower($sc->libelle) }}">
                                        <td class="font-weight-bold text-primary font-mono small" style="white-space: nowrap;">{{ $sc->numero_sous_compte }}</td>
                                        <td style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">
                                            <span class="font-weight-bold text-dark small">{{ $sc->libelle }}</span>
                                            <span class="text-muted ml-1" style="font-size: 8px;">({{ $sc->account->code_compte }})</span>
                                        </td>
                                        <td class="text-right">
                                            <div class="d-flex justify-content-end align-items-center">
                                                <button onclick="openEditModal({{ $sc->id }}, '{{ $sc->numero_sous_compte }}', '{{ addslashes($sc->libelle) }}')" 
                                                    class="btn btn-link text-muted p-1">
                                                    <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
                                                </button>
                                                <button onclick="confirmDelete({{ $sc->id }})" class="btn btn-link text-muted p-1">
                                                    <span class="material-symbols-outlined" style="font-size: 16px;">delete</span>
                                                </button>
                                                <form id="delete-form-{{ $sc->id }}" action="{{ route('accounting.account.destroy_sous_compte', $sc->id) }}" method="POST" class="d-none">
                                                    @csrf @method('DELETE')
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-5 text-center my-5">
                            <span class="material-symbols-outlined text-muted opacity-25 mb-3" style="font-size: 60px;">list_alt</span>
                            <p class="small font-weight-bold text-muted">Aucun sous-compte créé</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL AJOUT -->
    <div class="modal fade modal-premium" id="subAccountModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header d-block text-center border-0 pb-0 position-relative">
                    <button type="button" class="close position-absolute" data-dismiss="modal" style="right: 25px; top: 25px;">&times;</button>
                    <h3 class="modal-title font-weight-bold text-dark" style="font-family: 'Manrope';">Nouveau Sous-compte</h3>
                    <p class="text-muted small font-weight-bold">Configurez un compte auxiliaire personnalisé</p>
                </div>
                <div class="modal-body">
                    <form action="{{ route('accounting.account.store_sous_compte') }}" method="POST">
                        @csrf
                        <input type="hidden" name="account_id" id="sub_account_compte_id">
                        
                        <div class="form-group mb-4">
                            <label class="small text-uppercase font-weight-bold text-muted">Rattachement</label>
                            <div id="modal_compte_label" class="p-3 bg-light rounded-lg font-weight-bold border text-primary small"></div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="small text-uppercase font-weight-bold text-muted">Numéro Personnalisé</label>
                            <input type="text" name="numero_sous_compte" id="numero_sous_compte" class="form-control form-control-lg font-weight-bold rounded-xl border-2" required>
                        </div>

                        <div class="form-group mb-5">
                            <label class="small text-uppercase font-weight-bold text-muted">Intitulé / Libellé</label>
                            <input type="text" name="libelle" class="form-control form-control-lg font-weight-bold rounded-xl border-2" placeholder="Ex: Client ABC, Banque X..." required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold rounded-xl shadow-lg">CRÉER LE SOUS-COMPTE</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT -->
    <div class="modal fade modal-premium" id="editSubAccountModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header d-block text-center border-0 pb-0 position-relative">
                    <button type="button" class="close position-absolute" data-dismiss="modal" style="right: 25px; top: 25px;">&times;</button>
                    <h3 class="modal-title font-weight-bold text-dark" style="font-family: 'Manrope';">Modifier l'Auxiliaire</h3>
                    <p class="text-muted small font-weight-bold">Mise à jour des informations du compte</p>
                </div>
                <div class="modal-body">
                    <form id="editForm" method="POST">
                        @csrf @method('PUT')
                        <div class="form-group mb-4">
                            <label class="small text-uppercase font-weight-bold text-muted">Numéro</label>
                            <input type="text" name="numero_sous_compte" id="edit_numero_sous_compte" class="form-control form-control-lg font-weight-bold rounded-xl border-2" required>
                        </div>
                        <div class="form-group mb-5">
                            <label class="small text-uppercase font-weight-bold text-muted">Libellé</label>
                            <input type="text" name="libelle" id="edit_libelle" class="form-control form-control-lg font-weight-bold rounded-xl border-2" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold rounded-xl shadow-lg">ENREGISTRER LES MODIFICATIONS</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        // Recherche Plan Comptable
        document.getElementById('searchAccount').addEventListener('input', function(e) {
            const search = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.account-row');
            rows.forEach(row => {
                const text = row.getAttribute('data-search');
                row.style.display = text.includes(search) ? '' : 'none';
            });
        });

        // Recherche Sous-comptes
        document.getElementById('searchSubAccount').addEventListener('input', function(e) {
            const search = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.sub-account-row');
            rows.forEach(row => {
                const text = row.getAttribute('data-search');
                row.style.display = text.includes(search) ? '' : 'none';
            });
        });

        function openSubAccountModal(accountId, accountCode, accountLabel) {
            document.getElementById('sub_account_compte_id').value = accountId;
            document.getElementById('modal_compte_label').innerText = accountCode + ' - ' + accountLabel;
            document.getElementById('numero_sous_compte').value = accountCode;
            $('#subAccountModal').modal('show');
        }

        function openEditModal(id, numero, libelle) {
            const form = document.getElementById('editForm');
            form.action = `/accounting/compte/sous-compte/${id}`;
            document.getElementById('edit_numero_sous_compte').value = numero;
            document.getElementById('edit_libelle').value = libelle;
            $('#editSubAccountModal').modal('show');
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Supprimer ce sous-compte ?',
                text: "Cette action est irréversible !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#005b82',
                confirmButtonText: 'OUI, SUPPRIMER',
                cancelButtonText: 'ANNULER'
            }).then((result) => {
                if (result.isConfirmed) document.getElementById('delete-form-' + id).submit();
            });
        }
    </script>
@endsection
