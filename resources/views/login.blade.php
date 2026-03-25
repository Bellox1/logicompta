<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comptafriq - Connexion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;900&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            darkMode: 'media',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif']
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
            font-family: 'Outfit', sans-serif;
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
            animation: fadeInUp 0.5s ease forwards;
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        .delay-1 {
            animation-delay: 0.1s;
            opacity: 0;
        }

        .delay-2 {
            animation-delay: 0.2s;
            opacity: 0;
        }

        .delay-3 {
            animation-delay: 0.3s;
            opacity: 0;
        }

        .delay-4 {
            animation-delay: 0.4s;
            opacity: 0;
        }

        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0 30px #0f172a inset !important;
            -webkit-text-fill-color: #fff !important;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-[#0a0f1e] min-h-screen flex overflow-hidden">

    {{-- ═══════════════════════ LEFT PANEL ═══════════════════════ --}}
    <div class="hidden lg:flex lg:w-1/2 relative flex-col items-center justify-center p-12 overflow-hidden"
        style="background: linear-gradient(135deg, #001a3a 0%, #003366 50%, #004d99 100%);">

        {{-- Decorative circles --}}
        <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full opacity-10"
            style="background: radial-gradient(circle, #fff, transparent);"></div>
        <div class="absolute -bottom-32 -right-32 w-[500px] h-[500px] rounded-full opacity-10"
            style="background: radial-gradient(circle, #fff, transparent);"></div>
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[700px] rounded-full opacity-5 border border-white/20">
        </div>
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full opacity-5 border border-white/20">
        </div>

        {{-- Content --}}
        <div class="relative z-10 text-center max-w-md">
            <div class="animate-float mb-10">
                <img src="{{ asset('storage/images/logo.png') }}" alt="Comptafriq Logo"
                    class="w-56 mx-auto drop-shadow-2xl">
            </div>
            <h1 class="text-4xl font-black text-white tracking-tight mb-4">Comptafriq</h1>
            <p class="text-blue-200 text-lg leading-relaxed mb-8">
                Votre plateforme de gestion comptable moderne et intuitive.
            </p>
            <div class="grid grid-cols-3 gap-4 mt-8">
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 text-center flex flex-col items-center gap-2">
                    <i data-lucide="book-open" class="w-7 h-7 text-blue-200"></i>
                    <div class="text-xs text-blue-200 font-medium">Écritures</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 text-center flex flex-col items-center gap-2">
                    <i data-lucide="bar-chart-2" class="w-7 h-7 text-blue-200"></i>
                    <div class="text-xs text-blue-200 font-medium">Rapports</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 text-center flex flex-col items-center gap-2">
                    <i data-lucide="shield-check" class="w-7 h-7 text-blue-200"></i>
                    <div class="text-xs text-blue-200 font-medium">Sécurisé</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════ RIGHT PANEL ═══════════════════════ --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 lg:p-12 bg-gray-50 dark:bg-[#0a0f1e]">
        <div class="w-full max-w-md">

            {{-- Mobile logo --}}
            <div class="lg:hidden text-center mb-8 animate-fade-up">
                <img src="{{ asset('storage/images/logo.png') }}" alt="Logo" class="w-32 mx-auto mb-4">
            </div>

            {{-- Header --}}
            <div class="mb-8 animate-fade-up delay-1">
                <p class="text-xs font-bold uppercase tracking-widest text-blue-600 dark:text-blue-400 mb-2">Bienvenue
                </p>
                <h2 class="text-3xl font-black text-gray-900 dark:text-white">Connexion</h2>
                <p class="text-gray-500 dark:text-slate-400 mt-2 text-sm">Accédez à votre espace comptable</p>
            </div>

            {{-- Alerts --}}
            @if (session('error'))
                <div
                    class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm flex items-center gap-3 animate-fade-up delay-1">
                    <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                    {{ session('error') }}
                </div>
            @endif
            @if (session('success'))
                <div
                    class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 text-sm flex items-center gap-3 animate-fade-up delay-1">
                    <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Form --}}
            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div class="animate-fade-up delay-2">
                    <label for="email"
                        class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-2">Adresse email</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-500">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </span>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            placeholder="votre@email.com" required
                            class="w-full bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white pl-11 pr-4 py-3.5 rounded-xl text-sm focus:outline-none focus:border-blue-500 transition-all placeholder-gray-400 dark:placeholder-slate-600">
                    </div>
                </div>

                {{-- Password --}}
                <div class="animate-fade-up delay-3">
                    <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-2">Mot
                        de passe</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-500">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </span>
                        <input type="password" id="password" name="password" placeholder="••••••••" required
                            class="w-full bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white pl-11 pr-12 py-3.5 rounded-xl text-sm focus:outline-none focus:border-blue-500 transition-all placeholder-gray-400 dark:placeholder-slate-600">
                        <button type="button" onclick="togglePassword()"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300 transition-colors">
                            <i data-lucide="eye" id="eye-icon" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                {{-- Forgot password --}}
                <div class="text-right animate-fade-up delay-3">
                    <a href="{{ url('/forgot-password') }}"
                        class="text-xs text-blue-400 hover:text-blue-300 font-medium transition-colors">
                        Mot de passe oublié ?
                    </a>
                </div>

                {{-- Submit --}}
                <div class="animate-fade-up delay-4">
                    <button type="submit"
                        class="w-full py-4 font-bold text-white rounded-xl transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-900/40 text-sm uppercase tracking-widest"
                        style="background: linear-gradient(135deg, #003366, #004d99);">
                        Se connecter
                    </button>
                </div>
            </form>

            {{-- Divider --}}
            <div class="relative my-8 animate-fade-up delay-4">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200 dark:border-white/10"></div>
                </div>
                <div class="relative flex justify-center">
                    <span
                        class="bg-gray-50 dark:bg-[#0a0f1e] px-4 text-xs font-medium text-gray-400 dark:text-slate-500 uppercase tracking-widest">ou</span>
                </div>
            </div>

            {{-- Register link --}}
            <div class="text-center animate-fade-up delay-4">
                <p class="text-sm text-gray-500 dark:text-slate-500">
                    Pas encore de compte ?
                    <a href="{{ route('signup') }}"
                        class="font-bold text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300 transition-colors ml-1">
                        S'inscrire
                    </a>
                </p>
            </div>

        </div>
    </div>

    <script>
        lucide.createIcons();

        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
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
