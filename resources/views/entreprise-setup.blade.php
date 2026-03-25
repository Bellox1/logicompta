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
            <!-- Logo - Adapté au mode clair (Inversion simple) -->
            <div class="flex justify-center mb-12">
                <img src="{{ asset('storage/images/logo.png') }}" alt="Comptafriq"
                    class="h-32 w-auto object-contain dark:filter-none filter invert dark:brightness-110">
            </div>

            <!-- Card -->
            <div
                class="bg-white dark:bg-[#161615] py-8 px-4 border border-gray-200 dark:border-gray-800 sm:rounded-2xl sm:px-10 shadow-sm">

                <!-- Header -->
                <div class="text-center mb-8">
                    @if (session('pending_user'))
                        <p class="text-xs font-bold text-googleBlue uppercase mb-2">Compte pour :
                            {{ session('pending_user')['email'] }}</p>
                    @endif
                    <h1 class="text-2xl font-semibold tracking-tight">Espace de travail</h1>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Dernière étape ! Choisissez comment vous
                        souhaitez démarrer.</p>
                </div>

                <!-- Simple Action Selection -->
                <div class="space-y-4" id="initial-choice">
                    <button onclick="showStep('join')"
                        class="w-full flex items-center justify-between p-5 border border-gray-200 dark:border-gray-800 rounded-2xl hover:bg-gray-50 dark:hover:bg-black transition-colors group">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center rounded-xl">
                                <i data-lucide="users" class="w-6 h-6"></i>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-sm">Rejoindre une entreprise</p>
                                <p class="text-xs text-gray-500">J'ai un code d'invitation</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right"
                            class="w-4 h-4 text-gray-300 group-hover:text-gray-600 transition-colors"></i>
                    </button>

                    <button onclick="showStep('create')"
                        class="w-full flex items-center justify-between p-5 border border-gray-200 dark:border-gray-800 rounded-2xl hover:bg-gray-50 dark:hover:bg-black transition-colors group">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 bg-green-50 dark:bg-green-900/20 text-green-600 flex items-center justify-center rounded-xl">
                                <i data-lucide="plus" class="w-6 h-6"></i>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-sm">Créer une entreprise</p>
                                <p class="text-xs text-gray-500">Administrer ma propre structure</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right"
                            class="w-4 h-4 text-gray-300 group-hover:text-gray-600 transition-colors"></i>
                    </button>

                    <form action="{{ route('entreprise.setup.post') }}" method="POST" class="pt-6 text-center">
                        @csrf
                        <input type="hidden" name="action" value="skip">
                        <button type="submit"
                            class="text-xs font-bold text-gray-400 hover:text-gray-600 transition-colors flex items-center justify-center gap-2 mx-auto">
                            Rejoindre plus tard <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </button>
                    </form>
                </div>

                <!-- Step: Join -->
                <div id="step-join" class="hidden animate-in fade-in slide-in-from-right-4 duration-300">
                    <button onclick="resetChoice()"
                        class="flex items-center gap-2 text-xs font-semibold text-gray-500 hover:text-gray-800 mb-6 transition-colors">
                        <i data-lucide="arrow-left" class="w-3 h-3"></i> Retour
                    </button>

                    <h2 class="text-lg font-bold mb-4">Code Entreprise</h2>
                    <form action="{{ route('entreprise.setup.post') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="action" value="join">
                        <input type="text" name="company_code" placeholder="EX: LOG-XXXX"
                            oninput="this.value = this.value.toUpperCase()" required
                            class="block w-full px-4 py-4 border border-gray-200 dark:border-gray-800 rounded-xl text-sm focus:ring-2 focus:ring-googleBlue bg-gray-50 dark:bg-black outline-none transition-all">

                        <button type="submit"
                            class="w-full py-4 bg-googleBlue text-white font-bold rounded-xl hover:bg-blue-700 transition-all shadow-sm">
                            Valider et Finaliser l'inscription
                        </button>
                    </form>
                </div>

                <!-- Step: Create -->
                <div id="step-create" class="hidden animate-in fade-in slide-in-from-right-4 duration-300">
                    <button onclick="resetChoice()"
                        class="flex items-center gap-2 text-xs font-semibold text-gray-500 hover:text-gray-800 mb-6 transition-colors">
                        <i data-lucide="arrow-left" class="w-3 h-3"></i> Retour
                    </button>

                    <h2 class="text-lg font-bold mb-4">Nom de l'entreprise</h2>
                    <form action="{{ route('entreprise.setup.post') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="action" value="create">
                        <input type="text" name="company_name" placeholder="Ex: Logicompta SARL" required
                            class="block w-full px-4 py-4 border border-gray-200 dark:border-gray-800 rounded-xl text-sm focus:ring-2 focus:ring-googleBlue bg-gray-50 dark:bg-black outline-none transition-all">

                        <button type="submit"
                            class="w-full py-4 bg-googleBlue text-white font-bold rounded-xl hover:bg-blue-700 transition-all shadow-sm">
                            Créer et Finaliser
                        </button>
                    </form>
                </div>

                @if (session('error'))
                    <div class="mt-4 p-3 bg-red-50 text-red-600 text-xs rounded-lg border border-red-100">
                        {{ session('error') }}
                    </div>
                @endif

            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function showStep(step) {
            document.getElementById('initial-choice').classList.add('hidden');
            document.getElementById('step-' + step).classList.remove('hidden');
        }

        function resetChoice() {
            document.getElementById('step-join').classList.add('hidden');
            document.getElementById('step-create').classList.add('hidden');
            document.getElementById('initial-choice').classList.remove('hidden');
        }
    </script>
</body>

</html>
