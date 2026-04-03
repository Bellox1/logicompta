<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comptafriq - Configuration</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            darkMode: 'media',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    },
                    colors: {
                        primary: '#005b82',
                        'primary-light': '#004d99',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-12px);
            }
        }

        .animate-fade-up {
            animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        .delay-1 { animation-delay: 0.1s; opacity: 0; }
        .delay-2 { animation-delay: 0.2s; opacity: 0; }
        .delay-3 { animation-delay: 0.3s; opacity: 0; }
        .delay-4 { animation-delay: 0.4s; opacity: 0; }
        .delay-5 { animation-delay: 0.5s; opacity: 0; }

        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>

<body class="bg-[#020617] text-white min-h-screen flex flex-col items-center justify-between p-8 overflow-x-hidden antialiased">

    {{-- Background Decoration --}}
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-[20%] -left-[10%] w-[60%] h-[60%] rounded-full bg-primary/10 blur-[120px]"></div>
        <div class="absolute -bottom-[20%] -right-[10%] w-[50%] h-[50%] rounded-full bg-primary/10 blur-[100px]"></div>
    </div>

    {{-- Top Section: Logo & Welcome --}}
    <div class="relative z-10 w-full max-w-2xl mt-12 text-center animate-fade-up">
        <div class="animate-float mb-12">
            <img src="{{ asset('storage/images/logo.png') }}" alt="Comptafriq" class="w-48 mx-auto drop-shadow-[0_0_30px_rgba(0,51,102,0.4)]">
        </div>
        <h1 class="text-5xl font-black tracking-tight mb-4">Initialisation de l'espace</h1>
        <p class="text-xl text-slate-300/70 max-w-lg mx-auto leading-relaxed">
            Dernière étape ! Pour finaliser votre configuration, choisissez comment vous souhaitez intégrer notre écosystème.
        </p>

        @if (session('pending_user'))
            <div class="inline-flex items-center gap-2 px-4 py-2 mt-8 rounded-full bg-white/5 border border-white/10 text-slate-300 text-xs font-bold uppercase tracking-widest animate-fade-up delay-1">
                <i data-lucide="user" class="w-4 h-4"></i>
                Session active : <span class="lowercase tracking-normal font-normal opacity-80 ml-1">{{ strtolower(session('pending_user')['email']) }}</span>
            </div>
        @endif
    </div>

    {{-- Main Logic Area --}}
    <div class="relative z-10 w-full max-w-lg flex-1 flex items-center justify-center py-12">
        {{-- Choice View --}}
        <div id="initial-choice" class="w-full space-y-4 animate-fade-up delay-2">
            {{-- Buttons will be here controlled by logic --}}
        </div>

    {{-- Forms Area (Hidden, used via Swal/Logic) --}}
    <div class="hidden">
        <form id="join-form-main" action="{{ route('entreprise.setup.post') }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="join">
            <input type="hidden" name="company_code" id="swal-company-code">
        </form>

        <form id="create-form-main" action="{{ route('entreprise.setup.post') }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="company_name" id="swal-company-name">
        </form>
    </div>

        {{-- Errors --}}
        @if (session('error'))
            <div id="error-msg" class="fixed top-8 left-1/2 -translate-x-1/2 p-4 rounded-xl bg-red-500/20 border border-red-500/40 text-red-200 text-sm flex items-center gap-3 animate-fade-up max-w-md w-full">
                <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                <span>{{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" class="p-1 hover:bg-white/10 rounded-lg transition-colors ml-auto">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
            </div>
        @endif
    </div>

    {{-- Bottom Section: Actions --}}
    <div id="bottom-actions" class="relative z-10 w-full max-w-2xl pb-16 animate-fade-up delay-3">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <button onclick="showStep('join')" class="group flex items-center justify-between p-5 glass-card rounded-2xl hover:border-primary/50 hover:bg-white/[0.05] transition-all duration-500 hover:-translate-y-1">
                <div class="flex items-center gap-4 text-left">
                    <div class="w-12 h-12 bg-primary/10 text-primary flex items-center justify-center rounded-xl group-hover:bg-primary group-hover:text-white transition-all duration-500 shadow-xl">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-sm font-black text-white group-hover:text-primary transition-colors uppercase tracking-widest">Rejoindre</p>
                        <p class="text-[10px] text-slate-500 leading-none">J'ai un code d'accès</p>
                    </div>
                </div>
                <i data-lucide="arrow-right" class="w-4 h-4 text-slate-700 group-hover:text-primary transition-colors"></i>
            </button>

            <button onclick="showStep('create')" class="group flex items-center justify-between p-5 glass-card rounded-2xl hover:border-emerald-500/50 hover:bg-white/[0.05] transition-all duration-500 hover:-translate-y-1">
                <div class="flex items-center gap-4 text-left">
                    <div class="w-12 h-12 bg-emerald-500/10 text-emerald-400 flex items-center justify-center rounded-xl group-hover:bg-emerald-600 group-hover:text-white transition-all duration-500 shadow-xl">
                        <i data-lucide="plus-circle" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-sm font-black text-white group-hover:text-emerald-400 transition-colors uppercase tracking-widest">Démarrer</p>
                        <p class="text-[10px] text-slate-500 leading-none">Gestion de mon entreprise</p>
                    </div>
                </div>
                <i data-lucide="arrow-right" class="w-4 h-4 text-slate-700 group-hover:text-emerald-500 transition-colors"></i>
            </button>
        </div>

        <div class="mt-12 text-center opacity-60 hover:opacity-100 transition-opacity">
            <form action="{{ route('entreprise.setup.post') }}" method="POST" id="skip-form">
                @csrf
                <input type="hidden" name="action" value="skip">
                <button type="button" 
                    onclick="Swal.fire({
                        title: 'Plus tard ?',
                        text: 'Vous ne pourrez pas accéder aux fonctionnalités comptables tant que vous n\'avez pas configuré votre entreprise.',
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonColor: '#005b82',
                        cancelButtonColor: '#1e293b',
                        confirmButtonText: 'Passer pour le moment',
                        cancelButtonText: 'Rester ici',
                        background: '#0f172a',
                        color: '#ffffff'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('skip-form').submit();
                        }
                    })"
                    class="text-xs font-black uppercase tracking-[0.3em] flex items-center justify-center gap-3 mx-auto text-slate-400 hover:text-white transition-colors">
                    Passer cette étape <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function showStep(type) {
            if (type === 'join') {
                Swal.fire({
                    title: 'Rejoindre une équipe',
                    text: "Entrez votre code d'accès pour rejoindre vos collaborateurs.",
                    input: 'text',
                    inputPlaceholder: 'EX: COMPTA-X1Y2',
                    icon: 'question',
                    background: '#0f172a',
                    color: '#ffffff',
                    showCancelButton: true,
                    confirmButtonText: 'Rejoindre',
                    cancelButtonText: 'Annuler',
                    confirmButtonColor: '#005b82',
                    cancelButtonColor: '#1e293b',
                    didOpen: () => {
                        lucide.createIcons();
                        const input = Swal.getInput();
                        input.classList.add('lowercase-force-not'); // I'll just handle it in js
                        input.style.textAlign = 'center';
                        input.style.textTransform = 'uppercase';
                        input.style.letterSpacing = '0.2em';
                        input.addEventListener('input', (e) => (e.target.value = e.target.value.toUpperCase()));
                    },
                    preConfirm: (val) => {
                        if (!val) {
                            Swal.showValidationMessage('Veuillez entrer un code');
                            return false;
                        }
                        return val;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('swal-company-code').value = result.value;
                        document.getElementById('join-form-main').submit();
                    }
                });
            } else if (type === 'create') {
                Swal.fire({
                    title: 'Démarrer la gestion',
                    text: 'Quel est le nom de l\'entreprise que vous souhaitez gérer ?',
                    input: 'text',
                    inputPlaceholder: 'Ex: Ma Société SAS',
                    icon: 'success',
                    background: '#0f172a',
                    color: '#ffffff',
                    showCancelButton: true,
                    confirmButtonText: 'Créer maintenant',
                    cancelButtonText: 'Annuler',
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#1e293b',
                    didOpen: () => {
                        lucide.createIcons();
                    },
                    preConfirm: (val) => {
                        if (!val) {
                            Swal.showValidationMessage('Veuillez donner un nom');
                            return false;
                        }
                        return val;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('swal-company-name').value = result.value;
                        document.getElementById('create-form-main').submit();
                    }
                });
            }
        }

        // Auto-show based on URL
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const action = urlParams.get('action');
            if (action === 'create' || action === 'join') {
                showStep(action);
            }
        });
    </script>
</body>

</html>
