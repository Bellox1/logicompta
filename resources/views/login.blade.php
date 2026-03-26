<!DOCTYPE html>
<html lang="fr" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, viewport-fit=cover">
    <title>Comptafriq - Connexion</title>
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

        /* Prevent overscroll bounce on mobile */
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

        input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 30px #0f172a inset !important;
            -webkit-text-fill-color: #fff !important;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-[#0a0f1e]">

    <div class="auth-container">
        {{-- ═══════════════════════ LEFT PANEL ═══════════════════════ --}}
        <div class="hidden lg:flex w-1/2 relative flex-col items-center justify-center p-12 overflow-hidden"
            style="background: linear-gradient(135deg, #001a3a 0%, #003366 50%, #004d99 100%);">

            <div class="relative z-10 text-center max-w-md">
                <div class="mb-10">
                    <img src="{{ asset('storage/images/logo.png') }}" alt="Logo" class="w-56 mx-auto">
                </div>
                <h1 class="text-4xl font-black text-white tracking-tight mb-4">Comptafriq</h1>
                <p class="text-blue-200 text-lg leading-relaxed mb-8">
                    Votre plateforme de gestion comptable moderne et intuitive.
                </p>
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 text-center">
                        <i data-lucide="book-open" class="w-7 h-7 text-blue-200 mx-auto mb-1"></i>
                        <div class="text-[10px] text-blue-200 font-bold uppercase tracking-widest">Écritures</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 text-center">
                        <i data-lucide="bar-chart-2" class="w-7 h-7 text-blue-200 mx-auto mb-1"></i>
                        <div class="text-[10px] text-blue-200 font-bold uppercase tracking-widest">Bilan</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 text-center">
                        <i data-lucide="zap" class="w-7 h-7 text-blue-200 mx-auto mb-1"></i>
                        <div class="text-[10px] text-blue-200 font-bold uppercase tracking-widest">Temps Réel</div>
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
                    <h2 class="text-4xl font-black text-gray-900 dark:text-white tracking-tight mb-2">Bienvenue</h2>
                    <p class="text-gray-500 dark:text-slate-400 font-medium">Connectez-vous à votre espace</p>
                </div>

                {{-- Alerts --}}
                @if (session('error'))
                    <div
                        class="mb-8 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-700 dark:text-red-400 flex items-center gap-3 animate-fade-up">
                        <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                @endif

                @if (session('success'))
                    <div
                        class="mb-8 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-700 dark:text-green-400 flex items-center gap-3 animate-fade-up">
                        <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                @endif

                {{-- Form --}}
                <form action="{{ route('login.post') }}" method="POST" class="space-y-6 animate-fade-up"
                    style="animation-delay: 0.2s;">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-slate-300 mb-2">Email</label>
                        <div class="relative group">
                            <span
                                class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-500 group-focus-within:text-primary transition-colors">
                                <i data-lucide="mail" class="w-5 h-5"></i>
                            </span>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                placeholder="email@gmail.com"
                                class="w-full bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white pl-12 pr-4 py-4 rounded-2xl text-base focus:outline-none focus:border-primary transition-all shadow-sm">
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-bold text-gray-700 dark:text-slate-300">Mot de
                                passe</label>
                            <a href="{{ route('forgot-password') }}"
                                class="text-xs font-bold text-primary hover:text-primary-light transition-colors">Oublié
                                ?</a>
                        </div>
                        <div class="relative group">
                            <span
                                class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-500 group-focus-within:text-primary transition-colors">
                                <i data-lucide="lock" class="w-5 h-5"></i>
                            </span>
                            <input type="password" name="password" id="password" required placeholder="••••••••"
                                class="w-full bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white pl-12 pr-12 py-4 rounded-2xl text-sm focus:outline-none focus:border-primary transition-all shadow-sm">
                            <button type="button" onclick="togglePassword('password', 'eye-icon')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition-colors">
                                <i data-lucide="eye" id="eye-icon" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-4 bg-primary text-white font-black rounded-2xl hover:bg-primary-light transition-all shadow-lg shadow-blue-900/40 text-sm uppercase tracking-widest active:scale-[0.98]">
                        Se connecter
                    </button>
                </form>

                {{-- Footer --}}
                <div class="mt-10 text-center animate-fade-up" style="animation-delay: 0.3s;">
                    <p class="text-sm text-gray-500 dark:text-slate-400">
                        Nouveau sur Comptafriq ?
                        <a href="{{ route('signup') }}"
                            class="font-black text-primary hover:text-primary-light ml-1 underline transition-all">S'inscrire</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

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
