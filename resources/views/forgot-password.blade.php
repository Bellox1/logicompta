<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comptafriq - Mot de passe oublié</title>
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
                        'primary-light': '#004d99'
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
    </style>
</head>

<body class="bg-gray-50 dark:bg-[#0a0f1e] min-h-screen flex overflow-hidden">

    {{-- LEFT PANEL --}}
    <div class="hidden lg:flex lg:w-1/2 relative flex-col items-center justify-center p-12 overflow-hidden"
        style="background: linear-gradient(135deg, #001a3a 0%, #003366 50%, #004d99 100%);">
        <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full opacity-10"
            style="background: radial-gradient(circle, #fff, transparent);"></div>
        <div class="absolute -bottom-32 -right-32 w-[500px] h-[500px] rounded-full opacity-10"
            style="background: radial-gradient(circle, #fff, transparent);"></div>

        <div class="relative z-10 text-center max-w-md">
            <div class="animate-float mb-10">
                <img src="{{ asset('storage/images/logo.png') }}" alt="Comptafriq Logo"
                    class="w-56 mx-auto drop-shadow-2xl">
            </div>
            <h1 class="text-4xl font-black text-white tracking-tight mb-4">Récupération</h1>
            <p class="text-blue-200 text-lg leading-relaxed mb-8">
                Pas d'inquiétude ! Entrez votre email et nous vous enverrons un lien pour réinitialiser votre mot de
                passe.
            </p>
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-left">
                <div class="flex items-start gap-3">
                    <i data-lucide="mail" class="w-5 h-5 text-blue-300 mt-0.5 flex-shrink-0"></i>
                    <div>
                        <p class="text-white text-sm font-semibold">Vérifiez votre email</p>
                        <p class="text-blue-200 text-xs mt-1">Un lien sécurisé vous sera envoyé. Vérifiez aussi vos
                            spams.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT PANEL --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 lg:p-12 bg-gray-50 dark:bg-[#0a0f1e]">
        <div class="w-full max-w-md">

            {{-- Mobile logo --}}
            <div class="lg:hidden text-center mb-8 animate-fade-up">
                <img src="{{ asset('storage/images/logo.png') }}" alt="Logo" class="w-32 mx-auto mb-4">
            </div>

            {{-- Back button --}}
            <div class="mb-6 animate-fade-up">
                <a href="{{ url('/login') }}"
                    class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Retour à la connexion
                </a>
            </div>

            {{-- Header --}}
            <div class="mb-8 animate-fade-up delay-1">
                <p class="text-xs font-bold uppercase tracking-widest text-blue-600 dark:text-blue-400 mb-2">Sécurité
                </p>
                <h2 class="text-3xl font-black text-gray-900 dark:text-white">Mot de passe oublié ?</h2>
                <p class="text-gray-500 dark:text-slate-400 mt-2 text-sm">Entrez votre email pour recevoir un lien de
                    réinitialisation</p>
            </div>

            {{-- Success message --}}
            <div id="success-box"
                class="hidden mb-6 p-5 rounded-xl bg-green-500/10 border border-green-500/30 text-center">
                <i data-lucide="check-circle" class="w-10 h-10 text-green-400 mx-auto mb-3"></i>
                <p class="text-green-400 font-semibold text-sm">Email envoyé avec succès !</p>
                <p class="text-slate-400 text-xs mt-1">Vérifiez votre boîte de réception (et vos spams).</p>
            </div>

            {{-- Error message --}}
            <div id="error-box"
                class="hidden mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm flex items-center gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                <span id="error-text"></span>
            </div>

            {{-- Form --}}
            <form id="forgotPasswordForm" class="space-y-5">
                <div class="animate-fade-up delay-2">
                    <label for="email"
                        class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-2">Adresse email</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-500">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </span>
                        <input type="email" id="email" name="email" placeholder="votre@email.com" required
                            class="w-full bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white pl-11 pr-4 py-3.5 rounded-xl text-sm focus:outline-none focus:border-blue-500 transition-all placeholder-gray-400 dark:placeholder-slate-600">
                    </div>
                </div>

                <div class="animate-fade-up delay-3">
                    <button type="submit" id="submit-btn"
                        class="w-full py-4 font-bold text-white rounded-xl transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-900/40 text-sm uppercase tracking-widest flex items-center justify-center gap-2"
                        style="background: linear-gradient(135deg, #003366, #004d99);">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        Envoyer le lien
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script>
        lucide.createIcons();

        const API_BASE = '{{ url('/api') }}';

        document.getElementById('forgotPasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const btn = document.getElementById('submit-btn');
            const errorBox = document.getElementById('error-box');
            const successBox = document.getElementById('success-box');

            errorBox.classList.add('hidden');
            btn.disabled = true;
            btn.innerHTML =
                '<svg class="animate-spin w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/><path fill="currentColor" d="M4 12a8 8 0 018-8v8z" class="opacity-75"/></svg> Envoi en cours...';

            try {
                const res = await fetch(`${API_BASE}/forgot-password`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email
                    })
                });
                const data = await res.json();

                if (data.success) {
                    successBox.classList.remove('hidden');
                    document.getElementById('forgotPasswordForm').classList.add('hidden');
                } else {
                    document.getElementById('error-text').textContent = data.message ||
                        'Erreur lors de l\'envoi.';
                    errorBox.classList.remove('hidden');
                }
            } catch {
                document.getElementById('error-text').textContent = 'Erreur de connexion au serveur.';
                errorBox.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="send" class="w-4 h-4"></i> Envoyer le lien';
                lucide.createIcons();
            }
        });
    </script>
</body>

</html>
