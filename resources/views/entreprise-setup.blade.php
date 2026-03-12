<!DOCTYPE html>
<html lang="fr" class="h-full bg-white dark:bg-[#0a0a0a]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration Logicompta</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'media',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        primary: '#003366',
                        googleBlue: '#1a73e8',
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full font-sans antialiased text-gray-900 dark:text-gray-100">

    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        
        <!-- Center Card Container -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Logo - Augmenté -->
            <div class="flex justify-center mb-8">
                <img src="{{ asset('images/ChatGPT Image 11 mars 2026, 10_41_49.png') }}" alt="Logicompta" class="h-28 w-auto">
            </div>

            <!-- Card -->
            <div class="bg-white dark:bg-[#161615] py-8 px-4 border border-gray-200 dark:border-gray-800 sm:rounded-2xl sm:px-10 shadow-sm">
                
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-semibold tracking-tight">Espace de travail</h1>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Dernière étape ! Choisissez comment vous souhaitez démarrer.</p>
                </div>

                <!-- Simple Action Selection -->
                <div class="space-y-4" id="initial-choice">
                    <button onclick="showStep('join')" class="w-full flex items-center justify-between p-5 border border-gray-200 dark:border-gray-800 rounded-2xl hover:bg-gray-50 dark:hover:bg-black transition-colors group">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center rounded-xl">
                                <i data-lucide="users" class="w-6 h-6"></i>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-sm">Rejoindre une entreprise</p>
                                <p class="text-xs text-gray-500">J'ai un code d'invitation</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 group-hover:text-gray-600 transition-colors"></i>
                    </button>

                    <button onclick="showStep('create')" class="w-full flex items-center justify-between p-5 border border-gray-200 dark:border-gray-800 rounded-2xl hover:bg-gray-50 dark:hover:bg-black transition-colors group">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-green-50 dark:bg-green-900/20 text-green-600 flex items-center justify-center rounded-xl">
                                <i data-lucide="plus" class="w-6 h-6"></i>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-sm">Créer une entreprise</p>
                                <p class="text-xs text-gray-500">Administrer ma propre structure</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 group-hover:text-gray-600 transition-colors"></i>
                    </button>
                    
                    <div class="pt-6 text-center">
                        <button onclick="submitAction('skip')" id="skip-btn" class="text-xs font-bold text-gray-400 hover:text-gray-600 transition-colors flex items-center justify-center gap-2 mx-auto">
                            Rejoindre plus tard <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </button>
                    </div>
                </div>

                <!-- Step: Join -->
                <div id="step-join" class="hidden animate-in fade-in slide-in-from-right-4 duration-300">
                    <button onclick="resetChoice()" class="flex items-center gap-2 text-xs font-semibold text-gray-500 hover:text-gray-800 mb-6 transition-colors">
                        <i data-lucide="arrow-left" class="w-3 h-3"></i> Retour
                    </button>
                    
                    <h2 class="text-lg font-bold mb-4">Code Entreprise</h2>
                    <div class="space-y-4">
                        <input type="text" id="join-code" placeholder="EX: LOG-XXXX" oninput="this.value = this.value.toUpperCase()"
                               class="block w-full px-4 py-4 border border-gray-200 dark:border-gray-800 rounded-xl text-sm focus:ring-2 focus:ring-googleBlue bg-gray-50 dark:bg-black outline-none transition-all">
                        
                        <div id="join-alert" class="hidden p-3 bg-red-50 text-red-600 text-xs rounded-lg border border-red-100"></div>
                        <button onclick="submitAction('join')" id="join-submit" class="w-full py-4 bg-googleBlue text-white font-bold rounded-xl hover:bg-blue-700 transition-all shadow-sm">
                            Valider et Finaliser l'inscription
                        </button>
                    </div>
                </div>

                <!-- Step: Create -->
                <div id="step-create" class="hidden animate-in fade-in slide-in-from-right-4 duration-300">
                    <button onclick="resetChoice()" class="flex items-center gap-2 text-xs font-semibold text-gray-500 hover:text-gray-800 mb-6 transition-colors">
                        <i data-lucide="arrow-left" class="w-3 h-3"></i> Retour
                    </button>
                    
                    <h2 class="text-lg font-bold mb-4">Nom de l'entreprise</h2>
                    <div class="space-y-4">
                        <input type="text" id="create-name" placeholder="Ex: Logicompta SARL"
                               class="block w-full px-4 py-4 border border-gray-200 dark:border-gray-800 rounded-xl text-sm focus:ring-2 focus:ring-googleBlue bg-gray-50 dark:bg-black outline-none transition-all">
                        
                        <!-- Success View -->
                        <div id="create-success" class="hidden space-y-4 p-5 bg-green-50 dark:bg-green-900/10 rounded-xl border border-green-200">
                            <p class="text-[10px] font-black text-green-600 uppercase tracking-widest text-center">Entreprise créée !</p>
                            <div class="text-3xl font-black text-center tracking-widest" id="new-code">---</div>
                            <button onclick="copyToClipboard()" id="copy-btn" class="w-full py-2 bg-green-600 text-white rounded-lg text-[10px] font-bold">
                                Copier l'identifiant
                            </button>
                        </div>

                        <div id="create-alert" class="hidden p-3 bg-red-50 text-red-600 text-xs rounded-lg border border-red-100"></div>
                        
                        <button onclick="submitAction('create')" id="create-submit" class="w-full py-4 bg-googleBlue text-white font-bold rounded-xl hover:bg-blue-700 transition-all shadow-sm">
                            Créer et Finaliser
                        </button>
                        
                        <div id="goto-dash" class="hidden pt-4">
                             <a href="{{ url('/dashbord') }}" class="w-full py-4 bg-gray-900 text-white font-bold rounded-xl flex items-center justify-center gap-2">
                                Accéder au Tableau de Bord <i data-lucide="arrow-right" class="w-4 h-4"></i>
                             </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        const API_BASE = '{{ url('/api') }}';
        let spawnedCode = '';

        function showStep(step) {
            document.getElementById('initial-choice').classList.add('hidden');
            document.getElementById('step-' + step).classList.remove('hidden');
        }

        function resetChoice() {
            document.getElementById('step-join').classList.add('hidden');
            document.getElementById('step-create').classList.add('hidden');
            document.getElementById('initial-choice').classList.remove('hidden');
        }

        async function submitAction(action) {
            const pendingUser = JSON.parse(localStorage.getItem('logicompta_pending_user'));
            console.log('Pending user:', pendingUser);
            
            if (!pendingUser) {
                console.log('No pending user, redirecting to signup');
                window.location.href = '{{ url("/signup") }}';
                return;
            }

            const payload = {
                ...pendingUser,
                action: action,
                company_code: document.getElementById('join-code')?.value.trim() || '',
                company_name: document.getElementById('create-name')?.value.trim() || ''
            };
            
            console.log('Sending payload:', payload);

            const btnId = action === 'join' ? 'join-submit' : (action === 'create' ? 'create-submit' : 'skip-btn');
            const btn = document.getElementById(btnId);
            
            if (btn) btn.disabled = true;

            try {
                console.log('Making request to:', `${API_BASE}/register-and-setup`);
                const res = await fetch(`${API_BASE}/register-and-setup`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });
                
                console.log('Response status:', res.status);
                const data = await res.json();
                console.log('Response data:', data);

                if (data.success) {
                    // Sauvegarder les vraies infos finales
                    localStorage.setItem('comptafriq_token', data.token);
                    localStorage.setItem('comptafriq_user', JSON.stringify(data.user));
                    localStorage.removeItem('logicompta_pending_user');

                    if (action === 'create') {
                        spawnedCode = data.code;
                        document.getElementById('new-code').textContent = data.code;
                        document.getElementById('create-success').classList.remove('hidden');
                        document.getElementById('create-submit').classList.add('hidden');
                        document.getElementById('goto-dash').classList.remove('hidden');
                        lucide.createIcons();
                    } else {
                        // Pour skip et join, rediriger vers le dashboard
                        console.log('Redirecting to dashboard...');
                        setTimeout(() => {
                            window.location.href = '{{ url("/dashbord") }}';
                        }, 500);
                    }
                } else {
                    console.error('API Error:', data.message);
                    alert(data.message || 'Erreur lors de la configuration.');
                    if (btn) btn.disabled = false;
                }
            } catch (e) {
                console.error('Network error:', e);
                alert('Erreur de connexion au serveur. Détails: ' + e.message);
                if (btn) btn.disabled = false;
            }
        }

        function copyToClipboard() {
            navigator.clipboard.writeText(spawnedCode);
            const btn = document.getElementById('copy-btn');
            btn.textContent = 'Copié !';
            setTimeout(() => { btn.textContent = "Copier l'identifiant"; }, 2000);
        }

        window.onload = () => {
            // Pour les tests : si pas de pending user, on en crée un faux
            if (!localStorage.getItem('logicompta_pending_user')) {
                // Créer un faux utilisateur pour les tests
                const testUser = {
                    name: 'Test User',
                    email: 'test@example.com',
                    password: 'password123'
                };
                localStorage.setItem('logicompta_pending_user', JSON.stringify(testUser));
                console.log('Utilisateur de test créé dans localStorage');
            }
        };
    </script>
</body>
</html>
