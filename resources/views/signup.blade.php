<!DOCTYPE html>
<html lang="fr" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, viewport-fit=cover">
    <title>Comptafriq - Inscription</title>
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
            margin: 0;
            padding: 0;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-up {
            animation: fadeInUp 0.4s ease-out forwards;
        }

        /* Prevent horizontal overflow */
        html,
        body {
            overflow-x: hidden;
            height: auto;
            min-height: 100%;
        }

        .auth-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        @media (min-width: 1024px) {
            .auth-container {
                flex-direction: row;
                height: 100vh;
                overflow: hidden;
            }
        }

        /* Strength meter transition */
        .strength-bar {
            transition: width 0.3s ease, background-color 0.3s ease;
        }
    </style>
</head>

<body class="bg-slate-50 dark:bg-[#0a0f1e]">

    <div class="auth-container">
        {{-- ═══════════════════════ LEFT PANEL ═══════════════════════ --}}
        <div class="hidden lg:flex w-1/2 relative flex-col items-center justify-center p-12 overflow-hidden bg-primary">

            <div class="relative z-10 text-center max-w-md">
                <div class="mb-10">
                    <img src="{{ asset('storage/images/logo.png') }}" alt="Logo" class="w-56 mx-auto">
                </div>
                <h1 class="text-4xl font-black text-white tracking-tight mb-4">Rejoignez-nous</h1>
                <p class="text-slate-300 text-lg leading-relaxed mb-8">
                    Créez votre compte et gérez votre comptabilité en toute simplicité.
                </p>
                <div class="space-y-3">
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl px-5 py-3 text-left">
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-400"></i>
                        <span class="text-sm text-slate-300 font-bold">Journal & Saisie comptable</span>
                    </div>
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl px-5 py-3 text-left">
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-400"></i>
                        <span class="text-sm text-slate-300 font-bold">États de synthèse & Bilan</span>
                    </div>
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl px-5 py-3 text-left">
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-400"></i>
                        <span class="text-sm text-slate-300 font-bold">Équipe illimitée</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════ RIGHT PANEL ═══════════════════════ --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 md:p-12 lg:overflow-y-auto">
            <div class="w-full max-w-sm py-10">

                {{-- Mobile Logo --}}
                <div class="lg:hidden text-center mb-10 animate-fade-up">
                    <img src="{{ asset('storage/images/logo.png') }}" alt="Logo"
                        class="w-32 mx-auto filter brightness-100 dark:brightness-110">
                </div>

                {{-- Header --}}
                <div class="mb-10 animate-fade-up" style="animation-delay: 0.1s;">
                    <h2 class="text-4xl font-black text-slate-900 dark:text-white tracking-tight mb-2">Inscription</h2>
                    <p class="text-slate-500 dark:text-slate-400 font-medium">Commencez dès aujourd'hui</p>
                </div>

                {{-- Alerts --}}
                @if ($errors->any())
                    <div
                        class="mb-8 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-700 dark:text-red-400 animate-fade-up">
                        <div class="flex items-center gap-3 mb-2">
                            <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                            <span class="text-sm font-bold">Des erreurs sont survenues :</span>
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-1 ml-8">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form --}}
                <form action="{{ route('signup.post') }}" method="POST" class="space-y-6 animate-fade-up"
                    style="animation-delay: 0.2s;">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nom
                            Complet</label>
                        <div class="relative group">
                            <span
                                class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 group-focus-within:text-primary transition-colors">
                                <i data-lucide="user" class="w-5 h-5"></i>
                            </span>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                placeholder="Votre nom complet"
                                class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white pl-12 pr-4 py-4 rounded-2xl text-base focus:outline-none focus:border-primary transition-all shadow-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Email</label>
                        <div class="relative group">
                            <span
                                class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 group-focus-within:text-primary transition-colors">
                                <i data-lucide="mail" class="w-5 h-5"></i>
                            </span>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                placeholder="votre@email.com"
                                class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white pl-12 pr-4 py-4 rounded-2xl text-base focus:outline-none focus:border-primary transition-all shadow-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Mot de
                            passe</label>
                        <div class="relative group">
                            <span
                                class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 group-focus-within:text-primary transition-colors">
                                <i data-lucide="lock" class="w-5 h-5"></i>
                            </span>
                            <input type="password" name="password" id="password" required
                                placeholder="Minimum 8 caractères"
                                class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white pl-12 pr-12 py-4 rounded-2xl text-base focus:outline-none focus:border-primary transition-all shadow-sm">
                            <button type="button" onclick="togglePassword('password', 'eye-icon-1')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary transition-colors">
                                <i data-lucide="eye" id="eye-icon-1" class="w-5 h-5"></i>
                            </button>
                        </div>

                        {{-- Strength bar --}}
                        <div class="mt-4 px-1">
                            <div class="h-1.5 w-full bg-slate-100 dark:bg-white/10 rounded-full overflow-hidden">
                                <div id="strength-bar" class="strength-bar h-full w-0 bg-red-500"></div>
                            </div>
                            <div id="strength-text"
                                class="text-[10px] text-slate-500 mt-2 font-bold uppercase tracking-widest">Très faible
                            </div>
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Confirmation</label>
                        <div class="relative group">
                            <span
                                class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 group-focus-within:text-primary transition-colors">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                            </span>
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                placeholder="Confirmez le mot de passe"
                                class="w-full bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white pl-12 pr-12 py-4 rounded-2xl text-base focus:outline-none focus:border-primary transition-all shadow-sm">
                            <button type="button" onclick="togglePassword('password_confirmation', 'eye-icon-2')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary transition-colors">
                                <i data-lucide="eye" id="eye-icon-2" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>

                    <p class="text-xs text-center text-slate-400 dark:text-slate-500 leading-relaxed">
                        En cliquant sur S'inscrire, vous acceptez nos
                        <a href="#" class="text-primary font-bold hover:underline">Conditions d'Utilisation</a>
                    </p>

                    <button type="submit"
                        class="w-full py-4 bg-primary text-white font-black rounded-2xl hover:bg-primary-light transition-all shadow-lg shadow-primary/20 text-sm uppercase tracking-widest active:scale-[0.98]">
                        S'inscrire
                    </button>
                </form>

                {{-- Footer --}}
                <div class="mt-10 text-center animate-fade-up" style="animation-delay: 0.3s;">
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Déjà un compte ?
                        <a href="{{ route('login') }}"
                            class="font-black text-primary hover:text-primary-light ml-1 underline transition-all">Se
                            connecter</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // Password Strength helper
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');

        passwordInput.addEventListener('input', (e) => {
            const val = e.target.value;
            let score = 0;

            if (val.length >= 8) score += 25;
            if (/[A-Z]/.test(val)) score += 25;
            if (/[0-9]/.test(val)) score += 25;
            if (/[^A-Za-z0-9]/.test(val)) score += 25;

            strengthBar.style.width = score + '%';

            if (score <= 25) {
                strengthBar.className = 'strength-bar h-full bg-red-500';
                strengthText.innerText = 'Trés Faible';
                strengthText.className = 'text-[10px] text-red-500 mt-2 font-bold uppercase tracking-widest';
            } else if (score <= 50) {
                strengthBar.className = 'strength-bar h-full bg-orange-500';
                strengthText.innerText = 'Moyen';
                strengthText.className = 'text-[10px] text-orange-500 mt-2 font-bold uppercase tracking-widest';
            } else if (score <= 75) {
                strengthBar.className = 'strength-bar h-full bg-yellow-500';
                strengthText.innerText = 'Bon';
                strengthText.className = 'text-[10px] text-yellow-500 mt-2 font-bold uppercase tracking-widest';
            } else {
                strengthBar.className = 'strength-bar h-full bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.4)]';
                strengthText.innerText = 'Excellent';
                strengthText.className = 'text-[10px] text-green-500 mt-2 font-bold uppercase tracking-widest';
            }
        });

        // Toggle Password visibility
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }
    </script>
</body>

</html>
