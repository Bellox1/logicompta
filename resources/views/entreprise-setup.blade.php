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
    <script>
        tailwind.config = {
            darkMode: 'media',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    },
                    colors: {
                        primary: '#003366',
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

<body class="bg-[#0a0f1e] text-white min-h-screen flex flex-col items-center justify-between p-8 overflow-x-hidden">

    {{-- Background Decoration --}}
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-[20%] -left-[10%] w-[60%] h-[60%] rounded-full bg-blue-600/10 blur-[120px]"></div>
        <div class="absolute -bottom-[20%] -right-[10%] w-[50%] h-[50%] rounded-full bg-indigo-600/10 blur-[100px]"></div>
    </div>

    {{-- Top Section: Logo & Welcome --}}
    <div class="relative z-10 w-full max-w-2xl mt-12 text-center animate-fade-up">
        <div class="animate-float mb-12">
            <img src="{{ asset('storage/images/logo.png') }}" alt="Comptafriq" class="w-48 mx-auto drop-shadow-[0_0_30px_rgba(0,51,102,0.4)]">
        </div>
        <h1 class="text-5xl font-black tracking-tight mb-4">Initialisation de l'espace</h1>
        <p class="text-xl text-blue-200/70 max-w-lg mx-auto leading-relaxed">
            Dernière étape ! Pour finaliser votre configuration, choisissez comment vous souhaitez intégrer notre écosystème.
        </p>

        @if (session('pending_user'))
            <div class="inline-flex items-center gap-2 px-4 py-2 mt-8 rounded-full bg-white/5 border border-white/10 text-blue-300 text-xs font-bold uppercase tracking-widest animate-fade-up delay-1">
                <i data-lucide="user" class="w-4 h-4"></i>
                Session active : {{ session('pending_user')['email'] }}
            </div>
        @endif
    </div>

    {{-- Main Logic Area --}}
    <div class="relative z-10 w-full max-w-lg flex-1 flex items-center justify-center py-12">
        {{-- Choice View --}}
        <div id="initial-choice" class="w-full space-y-4 animate-fade-up delay-2">
            {{-- Buttons will be here controlled by logic --}}
        </div>

        {{-- Step: Join --}}
        <div id="step-join" class="hidden w-full glass-card p-10 rounded-[2.5rem] animate-fade-up">
            <button onclick="resetChoice()" class="flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-white mb-8 transition-colors group">
                <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i> Retour
            </button>
            <h3 class="text-2xl font-black mb-6 flex items-center gap-3">
                <i data-lucide="hash" class="w-6 h-6 text-blue-400"></i> Code d'invitation
            </h3>
            <form action="{{ route('entreprise.setup.post') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="action" value="join">
                <input type="text" name="company_code" placeholder="EX: COMPTA-X1Y2" 
                    oninput="this.value = this.value.toUpperCase()" required
                    class="w-full bg-white/5 border border-white/10 text-white pl-6 pr-6 py-5 rounded-2xl text-lg focus:outline-none focus:border-blue-500 transition-all placeholder-slate-600 tracking-widest font-mono text-center">
                <button type="submit" class="w-full py-5 font-black text-white rounded-2xl transition-all duration-300 hover:scale-[1.02] hover:shadow-[0_0_30px_rgba(59,130,246,0.3)] uppercase tracking-[0.2em] text-sm"
                    style="background: linear-gradient(135deg, #003366, #0066cc);">
                    Rejoindre l'équipe
                </button>
            </form>
        </div>

        {{-- Step: Create --}}
        <div id="step-create" class="hidden w-full glass-card p-10 rounded-[2.5rem] animate-fade-up">
            <button onclick="resetChoice()" class="flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-white mb-8 transition-colors group">
                <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i> Retour
            </button>
            <h3 class="text-2xl font-black mb-6 flex items-center gap-3">
                <i data-lucide="briefcase" class="w-6 h-6 text-emerald-400"></i> Nouvelle Entreprise
            </h3>
            <form action="{{ route('entreprise.setup.post') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="action" value="create">
                <input type="text" name="company_name" placeholder="Le nom de votre société..." required
                    class="w-full bg-white/5 border border-white/10 text-white px-6 py-5 rounded-2xl text-lg focus:outline-none focus:border-emerald-500 transition-all placeholder-slate-600">
                <button type="submit" class="w-full py-5 font-black text-white rounded-2xl transition-all duration-300 hover:scale-[1.02] hover:shadow-[0_0_30px_rgba(16,185,129,0.3)] uppercase tracking-[0.2em] text-sm"
                    style="background: linear-gradient(135deg, #065f46, #10b981);">
                    Créer mon espace
                </button>
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
            <button onclick="showStep('join')" class="group flex items-center justify-between p-5 glass-card rounded-2xl hover:border-blue-500/50 hover:bg-white/[0.05] transition-all duration-500 hover:-translate-y-1">
                <div class="flex items-center gap-4 text-left">
                    <div class="w-12 h-12 bg-blue-500/10 text-blue-400 flex items-center justify-center rounded-xl group-hover:bg-blue-600 group-hover:text-white transition-all duration-500 shadow-xl">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-sm font-black text-white group-hover:text-blue-400 transition-colors uppercase tracking-widest">Rejoindre</p>
                        <p class="text-[10px] text-slate-500 leading-none">J'ai un code d'accès</p>
                    </div>
                </div>
                <i data-lucide="arrow-right" class="w-4 h-4 text-slate-700 group-hover:text-blue-500 transition-colors"></i>
            </button>

            <button onclick="showStep('create')" class="group flex items-center justify-between p-5 glass-card rounded-2xl hover:border-emerald-500/50 hover:bg-white/[0.05] transition-all duration-500 hover:-translate-y-1">
                <div class="flex items-center gap-4 text-left">
                    <div class="w-12 h-12 bg-emerald-500/10 text-emerald-400 flex items-center justify-center rounded-xl group-hover:bg-emerald-600 group-hover:text-white transition-all duration-500 shadow-xl">
                        <i data-lucide="plus-circle" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-sm font-black text-white group-hover:text-emerald-400 transition-colors uppercase tracking-widest">Créer</p>
                        <p class="text-[10px] text-slate-500 leading-none">Nouvelle entité</p>
                    </div>
                </div>
                <i data-lucide="arrow-right" class="w-4 h-4 text-slate-700 group-hover:text-emerald-500 transition-colors"></i>
            </button>
        </div>

        <div class="mt-12 text-center opacity-60 hover:opacity-100 transition-opacity">
            <form action="{{ route('entreprise.setup.post') }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="skip">
                <button type="submit" class="text-xs font-black uppercase tracking-[0.3em] flex items-center justify-center gap-3 mx-auto">
                    Passer cette étape <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function showStep(step) {
            document.getElementById('bottom-actions').classList.add('hidden');
            document.getElementById('initial-choice').classList.add('hidden');
            
            // Hide all steps first
            document.getElementById('step-join').classList.add('hidden');
            document.getElementById('step-create').classList.add('hidden');
            
            // Show target
            document.getElementById('step-' + step).classList.remove('hidden');
            lucide.createIcons();
        }

        function resetChoice() {
            document.getElementById('step-join').classList.add('hidden');
            document.getElementById('step-create').classList.add('hidden');
            document.getElementById('bottom-actions').classList.remove('hidden');
            lucide.createIcons();
        }

        // Check for URL parameter to auto-show a step
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
