<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#FFFFFF" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#161615" media="(prefers-color-scheme: dark)">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Comptabilité - @yield('title')</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Tom Select -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            darkMode: 'media',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: 'var(--primary)',
                        'primary-light': 'var(--primary-light)',
                        accent: '#f53003',
                        bg: 'var(--bg)',
                        'card-bg': 'var(--card-bg)',
                        border: 'var(--border-color)',
                    }
                }
            }
        }
    </script>

    <style>
        :root {
            --bg: #FFFFFF;
            --card-bg: #FFFFFF;
            --border-color: #e3e3e0;
            --text-main: #1f2937;
            /* gray-800 */
            --primary: #003366;
            --primary-light: #0055aa;
        }

        /* Table Responsive Wrapper - Scroll horizontal local au cadre blanc */
        .table-responsive {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: visible;
            position: relative;
            background: var(--card-bg);
            border-radius: 0;
            display: block;
            cursor: grab;
            user-select: none;
            scrollbar-width: thin;
        }

        .table-responsive:active {
            cursor: grabbing;
        }

        /* Global Input Styling */
        input,
        select,
        textarea {
            background-color: #ffffff;
            color: #111827;
            border: 1px solid #e5e7eb;
        }

        /* Specific fix for Date inputs to ensure they show up on mobile */
        @media (max-width: 768px) {
            input[type="date"]::before {
                color: #6b7280;
                content: attr(placeholder);
            }

            input[type="date"] {
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important;
                box-sizing: border-box !important;
            }
        }

        input[type="date"] {
            min-height: 2.5rem;
            color-scheme: light;
            /* Default light scheme */
        }

        @media (prefers-color-scheme: dark) {
            input[type="date"] {
                color-scheme: dark !important;
            }

            /* Aggressive fix for Webkit calendar icon in dark mode */
            input[type="date"]::-webkit-calendar-picker-indicator {
                filter: invert(1) brightness(1.5) !important;
                cursor: pointer;
            }
        }

        @media (prefers-color-scheme: dark) {
            :root {
                color-scheme: dark;
                --bg: #0a0a0a;
                --card-bg: #161615;
                --border-color: #262624;
                --text-main: #f3f4f6;
                /* gray-100 */
                --primary: #3b82f6;
                --primary-light: #60a5fa;
            }

            input,
            select,
            textarea {
                background-color: #1c1c1b !important;
                color: #ffffff !important;
                border-color: #404040 !important;
            }

            .table-responsive {
                border-color: rgba(255, 255, 255, 0.1) !important;
            }

            h1,
            h2,
            h3,
            h4,
            .text-gray-950,
            .text-gray-900,
            .text-gray-800,
            .text-black {
                color: var(--text-main) !important;
            }

            .text-gray-700,
            .text-gray-600 {
                color: #d1d5db !important;
                /* gray-300 */
            }

            /* Fix grey overlays on tables and rows */
            .bg-gray-50,
            .bg-gray-100,
            .bg-gray-200,
            [class*="bg-gray-50/"],
            [class*="bg-gray-100/"],
            [class*="bg-gray-200/"],
            [class*="bg-white/5"],
            [class*="bg-white/10"] {
                background-color: rgba(255, 255, 255, 0.03) !important;
            }

            .bg-white,
            .bg-card-bg {
                background-color: var(--card-bg) !important;
            }

            .bg-bg {
                background-color: var(--bg) !important;
            }

            /* Specific table headers/sections */
            [class*="bg-primary/5"],
            [class*="bg-primary/10"] {
                background-color: rgba(59, 130, 246, 0.1) !important;
                /* Lighter primary blue */
            }

            .border-gray-50,
            .border-gray-100,
            .border-gray-200,
            .border-gray-300,
            .border-gray-400 {
                border-color: rgba(255, 255, 255, 0.05) !important;
            }

            .hover\:bg-gray-50:hover,
            .hover\:bg-gray-50\/50:hover,
            tr:hover {
                background-color: rgba(255, 255, 255, 0.05) !important;
            }

            /* Form elements */
            select,
            input {
                background-color: #1c1c1b !important;
                color: #f3f4f6 !important;
                border-color: #262624 !important;
                color-scheme: dark;
            }

            input[type="date"]::-webkit-calendar-picker-indicator {
                filter: invert(1);
            }
        }

        html {
            background-color: var(--bg) !important;
            overflow-x: hidden;
            height: 100%;
            overscroll-behavior: none;
        }

        body {
            background-color: var(--bg) !important;
            color: var(--text-main);
            margin: 0;
            padding: 0;
            min-height: 100dvh;
            font-family: 'Outfit', sans-serif;
            overflow-x: hidden;
            position: relative;
            width: 100%;
            overscroll-behavior: none;
        }

        .sidebar-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* iOS Specific fixes */
        input,
        select,
        textarea {
            box-sizing: border-box;
            font-size: 16px;
            /* Global fix for iOS zoom */
        }

        input[type="date"] {
            -webkit-appearance: listbox;
            /* Restore native date icons */
        }

        /* Sticky Table Headers - JS Powered Version */
        .sticky-thead {
            border-collapse: separate !important;
            border-spacing: 0;
            width: 100%;
        }

        /* thead will be translated via JS */
        .sticky-thead thead {
            position: relative;
            z-index: 100 !important;
        }

        .sticky-thead thead th {
            background-color: var(--primary) !important;
            color: #ffffff !important;
            padding: 1.25rem 1.5rem !important;
            border: none !important;
            box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.1);
            text-transform: uppercase;
            font-weight: 800;
            font-size: 11px;
            white-space: nowrap;
        }

        /* Support Balance (2 niveaux de titres) */
        .sticky-thead thead tr.row-sticky-2 th {
            padding: 0.5rem 1.25rem !important;
            font-size: 10px;
        }

        /* Scroll Principal de la page - Vertical uniquement */
        .main-content {
            overflow-y: auto !important;
            overflow-x: hidden !important;
            position: relative;
        }

        @media (max-width: 768px) {

            html,
            body {
                overflow-x: hidden !important;
                width: 100%;
                position: relative;
            }

            .main-content {
                padding: 0 0.5rem 1rem 0.5rem !important;
                flex: 1;
                min-width: 0;
                width: 100%;
                display: block;
            }

            input,
            select,
            textarea {
                font-size: 16px !important;
            }
        }

        /* Sidebar Collapsed State (Initial) */
        .sidebar-collapsed {
            width: 80px !important;
        }

        .sidebar-collapsed .sidebar-label,
        .sidebar-collapsed .sidebar-header-text {
            display: none !important;
        }

        .rotate-180,
        .sidebar-collapsed #toggle-chevron {
            transform: rotate(180deg);
        }

        #toggle-chevron {
            transition: transform 0.3s ease;
            display: inline-block;
        }

        .sidebar-collapsed a i {
            width: 1.5rem !important;
            height: 1.5rem !important;
        }

        .sidebar-collapsed a {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            gap: 0 !important;
        }

        /* Désactivation de l'arrondi uniquement pour le contenu principal (tableaux, boutons etc) */
        .main-content button,
        .main-content .button,
        .main-content a.inline-flex,
        .main-content a.flex,
        .main-content input[type="submit"],
        .main-content input[type="button"],
        .main-content .rounded-xl,
        .main-content .rounded-2xl,
        .main-content .rounded-3xl,
        .main-content .rounded-none {
            border-radius: 0 !important;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                width: 260px;
                height: 100dvh;
                z-index: 2000;
                transform: translateX(-100%);
                padding-top: calc(1rem + env(safe-area-inset-top, 0));
                padding-bottom: calc(1rem + env(safe-area-inset-bottom, 0));
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            /* Orientation de la transition vers le transform */
            .sidebar-transition {
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            #sidebar-overlay {
                transition: opacity 0.3s ease, visibility 0.3s;
                visibility: hidden;
                opacity: 0;
                display: block !important;
                /* On gère par visibility/opacity pour la fluidité */
            }

            #sidebar-overlay.active {
                visibility: visible;
                opacity: 1;
            }

            /* Propagation de la couleur du header vers le haut (encoche) */
            header.md\:hidden {
                padding-top: env(safe-area-inset-top, 0) !important;
                height: calc(4rem + env(safe-area-inset-top, 0)) !important;
                display: flex !important;
                align-items: center !important;
            }
        }
    </style>
    <script>
        // Appliquer l'état du sidebar IMMÉDIATEMENT pour éviter le flash
        (function() {
            const collapsed = localStorage.getItem('sidebar-collapsed') === 'true';
            if (collapsed && window.innerWidth > 768) {
                document.documentElement.classList.add('sidebar-is-collapsed');
            }
        })();
    </script>
</head>

<body class="bg-bg min-h-screen">
    <!-- Overlay for mobile (Visibility managed by JS) -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-gray-900/40 dark:bg-black/80 z-[1999] md:hidden"></div>

    <div class="flex h-[100dvh] overflow-hidden relative">
        <!-- Sidebar -->
        <aside id="sidebar"
            class="sidebar sidebar-transition w-[260px] h-full bg-white dark:bg-[#161615] border-r border-border flex flex-shrink-0 flex-col py-3 px-4 shadow-sm z-[2000] overflow-y-auto">
            <script>
                // Appliquer la classe si nécessaire avant que l'élément soit affiché
                if (localStorage.getItem('sidebar-collapsed') === 'true' && window.innerWidth > 768) {
                    document.getElementById('sidebar').classList.add('sidebar-collapsed');
                    document.getElementById('sidebar').classList.replace('w-[260px]', 'w-[80px]');
                }
            </script>
            <!-- Toggle Button (Floating) -->
            <button id="toggle-sidebar"
                class="fixed left-[244px] top-10 sidebar-transition bg-accent text-white border-[3px] border-[#FDFDFC] dark:border-[#0a0a0a] w-8 h-8 rounded-full flex items-center justify-center shadow-lg hover:scale-110 hover:bg-primary transition-all z-[2001] hidden md:flex">
                <script>
                    if (localStorage.getItem('sidebar-collapsed') === 'true' && window.innerWidth > 768) {
                        document.currentScript.parentElement.style.left = '64px';
                    }
                </script>
                <i id="toggle-chevron" data-lucide="chevron-left"></i>
            </button>

            <!-- Mobile Close Button -->
            <button id="close-mobile-sidebar"
                class="md:hidden absolute top-4 right-4 text-text-muted dark:text-gray-300">
                <i data-lucide="x"></i>
            </button>

            <div class="flex items-center mb-8 px-2 transition-all duration-300">
                <img src="{{ asset('storage/images/logo.png') }}" alt="Comptafriq Logo"
                    class="h-24 w-auto object-contain dark:filter-none filter invert dark:brightness-110">
            </div>

            <nav class="flex-1 flex flex-col gap-1 overflow-y-auto">
                <a href="{{ route('accounting.dashboard') }}"
                    class="flex items-center gap-4 px-4 py-3 font-medium transition-all border-l-4 {{ request()->routeIs('accounting.dashboard') ? 'border-primary text-primary bg-primary/5 dark:bg-primary/10' : 'border-transparent text-gray-600 hover:text-primary hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5' }}">
                    <i class="w-5 h-5" data-lucide="home"></i>
                    <span class="sidebar-label transition-all duration-300">Accueil</span>
                </a>

                <div
                    class="sidebar-label text-[10px] uppercase font-bold text-gray-400 mt-6 px-4 mb-2 tracking-widest hidden md:block">
                    Comptabilité Générale</div>
                <div class="md:hidden border-t border-border my-4 mx-2"></div>

                <a href="{{ route('accounting.journal.index') }}"
                    class="flex items-center gap-4 px-4 py-3 font-medium transition-all border-l-4 {{ request()->routeIs('accounting.journal.index') ? 'border-primary text-primary bg-primary/5 dark:bg-primary/10' : 'border-transparent text-gray-600 hover:text-primary hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5' }}">
                    <i class="w-5 h-5" data-lucide="book-open"></i>
                    <span class="sidebar-label transition-all duration-300">Journal</span>
                </a>
                <a href="{{ route('accounting.journal.create') }}"
                    class="flex items-center gap-4 px-4 py-3 font-medium transition-all border-l-4 {{ request()->routeIs('accounting.journal.create') ? 'border-primary text-primary bg-primary/5 dark:bg-primary/10' : 'border-transparent text-gray-600 hover:text-primary hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5' }}">
                    <i class="w-5 h-5" data-lucide="edit"></i>
                    <span class="sidebar-label transition-all duration-300">Saisie</span>
                </a>
                <a href="{{ route('accounting.ledger') }}"
                    class="flex items-center gap-4 px-4 py-3 font-medium transition-all border-l-4 {{ request()->routeIs('accounting.ledger') ? 'border-primary text-primary bg-primary/5 dark:bg-primary/10' : 'border-transparent text-gray-600 hover:text-primary hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5' }}">
                    <i class="w-5 h-5" data-lucide="bar-chart-2"></i>
                    <span class="sidebar-label transition-all duration-300">Grand Livre</span>
                </a>
                <a href="{{ route('accounting.balance') }}"
                    class="flex items-center gap-4 px-4 py-3 font-medium transition-all border-l-4 {{ request()->routeIs('accounting.balance') ? 'border-primary text-primary bg-primary/5 dark:bg-primary/10' : 'border-transparent text-gray-600 hover:text-primary hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5' }}">
                    <i class="w-5 h-5" data-lucide="scale"></i>
                    <span class="sidebar-label transition-all duration-300">Balance</span>
                </a>
                <a href="{{ route('accounting.bilan') }}"
                    class="flex items-center gap-4 px-4 py-3 font-medium transition-all border-l-4 {{ request()->routeIs('accounting.bilan') ? 'border-primary text-primary bg-primary/5 dark:bg-primary/10' : 'border-transparent text-gray-600 hover:text-primary hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5' }}">
                    <i class="w-5 h-5" data-lucide="briefcase"></i>
                    <span class="sidebar-label transition-all duration-300">Bilan</span>
                </a>
                <a href="{{ route('accounting.resultat') }}"
                    class="flex items-center gap-4 px-4 py-3 font-medium transition-all border-l-4 {{ request()->routeIs('accounting.resultat') ? 'border-primary text-primary bg-primary/5 dark:bg-primary/10' : 'border-transparent text-gray-600 hover:text-primary hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5' }}">
                    <i class="w-5 h-5" data-lucide="trending-up"></i>
                    <span class="sidebar-label transition-all duration-300">Résultat</span>
                </a>
                <a href="{{ route('accounting.archive.index') }}"
                    class="flex items-center gap-4 px-4 py-3 font-medium transition-all border-l-4 {{ request()->routeIs('accounting.archive.*') ? 'border-primary text-primary bg-primary/5 dark:bg-primary/10' : 'border-transparent text-gray-600 hover:text-primary hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5' }}">
                    <i class="w-5 h-5" data-lucide="archive"></i>
                    <span class="sidebar-label transition-all duration-300">Archives</span>
                </a>
                
                <a href="{{ route('accounting.account.index') }}"
                    class="flex items-center gap-4 px-4 py-3 font-medium transition-all border-l-4 {{ request()->routeIs('accounting.account.*') ? 'border-primary text-primary bg-primary/5 dark:bg-primary/10' : 'border-transparent text-gray-600 hover:text-primary hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5' }}">
                    <i class="w-5 h-5" data-lucide="list"></i>
                    <span class="sidebar-label transition-all duration-300">Plan Comptable</span>
                </a>

                <div
                    class="sidebar-label text-[10px] uppercase font-bold text-gray-400 mt-6 px-4 mb-2 tracking-widest hidden md:block">
                    Support</div>
                <a href="{{ route('accounting.help') }}"
                    class="flex items-center gap-4 px-4 py-3 font-medium transition-all border-l-4 {{ request()->routeIs('accounting.help') ? 'border-primary text-primary bg-primary/5 dark:bg-primary/10' : 'border-transparent text-gray-600 hover:text-primary hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5' }}">
                    <i class="w-5 h-5" data-lucide="help-circle"></i>
                    <span class="sidebar-label transition-all duration-300">Guide & Aide</span>
                </a>
            </nav>

            <div class="mt-auto pt-4 border-t border-border no-print">
                <div id="system-date-container" class="px-4 py-3 text-sm font-medium flex items-center gap-4 sidebar-label no-print">
                    <i data-lucide="clock" class="w-5 h-5 text-gray-500 dark:text-gray-400"></i>
                    <div id="system-datetime-display" class="text-gray-600 dark:text-gray-400 transition-all duration-300 whitespace-nowrap">
                        --/--/---- --:--:--
                    </div>
                </div>
                
                <form action="{{ route('logout') }}" method="POST" id="logout-form" class="hidden">
                    @csrf
                </form>
                <button type="button"
                    onclick="Swal.fire({
                        title: 'Déconnexion',
                        text: 'Voulez-vous vraiment vous déconnecter ?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#003366',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Oui, déconnecter',
                        cancelButtonText: 'Annuler',
                        background: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#161615' : '#fff',
                        color: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#fff' : '#000'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('logout-form').submit();
                        }
                    })"
                    class="flex items-center gap-4 px-4 py-3 font-medium transition-all border-l-4 border-transparent text-red-500 hover:text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 w-full text-left">
                    <i class="w-5 h-5" data-lucide="log-out"></i>
                    <span class="sidebar-label transition-all duration-300">Déconnexion</span>
                </button>
            </div>
        </aside>



        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col h-full overflow-hidden">
            <!-- Mobile Top Bar (Opaque background to avoid bleed-through) -->
            <header
                class="md:hidden flex items-center justify-between px-4 bg-white dark:bg-[#161615] border-b border-border dark:border-white/10 shadow-sm flex-shrink-0 relative">
                <button id="open-mobile-sidebar"
                    class="p-2 bg-white dark:bg-white/5 rounded-lg border border-border dark:border-white/10 z-10">
                    <i data-lucide="menu" class="w-5 h-5 dark:text-gray-300"></i>
                </button>

                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <img src="{{ asset('storage/images/logo.png') }}" alt="Comptafriq Logo"
                        class="h-20 w-auto object-contain pointer-events-auto dark:filter-none filter invert dark:brightness-110">
                </div>

                <div class="w-10"></div>
            </header>

            <!-- Scrollable Content Area - Enable full auto overflow for sticky headers -->
            <main class="main-content flex-1 overflow-auto p-6 md:p-10 transition-all scroll-smooth relative">
                @if (session('success'))
                    <div
                        class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-700 dark:text-green-400 flex items-center gap-3 animate-fade-up">
                        <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="flex-1">{{ session('success') }}</span>
                        <button onclick="this.parentElement.remove()"
                            class="p-1 hover:bg-black/5 rounded-lg transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                @if (session('warnings'))
                    <div
                        class="mb-6 p-4 rounded-xl bg-yellow-500/10 border border-yellow-500/30 text-yellow-700 dark:text-yellow-400 flex flex-col gap-2 animate-fade-up">
                        <div class="flex items-center gap-3">
                            <i data-lucide="alert-triangle" class="w-5 h-5 flex-shrink-0"></i>
                            <span class="font-bold flex-1">Quelques lignes ont été ignorées :</span>
                            <button onclick="this.parentElement.parentElement.remove()"
                                class="p-1 hover:bg-black/5 rounded-lg transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-1 ml-8">
                            @foreach(session('warnings') as $warning)
                                <li>{{ $warning }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('error'))
                    <div
                        class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-700 dark:text-red-400 flex items-center gap-3 animate-fade-up">
                        <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="flex-1">{{ session('error') }}</span>
                        <button onclick="this.parentElement.remove()"
                            class="p-1 hover:bg-black/5 rounded-lg transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggle-sidebar');
        const toggleChevron = document.getElementById('toggle-chevron');
        const openMobileBtn = document.getElementById('open-mobile-sidebar');
        const closeMobileBtn = document.getElementById('close-mobile-sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const body = document.body;

        lucide.createIcons();

        // High Precision Real-time Clock (DB Synced)
        let serverTimeOffset = 0;
        
        const fetchSystemDate = async () => {
            try {
                const startTime = Date.now();
                const response = await fetch('{{ route("accounting.system-date") }}');
                const data = await response.json();
                
                // Calculer le décalage entre le client et le serveur
                const serverTime = new Date(data.datetime).getTime();
                const clientTime = Date.now();
                serverTimeOffset = serverTime - clientTime;
                
                updateClockDisplay();
            } catch (error) {
                console.error('Erreur synchronisation horloge:', error);
            }
        };

        const updateClockDisplay = () => {
            const display = document.getElementById('system-datetime-display');
            if (!display) return;
            
            const now = new Date(Date.now() + serverTimeOffset);
            
            const day = String(now.getDate()).padStart(2, '0');
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const year = now.getFullYear();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            
            display.innerText = `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
        };

        fetchSystemDate();
        setInterval(updateClockDisplay, 1000);
        // Resync avec le serveur toutes les 10 minutes
        setInterval(fetchSystemDate, 600000);

        // Function to apply collapsed state
        const setSidebarState = (collapsed) => {
            if (collapsed) {
                sidebar.classList.replace('w-[260px]', 'w-[80px]');
                sidebar.classList.add('sidebar-collapsed');
                if (toggleBtn) toggleBtn.style.left = '64px';
            } else {
                sidebar.classList.replace('w-[80px]', 'w-[260px]');
                sidebar.classList.remove('sidebar-collapsed');
                if (toggleBtn) toggleBtn.style.left = '244px';
            }
            localStorage.setItem('sidebar-collapsed', collapsed);
        };

        // Web Sidebar Toggle
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const currentlyCollapsed = sidebar.classList.contains('sidebar-collapsed');
                setSidebarState(!currentlyCollapsed);
            });
        }

        // Mobile Sidebar Toggle
        const toggleMobile = (forceState) => {
            const isOpen = forceState !== undefined ? forceState : !sidebar.classList.contains('mobile-open');

            if (isOpen) {
                sidebar.classList.add('mobile-open');
                sidebarOverlay.classList.add('active');
            } else {
                sidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('active');
            }
        };

        if (openMobileBtn) openMobileBtn.addEventListener('click', () => toggleMobile(true));
        if (closeMobileBtn) closeMobileBtn.addEventListener('click', () => toggleMobile(false));
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', () => toggleMobile(false));

        // --- Système de Swipe Fluide (Optimisé) ---
        let touchStartX = 0;
        let touchCurrentX = 0;
        let isSwiping = false;

        sidebar.addEventListener('touchstart', e => {
            if (window.innerWidth > 768 || !sidebar.classList.contains('mobile-open')) return;
            touchStartX = e.touches[0].clientX;
            isSwiping = true;
            sidebar.style.transition = 'none'; // Désactive transition CSS
            sidebarOverlay.style.transition = 'none';
        }, {
            passive: true
        });

        sidebar.addEventListener('touchmove', e => {
            if (!isSwiping) return;
            touchCurrentX = e.touches[0].clientX;
            let deltaX = touchStartX - touchCurrentX;

            if (deltaX > 0) { // On pousse vers la gauche
                sidebar.style.transform = `translateX(${-deltaX}px)`;
                let progress = Math.min(deltaX / 260, 1);
                sidebarOverlay.style.opacity = (0.8 - (progress * 0.8))
                    .toString(); // 0.8 est l'opacité max du black/80
            }
        }, {
            passive: true
        });

        sidebar.addEventListener('touchend', e => {
            if (!isSwiping) return;
            isSwiping = false;

            sidebar.style.transition = ''; // Restore CSS transitions
            sidebarOverlay.style.transition = '';
            sidebarOverlay.style.opacity = '';

            let deltaX = touchStartX - touchCurrentX;
            sidebar.style.transform = '';

            if (deltaX > 70) { // Seuil de fermeture
                toggleMobile(false);
            }
        }, {
            passive: true
        });

        // Load Initial State
        const savedState = localStorage.getItem('sidebar-collapsed');
        if (savedState === 'true' && window.innerWidth > 768) {
            setSidebarState(true);
        }

        // Déplacement du système de sticky headers ici pour une meilleure organisation
        const mainContent = document.querySelector('.main-content');

        const updateStickyHeaders = () => {
            const tables = document.querySelectorAll('.sticky-thead');

            tables.forEach(table => {
                const thead = table.querySelector('thead');
                const tableRect = table.getBoundingClientRect();
                const mainRect = mainContent.getBoundingClientRect();

                // Différence entre le haut du main et le haut du tableau
                const offset = mainRect.top - tableRect.top;

                if (offset > 0) {
                    // Limiter pour ne pas sortir du tableau par le bas
                    const stopPoint = tableRect.height - thead.offsetHeight - 5;
                    const translateVal = Math.min(offset, stopPoint);
                    thead.style.transform = `translateY(${translateVal}px)`;
                } else {
                    thead.style.transform = 'translateY(0px)';
                }
            });
        };

        if (mainContent) {
            mainContent.addEventListener('scroll', updateStickyHeaders);
            window.addEventListener('resize', updateStickyHeaders);
            updateStickyHeaders();
        }

        /* --- Système de Drag-to-Scroll pour les tableaux (H & V) --- */
        const initDragToScroll = () => {
            const wrappers = document.querySelectorAll('.table-responsive');
            const mainContent = document.querySelector('.main-content');

            wrappers.forEach(wrapper => {
                let isDown = false;
                let startX, startY;
                let scrollLeft, scrollTop;

                const startDragging = (e) => {
                    isDown = true;
                    wrapper.style.cursor = 'grabbing';
                    const pageX = e.pageX || e.touches[0].pageX;
                    const pageY = e.pageY || e.touches[0].pageY;

                    startX = pageX - wrapper.offsetLeft;
                    startY = pageY - wrapper.offsetTop;

                    scrollLeft = wrapper.scrollLeft;
                    scrollTop = mainContent ? mainContent.scrollTop : 0;
                };

                const stopDragging = () => {
                    isDown = false;
                    wrapper.style.cursor = 'grab';
                };

                const moveDragging = (e) => {
                    if (!isDown) return;
                    e.preventDefault();

                    const pageX = e.pageX || e.touches[0].pageX;
                    const pageY = e.pageY || e.touches[0].pageY;

                    const x = pageX - wrapper.offsetLeft;
                    const y = pageY - wrapper.offsetTop;

                    const walkX = (x - startX) * 2;
                    const walkY = (y - startY) * 2;

                    wrapper.scrollLeft = scrollLeft - walkX;
                    if (mainContent) {
                        mainContent.scrollTop = scrollTop - walkY;
                    }
                };

                // Mouse Events
                wrapper.addEventListener('mousedown', startDragging);
                window.addEventListener('mouseup', stopDragging);
                wrapper.addEventListener('mouseleave', stopDragging);
                wrapper.addEventListener('mousemove', moveDragging);

                // Touch Events
                wrapper.addEventListener('touchstart', startDragging, {
                    passive: true
                });
                wrapper.addEventListener('touchend', stopDragging);
                wrapper.addEventListener('touchmove', moveDragging, {
                    passive: false
                });
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initDragToScroll);
        } else {
            initDragToScroll();
        }

        /* --- Navigation par Touches de Direction (Clavier) --- */
        document.addEventListener('keydown', (e) => {
            // Ne pas scroller si on est dans un champ de saisie ou qu'on interagit avec le menu
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;
            if (e.target.closest('#sidebar')) return;

            const scrollStep = 100; // Vitesse du scroll clavier
            const mainContent = document.querySelector('.main-content');
            const tables = document.querySelectorAll('.table-responsive');

            // On ne gère manuellement que ce qui n'est pas "natif" ou mal géré
            // Pour haut/bas, on ne scrolle le contenu que si aucune autre zone scrolable (comme le menu) n'est survolée

            switch (e.key) {
                case 'ArrowUp':
                    if (mainContent) {
                        e.preventDefault();
                        mainContent.scrollTop -= scrollStep;
                    }
                    break;
                case 'ArrowDown':
                    if (mainContent) {
                        e.preventDefault();
                        mainContent.scrollTop += scrollStep;
                    }
                    break;
                case 'ArrowLeft':
                    if (tables.length > 0) {
                        e.preventDefault();
                        tables.forEach(table => table.scrollLeft -= scrollStep);
                    }
                    break;
                case 'ArrowRight':
                    if (tables.length > 0) {
                        e.preventDefault();
                        tables.forEach(table => table.scrollLeft += scrollStep);
                    }
                    break;
            }
        });

        // Ré-initialiser globalement si besoin
        window.reInitTables = initDragToScroll;

        // Gestion générique des dropdowns (id finit par -dropdown-btn)
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[id$="-dropdown-btn"]');
            if (btn) {
                const menuId = btn.id.replace('-btn', '-menu');
                const menu = document.getElementById(menuId);
                if (menu) {
                    menu.classList.toggle('hidden');
                    // Fermer les autres menus
                    document.querySelectorAll('[id$="-dropdown-menu"]').forEach(otherMenu => {
                        if (otherMenu !== menu) otherMenu.classList.add('hidden');
                    });
                }
            } else {
                // Cliquer ailleurs ferme tous les menus
                document.querySelectorAll('[id$="-dropdown-menu"]').forEach(menu => {
                    if (!menu.contains(e.target)) menu.classList.add('hidden');
                });
            }
        });
    </script>
    @yield('scripts')
</body>

</html>
