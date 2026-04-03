@extends('layouts.accounting')

@section('title', 'Profil Utilisateur')

@section('content')
    <div class="w-full space-y-8 pb-10">
        <!-- Header -->
        <div>
            <h1 class="text-4xl font-black text-text-main flex items-center gap-3 uppercase tracking-tight">
                <i data-lucide="user-circle" class="w-10 h-10 text-primary"></i>
                Mon Profil
            </h1>
            <p class="text-text-secondary font-medium mt-2">Gérez vos informations et sécurisez votre accès à la plateforme.
            </p>
        </div>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Left Column: Summary & Quick Info -->
            <div class="space-y-6">
                <div class="bg-primary rounded-[2.5rem] p-8 text-white shadow-xl relative overflow-hidden">
                    <div class="absolute -right-10 -bottom-10 opacity-10">
                        <i data-lucide="user" class="w-48 h-48"></i>
                    </div>
                    <div class="relative z-10">
                        <div
                            class="w-20 h-20 bg-white/20 backdrop-blur-md rounded-3xl flex items-center justify-center text-3xl font-black mb-6">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <h2 class="text-2xl font-black mb-1">{{ auth()->user()->name }}</h2>
                        <p class="text-white/80 font-bold text-sm mb-6">{{ auth()->user()->email }}</p>
                        <span
                            class="inline-block px-4 py-1.5 bg-white text-primary rounded-xl text-[10px] font-black uppercase tracking-widest">
                            {{ ucfirst(auth()->user()->role) }}
                        </span>
                    </div>
                </div>

                <div class="bg-card-bg border border-border rounded-[2.5rem] p-8 shadow-sm">
                    <h4 class="text-xs font-black text-text-main uppercase tracking-widest mb-6">Détails du compte</h4>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-3">
                            <span class="text-text-secondary text-sm font-bold">Inscrit le</span>
                            <span
                                class="text-text-main font-black text-xs">{{ auth()->user()->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
                
                @php
                    $user = auth()->user();
                    $entreprise = $user->entreprise;
                    $isAdmin = $user->role === 'admin';
                @endphp

                @if ($isAdmin && $entreprise)
                    <!-- Section 3: Enterprise Info -->
                    <div
                        class="bg-card-bg border border-border rounded-[2.5rem] p-8 shadow-sm border-l-8 border-l-emerald-500">
                        <div class="flex flex-col gap-4 mb-6">
                            <h2 class="text-lg font-black text-text-main uppercase tracking-tight">Paramètres Entreprise
                            </h2>
                            <div
                                class="self-start px-4 py-2 bg-emerald-500/10 text-emerald-600 rounded-xl border border-emerald-500/20 font-mono text-xs font-bold uppercase">
                                Code: {{ $entreprise->code }}
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label
                                    class="block text-[10px] font-black text-text-secondary uppercase tracking-widest mb-3">Nom
                                    de l'organisation</label>
                                <input type="text" id="editEntrepriseName" value="{{ $entreprise->name }}"
                                    class="w-full px-5 py-3 bg-white dark:bg-black border border-border rounded-xl text-sm focus:border-primary transition-all font-bold">
                            </div>
                            <div class="flex justify-end pt-2">
                                <button onclick="saveEntreprise()"
                                    class="w-full py-3 bg-emerald-500 text-white font-black rounded-xl text-xs uppercase tracking-widest shadow-xl shadow-emerald-500/20 hover:scale-105 active:scale-95 transition-all">Mettre
                                    à jour</button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column: Forms -->
            <div class="lg:col-span-2 space-y-8">

                <!-- Section 1: Personal Information -->
                <div class="bg-card-bg border border-border rounded-[2.5rem] p-10 shadow-sm">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-xl font-black text-text-main uppercase tracking-tight">Informations Personnelles
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                        <div>
                            <label
                                class="block text-[10px] font-black text-text-secondary uppercase tracking-widest mb-3">Nom
                                complet</label>
                            <input type="text" id="editName" value="{{ auth()->user()->name }}"
                                class="w-full px-5 py-4 bg-white dark:bg-black border border-border rounded-2xl text-sm focus:border-primary transition-all font-bold">
                        </div>
                        <div>
                            <label
                                class="block text-[10px] font-black text-text-secondary uppercase tracking-widest mb-3">Email
                                de contact</label>
                            <input type="email" id="editEmail" value="{{ auth()->user()->email }}"
                                class="w-full px-5 py-4 bg-white dark:bg-black border border-border rounded-2xl text-sm focus:border-primary transition-all font-bold">
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end">
                        <button onclick="saveProfile()"
                            class="px-8 py-4 bg-primary text-white font-black rounded-2xl text-xs uppercase tracking-widest shadow-xl shadow-primary/20 hover:scale-105 active:scale-95 transition-all">Mettre
                            à jour le profil</button>
                    </div>
                </div>

                <!-- Section 2: Security (Password Change) -->
                <div class="bg-card-bg border border-border rounded-[2.5rem] p-10 shadow-sm border-l-8 border-l-amber-500">
                    <h2 class="text-xl font-black text-text-main uppercase tracking-tight mb-8">Sécurité & Accès</h2>
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label
                                class="block text-[10px] font-black text-text-secondary uppercase tracking-widest mb-3">Mot
                                de passe actuel</label>
                            <div class="relative">
                                <input type="password" id="current_password" placeholder="••••••••"
                                    class="w-full pl-5 pr-12 py-4 bg-white dark:bg-black border border-border rounded-2xl text-sm focus:border-primary transition-all font-bold">
                                <button type="button" onclick="togglePassword('current_password', this)"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-text-secondary hover:text-primary transition-colors">
                                    <i data-lucide="eye" class="w-5 h-5"></i>
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label
                                    class="block text-[10px] font-black text-text-secondary uppercase tracking-widest mb-3">Nouveau
                                    mot de passe</label>
                                <div class="relative">
                                    <input type="password" id="new_password" placeholder="Minimum 8 caractères"
                                        class="w-full pl-5 pr-12 py-4 bg-white dark:bg-black border border-border rounded-2xl text-sm focus:border-primary transition-all font-bold">
                                    <button type="button" onclick="togglePassword('new_password', this)"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-text-secondary hover:text-primary transition-colors">
                                        <i data-lucide="eye" class="w-5 h-5"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-black text-text-secondary uppercase tracking-widest mb-3">Confirmation</label>
                                <div class="relative">
                                    <input type="password" id="new_password_confirmation"
                                        placeholder="Répéter le mot de passe"
                                        class="w-full pl-5 pr-12 py-4 bg-white dark:bg-black border border-border rounded-2xl text-sm focus:border-primary transition-all font-bold">
                                    <button type="button" onclick="togglePassword('new_password_confirmation', this)"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-text-secondary hover:text-primary transition-colors">
                                        <i data-lucide="eye" class="w-5 h-5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end">
                        <button onclick="savePassword()"
                            class="px-8 py-4 bg-amber-500 text-white font-black rounded-2xl text-xs uppercase tracking-widest shadow-xl shadow-amber-500/20 hover:scale-105 active:scale-95 transition-all">Changer
                            le mot de passe</button>
                    </div>
                </div>


                <!-- Section 4: Critical Actions -->
                <div class="pt-8 text-center">
                    <button onclick="showDeleteModal()"
                        class="text-[10px] font-black text-rose-500 uppercase tracking-[0.2em] hover:text-rose-700 transition-colors">
                        Désactiver ou supprimer mon compte définitivement
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        async function saveProfile() {
            const nameInput = document.getElementById('editName');
            const emailInput = document.getElementById('editEmail');
            const name = nameInput.value;
            const email = emailInput.value;

            if (name === nameInput.defaultValue && email === emailInput.defaultValue) {
                Swal.fire({
                    title: 'Aucun changement',
                    text: 'Vos informations sont déjà à jour.',
                    icon: 'info',
                    timer: 2000,
                    showConfirmButton: false,
                    background: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#161615' : '#fff',
                    color: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#fff' : '#000'
                });
                return;
            }

            try {
                const response = await fetch('{{ route('profile.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        name,
                        email
                    })
                });

                const data = await response.json();
                if (response.ok) {
                    Swal.fire({
                        title: 'Profil Mis à Jour',
                        text: 'Vos informations ont été sauvegardées.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        background: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#161615' :
                            '#fff',
                        color: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#fff' : '#000'
                    }).then(() => window.location.reload());
                } else {
                    Swal.fire({
                        title: 'Erreur',
                        text: data.message || 'Mise à jour échouée',
                        icon: 'error',
                        background: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#161615' :
                            '#fff',
                    });
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        }

        async function savePassword() {
            const current_password = document.getElementById('current_password').value;
            const password = document.getElementById('new_password').value;
            const password_confirmation = document.getElementById('new_password_confirmation').value;

            if (!current_password || !password || !password_confirmation) {
                Swal.fire({
                    title: 'Attention',
                    text: 'Veuillez remplir tous les champs de sécurité.',
                    icon: 'warning',
                    background: '#fff'
                });
                return;
            }

            try {
                const response = await fetch('{{ route('profile.password') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        current_password,
                        password,
                        password_confirmation
                    })
                });

                const data = await response.json();
                if (response.ok) {
                    document.getElementById('current_password').value = '';
                    document.getElementById('new_password').value = '';
                    document.getElementById('new_password_confirmation').value = '';
                    Swal.fire({
                        title: 'Mot de passe changé',
                        text: 'Votre sécurité a été mise à jour.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        background: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#161615' :
                            '#fff',
                        color: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#fff' : '#000'
                    });
                } else {
                    Swal.fire({
                        title: 'Échec',
                        text: data.message ||
                            'Le mot de passe actuel est erroné ou les nouveaux ne correspondent pas.',
                        icon: 'error',
                        background: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#161615' :
                            '#fff',
                    });
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        }

        async function saveEntreprise() {
            const nameInput = document.getElementById('editEntrepriseName');
            const name = nameInput.value;

            if (name === nameInput.defaultValue) {
                Swal.fire({
                    title: 'Aucun changement',
                    text: 'Le nom de l\'organisation est déjà à jour.',
                    icon: 'info',
                    timer: 2000,
                    showConfirmButton: false,
                    background: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#161615' : '#fff',
                    color: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#fff' : '#000'
                });
                return;
            }

            try {
                const response = await fetch('{{ route('entreprise.update') }}', {                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        name
                    })
                });

                const data = await response.json();
                if (response.ok) {
                    Swal.fire({
                        title: 'Entreprise mise à jour',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        background: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#161615' :
                            '#fff',
                        color: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#fff' : '#000'
                    }).then(() => window.location.reload());
                } else {
                    Swal.fire({
                        title: 'Erreur',
                        text: data.message || 'Erreur',
                        icon: 'error',
                        background: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#161615' :
                            '#fff'
                    });
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        }

        function showDeleteModal() {
            Swal.fire({
                title: 'Action critique',
                text: 'Pour supprimer votre compte, veuillez contacter le support à support@comptafriq.com.',
                icon: 'warning',
                background: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#161615' : '#fff',
                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#fff' : '#000'
            });
        }

        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const iconContainer = btn;

            if (input.type === 'password') {
                input.type = 'text';
                iconContainer.innerHTML = '<i data-lucide="eye-off" class="w-5 h-5"></i>';
            } else {
                input.type = 'password';
                iconContainer.innerHTML = '<i data-lucide="eye" class="w-5 h-5"></i>';
            }
            lucide.createIcons();
        }

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
@endsection
