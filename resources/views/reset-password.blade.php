<!DOCTYPE html>
<html lang="fr" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, viewport-fit=cover">
    <title>Comptafriq - Nouveau mot de passe</title>
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

        html, body {
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
                <h1 class="text-4xl font-black text-white tracking-tight mb-4">Mise à jour</h1>
                <p class="text-blue-200 text-lg leading-relaxed mb-8">
                    Choisissez un nouveau mot de passe fort pour sécuriser votre compte.
                </p>
                <div class="space-y-4">
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl px-5 py-3">
                        <i data-lucide="shield-check" class="w-5 h-5 text-green-400"></i>
                        <span class="text-sm text-blue-100 font-bold">Sécurité renforcée</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════ RIGHT PANEL ═══════════════════════ --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 md:p-12 lg:overflow-y-auto">
            <div class="w-full max-w-sm py-10">

                {{-- Mobile Logo --}}
                <div class="lg:hidden text-center mb-10 animate-fade-up">
                    <img src="{{ asset('storage/images/logo.png') }}" alt="Logo" class="w-32 mx-auto filter brightness-100 dark:brightness-110">
                </div>

                {{-- Header --}}
                <div class="mb-10 animate-fade-up" style="animation-delay: 0.1s;">
                    <h2 class="text-4xl font-black text-gray-900 dark:text-white tracking-tight mb-2">Nouveau MDP</h2>
                    <p class="text-gray-500 dark:text-slate-400 font-medium">Réinitialisez votre compte</p>
                </div>

                {{-- Form --}}
                <form action="{{ route('reset-password.post') }}" method="POST" class="space-y-6 animate-fade-up" style="animation-delay: 0.2s;">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-slate-300 mb-2">Mot de passe</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-500 group-focus-within:text-primary transition-colors">
                                <i data-lucide="lock" class="w-5 h-5"></i>
                            </span>
                            <input type="password" name="password" required placeholder="Nouveau mot de passe"
                                class="w-full bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white pl-12 pr-4 py-4 rounded-2xl text-base focus:outline-none focus:border-primary transition-all shadow-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-slate-300 mb-2">Confirmation</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-500 group-focus-within:text-primary transition-colors">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                            </span>
                            <input type="password" name="password_confirmation" required placeholder="Confirmez le mot de passe"
                                class="w-full bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white pl-12 pr-4 py-4 rounded-2xl text-base focus:outline-none focus:border-primary transition-all shadow-sm">
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-4 bg-primary text-white font-black rounded-2xl hover:bg-primary-light transition-all shadow-lg shadow-blue-900/40 text-sm uppercase tracking-widest active:scale-[0.98]">
                        Mettre à jour
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>
