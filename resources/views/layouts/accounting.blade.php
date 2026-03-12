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
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Tom Select -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
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
                        'primary-light': '#0055aa',
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
            --text-main: #1f2937; /* gray-800 */
        }

        /* Table Responsive Wrapper - Scroll horizontal local au cadre blanc */
        .table-responsive {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: visible;
            position: relative;
            background: var(--card-bg);
            border-radius: 0.75rem;
            display: block;
            cursor: grab;
            user-select: none;
            scrollbar-width: thin;
        }

        .table-responsive:active {
            cursor: grabbing;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #0a0a0a;
                --card-bg: #161615;
                --border-color: #262624;
                --text-main: #f3f4f6; /* gray-100 */
            }
            
            .table-responsive {
                border-color: rgba(255,255,255,0.1) !important;
            }
            h1, h2, h3, h4, .text-gray-950, .text-gray-900, .text-gray-800, .text-black {
                color: var(--text-main) !important;
            }
            .text-gray-700, .text-gray-600 {
                color: #d1d5db !important; /* gray-300 */
            }
            
            .bg-white, .bg-card-bg {
                background-color: var(--card-bg) !important;
            }
            .bg-gray-50, .bg-bg {
                background-color: var(--bg) !important;
            }
            
            /* Form elements */
            select, input {
                background-color: #1c1c1b !important;
                color: #f3f4f6 !important;
                border-color: #262624 !important;
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
        
        .sidebar-transition { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        
        /* iOS Specific fixes */
        input, select, textarea {
            -webkit-appearance: none;
            appearance: none;
            box-sizing: border-box;
            font-size: 16px; /* Global fix for iOS zoom */
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
            background-color: #003366 !important;
            color: #ffffff !important;
            padding: 1.25rem 1.5rem !important;
            border: none !important;
            box-shadow: inset 0 -1px 0 rgba(255,255,255,0.1);
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
            html, body { overflow-x: hidden !important; width: 100%; position: relative; }
            .main-content {
                padding: 0 0.5rem 1rem 0.5rem !important;
                flex: 1;
                min-width: 0;
                width: 100%;
                display: block;
            }
            input, select, textarea {
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
                display: block !important; /* On gère par visibility/opacity pour la fluidité */
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
        <aside id="sidebar" class="sidebar sidebar-transition w-[260px] h-full bg-white dark:bg-[#161615] border-r border-border flex flex-shrink-0 flex-col py-3 px-4 shadow-sm z-[2000] overflow-y-auto">
            <script>
                // Appliquer la classe si nécessaire avant que l'élément soit affiché
                if (localStorage.getItem('sidebar-collapsed') === 'true' && window.innerWidth > 768) {
                    document.getElementById('sidebar').classList.add('sidebar-collapsed');
                    document.getElementById('sidebar').classList.replace('w-[260px]', 'w-[80px]');
                }
            </script>
            <!-- Toggle Button (Floating) -->
            <button id="toggle-sidebar" class="fixed left-[244px] top-10 sidebar-transition bg-accent text-white border-[3px] border-[#FDFDFC] dark:border-[#0a0a0a] w-8 h-8 rounded-full flex items-center justify-center shadow-lg hover:scale-110 hover:bg-primary transition-all z-[2001] hidden md:flex">
                <script>
                    if (localStorage.getItem('sidebar-collapsed') === 'true' && window.innerWidth > 768) {
                        document.currentScript.parentElement.style.left = '64px';
                    }
                </script>
                <i id="toggle-chevron" data-lucide="chevron-left"></i>
            </button>

            <!-- Mobile Close Button -->
            <button id="close-mobile-sidebar" class="md:hidden absolute top-4 right-4 text-text-muted dark:text-gray-300">
                <i data-lucide="x"></i>
            </button>

            <div class="flex items-center mb-8 px-2 transition-all duration-300">
                <img src="{{ asset('images/ChatGPT Image 11 mars 2026, 10_41_49.png') }}" alt="Comptafriq Logo" 
                     class="h-24 w-auto object-contain dark:filter-none filter invert dark:brightness-110">
            </div>
            
            <nav class="flex flex-col gap-1">
                <a href="{{ route('accounting.dashboard') }}" class="flex items-center gap-4 px-4 py-3 rounded-xl font-medium transition-all {{ request()->routeIs('accounting.dashboard') ? 'bg-primary text-white shadow-md' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' }}">
                    <i class="w-5 h-5" data-lucide="home"></i>
                    <span class="sidebar-label transition-all duration-300">Accueil</span>
                </a>

                <div class="sidebar-label text-[10px] uppercase font-bold text-gray-400 mt-6 px-4 mb-2 tracking-widest hidden md:block">Comptabilité Générale</div>
                <div class="md:hidden border-t border-border my-4 mx-2"></div>
                
                <a href="{{ route('accounting.journal.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-xl font-medium transition-all {{ request()->routeIs('accounting.journal.index') ? 'bg-primary text-white shadow-md' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' }}">
                    <i class="w-5 h-5" data-lucide="book-open"></i>
                    <span class="sidebar-label transition-all duration-300">Journal</span>
                </a>
                <a href="{{ route('accounting.journal.create') }}" class="flex items-center gap-4 px-4 py-3 rounded-xl font-medium transition-all {{ request()->routeIs('accounting.journal.create') ? 'bg-primary text-white shadow-md' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' }}">
                    <i class="w-5 h-5" data-lucide="edit"></i>
                    <span class="sidebar-label transition-all duration-300">Saisie</span>
                </a>
                <a href="{{ route('accounting.ledger') }}" class="flex items-center gap-4 px-4 py-3 rounded-xl font-medium transition-all {{ request()->routeIs('accounting.ledger') ? 'bg-primary text-white shadow-md' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' }}">
                    <i class="w-5 h-5" data-lucide="bar-chart-2"></i>
                    <span class="sidebar-label transition-all duration-300">Grand Livre</span>
                </a>
                <a href="{{ route('accounting.balance') }}" class="flex items-center gap-4 px-4 py-3 rounded-xl font-medium transition-all {{ request()->routeIs('accounting.balance') ? 'bg-primary text-white shadow-md' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' }}">
                    <i class="w-5 h-5" data-lucide="scale"></i>
                    <span class="sidebar-label transition-all duration-300">Balance</span>
                </a>
                <a href="{{ route('accounting.bilan') }}" class="flex items-center gap-4 px-4 py-3 rounded-xl font-medium transition-all {{ request()->routeIs('accounting.bilan') ? 'bg-primary text-white shadow-md' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' }}">
                    <i class="w-5 h-5" data-lucide="briefcase"></i>
                    <span class="sidebar-label transition-all duration-300">Bilan</span>
                </a>
                <a href="{{ route('accounting.resultat') }}" class="flex items-center gap-4 px-4 py-3 rounded-xl font-medium transition-all {{ request()->routeIs('accounting.resultat') ? 'bg-primary text-white shadow-md' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' }}">
                    <i class="w-5 h-5" data-lucide="trending-up"></i>
                    <span class="sidebar-label transition-all duration-300">Résultat</span>
                </a>

                <div class="sidebar-label text-[10px] uppercase font-bold text-gray-400 mt-6 px-4 mb-2 tracking-widest hidden md:block">Support</div>
                <a href="{{ route('accounting.help') }}" class="flex items-center gap-4 px-4 py-3 rounded-xl font-medium transition-all {{ request()->routeIs('accounting.help') ? 'bg-primary text-white shadow-md' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' }}">
                    <i class="w-5 h-5" data-lucide="help-circle"></i>
                    <span class="sidebar-label transition-all duration-300">Guide & Aide</span>
                </a>

                <form action="{{ route('logout') }}" method="POST" id="logout-form" class="hidden">
                    @csrf
                </form>
                <button type="button" onclick="document.getElementById('logout-form').submit();" class="flex items-center gap-4 px-4 py-3 rounded-xl font-medium transition-all text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 w-full text-left">
                    <i class="w-5 h-5" data-lucide="log-out"></i>
                    <span class="sidebar-label transition-all duration-300">Déconnexion</span>
                </button>
            </nav>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col h-full overflow-hidden">
            <!-- Mobile Top Bar (Opaque background to avoid bleed-through) -->
            <header class="md:hidden flex items-center justify-between px-4 bg-white dark:bg-[#161615] border-b border-border dark:border-white/10 shadow-sm flex-shrink-0 relative">
                <button id="open-mobile-sidebar" class="p-2 bg-white dark:bg-white/5 rounded-lg border border-border dark:border-white/10 z-10">
                    <i data-lucide="menu" class="w-5 h-5 dark:text-gray-300"></i>
                </button>
                
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <img src="{{ asset('images/ChatGPT Image 11 mars 2026, 10_41_49.png') }}" alt="Comptafriq Logo" 
                         class="h-20 w-auto object-contain pointer-events-auto dark:filter-none filter invert dark:brightness-110">
                </div>
                
                <div class="w-10"></div>
            </header>

            <!-- Scrollable Content Area - Enable full auto overflow for sticky headers -->
            <main class="main-content flex-1 overflow-auto p-6 md:p-10 transition-all scroll-smooth relative">
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 flex items-center gap-3">
                        <i class="w-5 h-5" data-lucide="check-circle"></i>
                        {{ session('success') }}
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
        }, { passive: true });

        sidebar.addEventListener('touchmove', e => {
            if (!isSwiping) return;
            touchCurrentX = e.touches[0].clientX;
            let deltaX = touchStartX - touchCurrentX;
            
            if (deltaX > 0) { // On pousse vers la gauche
                sidebar.style.transform = `translateX(${-deltaX}px)`;
                let progress = Math.min(deltaX / 260, 1);
                sidebarOverlay.style.opacity = (0.8 - (progress * 0.8)).toString(); // 0.8 est l'opacité max du black/80
            }
        }, { passive: true });

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
        }, { passive: true });

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
                wrapper.addEventListener('touchstart', startDragging, { passive: true });
                wrapper.addEventListener('touchend', stopDragging);
                wrapper.addEventListener('touchmove', moveDragging, { passive: false });
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
            
            switch(e.key) {
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

        // Gestion de la déconnexion - supprimée car maintenant gérée par le bouton logout-form
    </script>
    @yield('scripts')
</body>
</html>
