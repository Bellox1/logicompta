@extends('layouts.accounting')

@section('title', 'Profil Utilisateur')

@section('styles')
    <style>
        .profile-page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
        }

        .org-badge-top {
            background: #f1f5f9;
            color: var(--primary-color);
            padding: 10px 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 800;
            font-size: 13px;
            letter-spacing: 1px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        }

        .profile-hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, #003d57 100%);
            border-radius: 25px;
            padding: 40px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 91, 130, 0.1);
            margin-bottom: 25px;
        }

        .profile-avatar {
            width: 90px;
            height: 90px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .profile-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            border: 1px solid #f1f5f9;
            padding: 30px;
            height: 100%;
            position: relative;
        }

        .form-label-premium {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin-bottom: 10px;
            display: block;
        }

        .form-control-premium {
            height: 52px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 0 18px;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.2s ease;
            background: #f8fafc;
        }

        .form-control-premium:focus {
            background: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 91, 130, 0.05);
        }

        .btn-premium-save {
            height: 52px;
            border-radius: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        .btn-premium-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f8fafc;
        }

        .detail-row:last-child { border-bottom: none; }
    </style>
@endsection

@section('content')
    @php
        $user = auth()->user();
        $entreprise = $user->entreprise;
        $isAdmin = $user->role === 'admin';
    @endphp

    <div class="profile-page-header">
        <div>
            <h1 class="h3 font-weight-bold mb-1 text-dark" style="font-family: 'Manrope';">Mon Profil</h1>
            <p class="text-muted small mb-0 font-weight-bold">Gérez vos informations et sécurisez votre accès.</p>
        </div>
        @if($entreprise)
            <div class="org-badge-top">
                CODE : {{ $entreprise->code }}
            </div>
        @endif
    </div>

    <div class="row">
        <!-- Sidebar Info -->
        <div class="col-lg-4 mb-4">
            <div class="profile-hero">
                <div class="profile-avatar">
                    <span class="material-symbols-outlined" style="font-size: 48px; color: white;">person</span>
                </div>
                <h2 class="h4 font-weight-bold mb-1">{{ $user->name }}</h2>
                <p class="small font-weight-bold mb-0 opacity-80">{{ $user->email }}</p>
                <span class="material-symbols-outlined position-absolute" style="right: -20px; bottom: -20px; font-size: 150px; opacity: 0.1;">manage_accounts</span>
            </div>

            <div class="profile-card mb-4">
                <h6 class="form-label-premium mb-3">Récapitulatif</h6>
                <div class="detail-row">
                    <span class="small font-weight-bold text-muted uppercase">Privilège</span>
                    <span class="badge badge-primary px-3 py-1 rounded-pill font-weight-bold text-uppercase" style="font-size: 10px; background: var(--primary-color);">{{ $user->role }}</span>
                </div>
                <div class="detail-row">
                    <span class="small font-weight-bold text-muted uppercase">Statut</span>
                    <span class="badge badge-success-light text-success font-weight-bold p-0">Actif</span>
                </div>
                <div class="detail-row">
                    <span class="small font-weight-bold text-muted uppercase">Adhésion</span>
                    <span class="small font-weight-bold text-dark">{{ $user->created_at->format('M Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="small font-weight-bold text-muted uppercase">Dernière MaJ</span>
                    <span class="small font-weight-bold text-dark">{{ $user->updated_at->format('d/m/Y') }}</span>
                </div>
            </div>
            
            @if ($isAdmin && $entreprise)
                <div class="profile-card">
                    <h6 class="form-label-premium mb-4">Organisation</h6>
                    <div class="form-group mb-0">
                        <label class="form-label-premium">Nom de l'entreprise</label>
                        <input type="text" id="editEntrepriseName" value="{{ $entreprise->name }}" 
                            data-original="{{ $entreprise->name }}" class="form-control form-control-premium">
                        <p class="extra-small text-muted mt-2 mb-3">Utilisé pour vos documents officiels.</p>
                        <button onclick="saveEntreprise()" class="btn btn-success btn-block btn-premium-save">Mettre à jour</button>
                    </div>
                </div>
            @endif
        </div>

        <!-- Forms -->
        <div class="col-lg-8">
            <!-- Global Info -->
            <div class="profile-card mb-4">
                <h5 class="font-weight-bold mb-4" style="font-family: 'Manrope'; display: flex; align-items: center; gap: 10px;">
                    <span class="material-symbols-outlined text-primary">contact_page</span>
                    Informations Personnelles
                </h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label-premium">Nom Complet</label>
                        <input type="text" id="editName" value="{{ $user->name }}" 
                            data-original="{{ $user->name }}" class="form-control form-control-premium">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label-premium">Email de contact</label>
                        <input type="email" id="editEmail" value="{{ $user->email }}" 
                            data-original="{{ $user->email }}" class="form-control form-control-premium">
                    </div>
                </div>
                <div class="text-right mt-3">
                    <button onclick="saveProfile()" class="btn btn-primary btn-premium-save px-5">Enregistrer les modifications</button>
                </div>
            </div>

            <!-- Security -->
            <div class="profile-card mb-4">
                <h5 class="font-weight-bold mb-4" style="font-family: 'Manrope'; display: flex; align-items: center; gap: 10px;">
                    <span class="material-symbols-outlined text-warning" style="color: #f59e0b !important;">lock</span>
                    Sécurité & Accès
                </h5>
                <div class="form-group mb-4">
                    <label class="form-label-premium">Mot de passe actuel</label>
                    <input type="password" id="current_password" class="form-control form-control-premium" placeholder="••••••••">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label-premium">Nouveau mot de passe</label>
                        <input type="password" id="new_password" class="form-control form-control-premium" placeholder="Min. 8 caractères">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label-premium">Confirmation</label>
                        <input type="password" id="new_password_confirmation" class="form-control form-control-premium" placeholder="Répéter">
                    </div>
                </div>
                <div class="text-right mt-3">
                    <button onclick="savePassword()" class="btn btn-warning btn-premium-save px-5 text-white">Changer le mot de passe</button>
                </div>
            </div>

            <div class="text-center py-4">
                <button onclick="showDeleteModal()" class="btn btn-link text-danger font-weight-bold small text-uppercase" style="letter-spacing: 1px;">Supprimer mon compte définitivement</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        async function saveProfile() {
            const elName = document.getElementById('editName');
            const elEmail = document.getElementById('editEmail');
            
            const name = elName.value.trim();
            const email = elEmail.value.trim();

            if (name === elName.getAttribute('data-original').trim() && email === elEmail.getAttribute('data-original').trim()) {
                Swal.fire({ title: 'Information', text: 'Aucune modification détectée.', icon: 'info', timer: 1500, showConfirmButton: false });
                return;
            }

            try {
                const response = await fetch('{{ route('profile.update') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name, email })
                });
                const data = await response.json();
                if (response.ok) {
                    Swal.fire({ title: 'Profil mis à jour', icon: 'success', showConfirmButton: false, timer: 1500 }).then(() => window.location.reload());
                } else { Swal.fire({ title: 'Erreur', text: data.message, icon: 'error' }); }
            } catch (error) { console.error(error); }
        }

        async function savePassword() {
            const current_password = document.getElementById('current_password').value;
            const password = document.getElementById('new_password').value;
            const password_confirmation = document.getElementById('new_password_confirmation').value;

            if (!current_password || !password) return;

            try {
                const response = await fetch('{{ route('profile.password') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ current_password, password, password_confirmation })
                });
                const data = await response.json();
                if (response.ok) {
                    Swal.fire({ title: 'Succès', text: 'Mot de passe modifié', icon: 'success' });
                    document.querySelectorAll('input[type="password"]').forEach(i => i.value = '');
                } else { Swal.fire({ title: 'Échec', text: data.message, icon: 'error' }); }
            } catch (error) { console.error(error); }
        }

        async function saveEntreprise() {
            const elName = document.getElementById('editEntrepriseName');
            const name = elName.value.trim();

            if (name === elName.getAttribute('data-original').trim()) {
                Swal.fire({ title: 'Information', text: 'Aucune modification détectée.', icon: 'info', timer: 1500, showConfirmButton: false });
                return;
            }

            try {
                const response = await fetch('{{ route('entreprise.update') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name })
                });
                if (response.ok) {
                    Swal.fire({ title: 'Entreprise mise à jour', icon: 'success', timer: 1500, showConfirmButton: false }).then(() => window.location.reload());
                }
            } catch (error) { console.error(error); }
        }

        function showDeleteModal() {
            Swal.fire({ 
                title: 'Action critique', 
                text: 'La suppression de compte doit être effectuée par un administrateur système. Veuillez contacter le support.', 
                icon: 'warning',
                confirmButtonColor: 'var(--primary-color)'
            });
        }
    </script>
@endsection
