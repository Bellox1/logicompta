<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comptafriq - Nouveau mot de passe</title>
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

        .delay-4 {
            animation-delay: 0.4s;
            opacity: 0;
        }

        .req-ok {
            color: #4ade80;
        }

        .req-fail {
            color: #64748b;
        }

        input:-webkit-autofill,
        input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0 30px #0a0f1e inset !important;
            -webkit-text-fill-color: #fff !important;
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
                <i data-lucide="shield-check" class="w-28 h-28 text-white/30 mx-auto"></i>
            </div>
            <h1 class="text-4xl font-black text-white tracking-tight mb-4">Sécurité</h1>
            <p class="text-blue-200 text-lg leading-relaxed mb-8">
                Choisissez un mot de passe fort pour protéger votre compte.
            </p>
            <div class="space-y-3 text-left">
                <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl px-5 py-3">
                    <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0"></i>
                    <span class="text-sm text-blue-100">Au moins 8 caractères</span>
                </div>
                <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl px-5 py-3">
                    <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0"></i>
                    <span class="text-sm text-blue-100">Au moins une lettre majuscule</span>
                </div>
                <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl px-5 py-3">
                    <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0"></i>
                    <span class="text-sm text-blue-100">Au moins un chiffre</span>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT PANEL --}}
    <div
        class="w-full lg:w-1/2 flex items-center justify-center p-6 lg:p-12 bg-gray-50 dark:bg-[#0a0f1e] overflow-y-auto">
        <div class="w-full max-w-md py-8">

            {{-- Mobile logo --}}
            <div class="lg:hidden text-center mb-8 animate-fade-up">
                <img src="{{ asset('storage/images/logo.png') }}" alt="Logo" class="w-32 mx-auto mb-4">
            </div>

            {{-- Back --}}
            <div class="mb-6 animate-fade-up">
                <a href="{{ url('/login') }}"
                    class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Retour à la connexion
                </a>
            </div>

            {{-- Header --}}
            <div class="mb-8 animate-fade-up delay-1">
                <p class="text-xs font-bold uppercase tracking-widest text-blue-600 dark:text-blue-400 mb-2">
                    Récupération</p>
                <h2 class="text-3xl font-black text-gray-900 dark:text-white">Nouveau mot de passe</h2>
                <p class="text-gray-500 dark:text-slate-400 mt-2 text-sm">Définissez votre nouveau mot de passe sécurisé
                </p>
            </div>

            {{-- Invalid token alert --}}
            <div id="invalid-token-box"
                class="hidden mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm flex items-center gap-3">
                <i data-lucide="alert-triangle" class="w-5 h-5 flex-shrink-0"></i>
                <span>Lien de réinitialisation invalide ou expiré. <a href="{{ url('/forgot-password') }}"
                        class="underline font-semibold">Demander un nouveau lien</a>.</span>
            </div>

            {{-- Success --}}
            <div id="success-box"
                class="hidden mb-6 p-5 rounded-xl bg-green-500/10 border border-green-500/30 text-center">
                <i data-lucide="check-circle" class="w-10 h-10 text-green-400 mx-auto mb-3"></i>
                <p class="text-green-400 font-semibold text-sm">Mot de passe réinitialisé !</p>
                <p class="text-slate-400 text-xs mt-1">Redirection vers la connexion...</p>
            </div>

            {{-- Error --}}
            <div id="error-box"
                class="hidden mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm flex items-center gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                <span id="error-text"></span>
            </div>

            {{-- Form --}}
            <form id="resetPasswordForm" class="space-y-5">
                <input type="hidden" id="token" name="token">
                <input type="hidden" id="emailHidden" name="email">

                {{-- New Password --}}
                <div class="animate-fade-up delay-2">
                    <label for="password"
                        class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-2">Nouveau mot de
                        passe</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-500">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </span>
                        <input type="password" id="password" name="password" placeholder="••••••••" required
                            oninput="checkRequirements()"
                            class="w-full bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white pl-11 pr-12 py-3.5 rounded-xl text-sm focus:outline-none focus:border-blue-500 transition-all placeholder-gray-400 dark:placeholder-slate-600">
                        <button type="button" onclick="togglePassword('password','eye1')"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300 transition-colors">
                            <i data-lucide="eye" id="eye1" class="w-4 h-4"></i>
                        </button>
                    </div>
                    {{-- Requirements --}}
                    <div class="mt-3 space-y-1.5 text-xs">
                        <div id="req-length" class="flex items-center gap-2 req-fail">
                            <i data-lucide="circle" class="w-3 h-3"></i> Au moins 8 caractères
                        </div>
                        <div id="req-upper" class="flex items-center gap-2 req-fail">
                            <i data-lucide="circle" class="w-3 h-3"></i> Au moins une majuscule
                        </div>
                        <div id="req-number" class="flex items-center gap-2 req-fail">
                            <i data-lucide="circle" class="w-3 h-3"></i> Au moins un chiffre
                        </div>
                    </div>
                </div>

                {{-- Confirm Password --}}
                <div class="animate-fade-up delay-3">
                    <label for="password_confirmation"
                        class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-2">Confirmer le mot de
                        passe</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-500">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </span>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            placeholder="••••••••" required
                            class="w-full bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white pl-11 pr-12 py-3.5 rounded-xl text-sm focus:outline-none focus:border-blue-500 transition-all placeholder-gray-400 dark:placeholder-slate-600">
                        <button type="button" onclick="togglePassword('password_confirmation','eye2')"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300 transition-colors">
                            <i data-lucide="eye" id="eye2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="animate-fade-up delay-4 pt-1">
                    <button type="submit" id="submit-btn"
                        class="w-full py-4 font-bold text-white rounded-xl transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-900/40 text-sm uppercase tracking-widest flex items-center justify-center gap-2"
                        style="background: linear-gradient(135deg, #003366, #004d99);">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        Définir le mot de passe
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script>
        lucide.createIcons();

        const API_BASE = '{{ url('/api') }}';

        // Pré-remplir token & email depuis URL
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            const token = params.get('token');
            const email = params.get('email');
            if (!token || !email) {
                document.getElementById('invalid-token-box').classList.remove('hidden');
                document.getElementById('resetPasswordForm').classList.add('hidden');
                return;
            }
            document.getElementById('token').value = token;
            document.getElementById('emailHidden').value = email;
        });

        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            input.type = input.type === 'password' ? 'text' : 'password';
            icon.setAttribute('data-lucide', input.type === 'password' ? 'eye' : 'eye-off');
            lucide.createIcons();
        }

        function checkRequirements() {
            const val = document.getElementById('password').value;
            setReq('req-length', val.length >= 8);
            setReq('req-upper', /[A-Z]/.test(val));
            setReq('req-number', /[0-9]/.test(val));
        }

        function setReq(id, ok) {
            const el = document.getElementById(id);
            el.className = 'flex items-center gap-2 text-xs ' + (ok ? 'req-ok' : 'req-fail');
            el.querySelector('i').setAttribute('data-lucide', ok ? 'check-circle' : 'circle');
            lucide.createIcons();
        }

        document.getElementById('resetPasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const token = document.getElementById('token').value;
            const email = document.getElementById('emailHidden').value;
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('password_confirmation').value;
            const btn = document.getElementById('submit-btn');
            const errorBox = document.getElementById('error-box');

            errorBox.classList.add('hidden');

            if (password !== confirm) {
                document.getElementById('error-text').textContent = 'Les mots de passe ne correspondent pas.';
                errorBox.classList.remove('hidden');
                return;
            }

            if (password.length < 8 || !/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
                document.getElementById('error-text').textContent =
                    'Le mot de passe ne respecte pas les exigences.';
                errorBox.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btn.innerHTML =
                '<svg class="animate-spin w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/><path fill="currentColor" d="M4 12a8 8 0 018-8v8z" class="opacity-75"/></svg> Enregistrement...';

            try {
                const res = await fetch(`${API_BASE}/reset-password`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        token,
                        email,
                        password,
                        password_confirmation: confirm
                    })
                });
                const data = await res.json();

                if (data.success) {
                    document.getElementById('success-box').classList.remove('hidden');
                    document.getElementById('resetPasswordForm').classList.add('hidden');
                    setTimeout(() => window.location.href = '{{ url('/login') }}', 3000);
                } else {
                    document.getElementById('error-text').textContent = data.message ||
                        'Erreur lors de la réinitialisation.';
                    errorBox.classList.remove('hidden');
                }
            } catch {
                document.getElementById('error-text').textContent = 'Erreur de connexion au serveur.';
                errorBox.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="shield-check" class="w-4 h-4"></i> Définir le mot de passe';
                lucide.createIcons();
            }
        });
    </script>
</body>

</html>
