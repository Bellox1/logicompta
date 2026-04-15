<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | {{ config('app.name', 'COMPTAFIQ') }}</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <!-- Bootstrap v4.4.1 CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <!-- Font Awesome 6.4.0 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <!-- Tom Select -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --primary-color: #0062cc;
            --secondary-color: #1a1c2e;
            --orange-color: #ff750f;
            --dark-color: #1a1c2e;
            --sidebar-bg: #fff;
            --main-bg: #f8f9fc;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--main-bg);
            color: #505050;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .font-headline {
            font-family: 'Manrope', sans-serif;
            font-weight: 700;
            color: var(--dark-color);
        }

        /* Layout Structure */
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }

        #sidebar {
            min-width: 280px;
            max-width: 280px;
            background: var(--sidebar-bg);
            color: var(--dark-color);
            transition: all 0.3s;
            border-right: 1px solid #eee;
            height: 100vh;
            position: fixed;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        #sidebar.active {
            margin-left: -280px !important;
        }

        #content {
            width: calc(100% - 280px);
            margin-left: 280px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        #content.active {
            width: 100%;
            margin-left: 0;
        }

        /* Sidebar Elements */
        .sidebar-header {
            padding: 15px 20px;
            border-bottom: 1px solid #f8f9fa;
            position: relative;
            min-height: 80px;
            display: flex;
            align-items: center;
        }

        .close-sidebar {
            display: none;
            position: absolute;
            right: 15px;
            top: 25px;
            background: none;
            border: none;
            color: var(--dark-color);
            font-size: 24px;
        }

        ul.components {
            padding: 10px 0;
            flex-grow: 1;
            overflow-y: auto;
        }

        ul li a {
            padding: 7px 20px;
            font-size: 12.5px;
            display: block;
            color: #64748b;
            font-weight: 600;
            text-decoration: none !important;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        ul li a:hover,
        ul li.active>a {
            color: var(--primary-color);
            background: rgba(0, 98, 204, 0.05);
            border-left: 3px solid var(--primary-color);
        }

        .menu-label {
            padding: 6px 20px;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #94a3b8;
            font-weight: 800;
            margin-top: 10px;
        }

        /* Header */
        .main-header {
            background: #fff;
            padding: 15px 30px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        /* Alerts & Progress */
        .alert-box {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .alert-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            width: 100%;
        }

        /* Tables Premium Style */
        .table-responsive {
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }

        /* Overlay mobile */
        #sidebar-overlay {
            display: none;
            position: fixed;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(2px);
            z-index: 1001;
            top: 0;
            left: 0;
        }

        @media (max-width: 992px) {
            #sidebar {
                margin-left: -280px;
                z-index: 1002;
            }

            #sidebar.active {
                margin-left: 0 !important;
            }

            #sidebar.active + #sidebar-overlay {
                display: block;
            }

            #content {
                width: 100%;
                margin-left: 0;
            }

            .close-sidebar {
                display: block;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <nav id="sidebar">
            <div class="sidebar-header d-flex justify-content-between align-items-center flex-nowrap">
                <img src="{{ asset('storage/images/logo.png') }}" alt="Logo"
                    style="max-width: 160px; max-height: 60px; object-fit: contain; flex-shrink: 1;">
                <button
                    class="close-sidebar btn btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                    onclick="toggleSidebar()"
                    style="width: 30px; height: 30px; padding: 0; border: 1px solid #eee; cursor: pointer; z-index: 2000; position: relative; margin-left: 10px; flex-shrink: 0;">
                    <i class="fas fa-chevron-left text-muted" style="font-size: 11px;"></i>
                </button>
            </div>

            <ul class="list-unstyled components">
                <div class="menu-label">Base</div>
                <li class="{{ request()->routeIs('accounting.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('accounting.dashboard') }}">
                        <i class="fas fa-chart-line mr-2"></i> Tableau de bord
                    </a>
                </li>
                <li class="{{ request()->routeIs('accounting.journal.index') ? 'active' : '' }}">
                    <a href="{{ route('accounting.journal.index') }}">
                        <i class="fas fa-book mr-2"></i> Journal
                    </a>
                </li>
                <li class="{{ request()->routeIs('accounting.journal.create') ? 'active' : '' }}">
                    <a href="{{ route('accounting.journal.create') }}">
                        <i class="fas fa-plus-circle mr-2"></i> Saisie
                    </a>
                </li>

                <div class="menu-label">Pilotage</div>
                <li class="{{ request()->routeIs('accounting.ledger') ? 'active' : '' }}">
                    <a href="{{ route('accounting.ledger') }}">
                        <i class="fas fa-list-ul mr-2"></i> Grand Livre
                    </a>
                </li>
                <li class="{{ request()->routeIs('accounting.balance') ? 'active' : '' }}">
                    <a href="{{ route('accounting.balance') }}">
                        <i class="fas fa-layer-group mr-2"></i> Balance
                    </a>
                </li>
                <li class="{{ request()->routeIs('accounting.bilan') ? 'active' : '' }}">
                    <a href="{{ route('accounting.bilan') }}">
                        <i class="fas fa-file-invoice-dollar mr-2"></i> Bilan
                    </a>
                </li>
                <li class="{{ request()->routeIs('accounting.resultat') ? 'active' : '' }}">
                    <a href="{{ route('accounting.resultat') }}">
                        <i class="fas fa-chart-bar mr-2"></i> Résultat
                    </a>
                </li>
                <li class="{{ request()->routeIs('accounting.archive.index') ? 'active' : '' }}">
                    <a href="{{ route('accounting.archive.index') }}">
                        <i class="fas fa-archive mr-2"></i> Archives
                    </a>
                </li>

                <div class="menu-label">Paramètres</div>
                <li class="{{ request()->routeIs('accounting.account.index') ? 'active' : '' }}">
                    <a href="{{ route('accounting.account.index') }}">
                        <i class="fas fa-clipboard-list mr-2"></i> Plan Comptable
                    </a>
                </li>
                <li class="{{ request()->routeIs('accounting.journals-settings.index') ? 'active' : '' }}">
                    <a href="{{ route('accounting.journals-settings.index') }}">
                        <i class="fas fa-bookmark mr-2"></i> Gestion des Journaux
                    </a>
                </li>

                <div class="menu-label">Support</div>
                <li class="{{ request()->routeIs('accounting.help') ? 'active' : '' }}">
                    <a href="{{ route('accounting.help') }}">
                        <i class="fas fa-question-circle mr-2"></i> Guide & Aide
                    </a>
                </li>
            </ul>
@if (auth()->check() && auth()->user()->entreprise)
                    <div class="px-4 py-2 small border-top bg-light">
                        <div class="text-muted uppercase font-weight-bold" style="font-size: 9px; letter-spacing: 1px;">
                            Entreprise</div>
                        <div class="text-dark font-weight-bold truncate" style="font-size: 13px;">
                            {{ auth()->user()->entreprise->name }}</div>
                    </div>
                @endif

                <div id="system-date-container" class="px-4 py-3 border-top d-flex align-items-center bg-white">
                    <i class="far fa-clock text-muted mr-2" style="font-size: 16px;"></i>
                    <div id="system-datetime-display" class="font-weight-bold text-dark" style="font-size: 12px;">
                        --/--/---- --:--:--</div>
                </div>

                <form action="{{ route('logout') }}" method="POST" id="logout-form" class="d-none">@csrf</form>
                <button type="button"
                    class="btn btn-link btn-block text-danger font-weight-bold text-left px-4 py-3 border-top"
                    onclick="confirmLogout()" style="text-decoration: none;">
                    <i class="fas fa-sign-out-alt mr-2" style="font-size: 16px;"></i> Déconnexion
                </button>
            </div>
        </nav>
        <div id="sidebar-overlay" onclick="toggleSidebar()"></div>

        <div id="content">
            <header class="main-header">
                <div class="d-flex align-items-center">
                    <button class="btn btn-light mr-3" onclick="toggleSidebar()" type="button">
                        <i class="fas fa-bars" style="font-size: 14px;"></i>
                    </button>
                    <h2 class="h5 m-0 font-headline">@yield('title')</h2>
                </div>
                <div class="header-right">
                    <!-- Notifications retirées à la demande de l'utilisateur -->
                </div>
            </header>

            <div class="p-3 p-md-4">
                {{-- Alert Management for Success --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4 p-3 d-flex align-items-center animate-fade-up shadow-sm border-0" role="alert">
                        <i class="fas fa-check-circle mr-3" style="font-size: 20px;"></i>
                        <span class="flex-grow-1 font-weight-bold">{{ session('success') }}</span>
                        <button type="button" class="close ml-3" data-dismiss="alert" aria-label="Close" style="outline: none;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                {{-- Alert Management for Errors/List --}}
                @if (session('error') || session('error_list'))
                    <div class="alert alert-danger alert-dismissible fade show mb-4 p-3 animate-fade-up shadow-sm border-0" role="alert">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-exclamation-circle mr-3" style="font-size: 20px;"></i>
                            <span class="font-weight-bold flex-grow-1">{{ session('error') ?? 'Erreur détectée' }}</span>
                            <button type="button" class="close ml-3" data-dismiss="alert" aria-label="Close" style="outline: none;">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        @if (session('error_list'))
                            <ul class="mb-0 ml-4 small font-weight-bold">
                                @foreach (session('error_list') as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <script>
        // Sidebar Persistence Logic
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');
            const sidebarState = localStorage.getItem('sidebarState');
            
            // On PC, if state is 'closed', apply active class
            if (window.innerWidth > 992 && sidebarState === 'closed') {
                sidebar.classList.add('active');
                content.classList.add('active');
            }
            // On Mobile, if state is 'open', apply active class
            if (window.innerWidth <= 992 && sidebarState === 'open') {
                sidebar.classList.add('active');
            }

            // Auto-close on link click (to ensure next page loads closed)
            document.querySelectorAll('#sidebar li a').forEach(link => {
                link.addEventListener('click', () => {
                    localStorage.setItem('sidebarState', 'closed');
                });
            });
        });

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');
            
            sidebar.classList.toggle('active');
            content.classList.toggle('active');
            
            // Save state
            const isActive = sidebar.classList.contains('active');
            if (window.innerWidth > 992) {
                localStorage.setItem('sidebarState', isActive ? 'closed' : 'open');
            } else {
                localStorage.setItem('sidebarState', isActive ? 'open' : 'closed');
            }
        }

        function confirmLogout() {
            Swal.fire({
                title: 'Déconnexion',
                text: 'Voulez-vous vraiment vous déconnecter ?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#005b82',
                confirmButtonText: 'OUI',
                cancelButtonText: 'ANNULER'
            }).then((result) => {
                if (result.isConfirmed) document.getElementById('logout-form').submit();
            });
        }

        // High Precision Real-time Clock (DB Synced)
        let serverTimeOffset = 0;
        const fetchSystemDate = async () => {
            try {
                const response = await fetch('{{ route('accounting.system-date') }}');
                const data = await response.json();
                serverTimeOffset = new Date(data.datetime).getTime() - Date.now();
                updateClockDisplay();
            } catch (e) {
                console.error('Erreur horloge', e);
            }
        };
        const updateClockDisplay = () => {
            const display = document.getElementById('system-datetime-display');
            if (!display) return;
            const now = new Date(Date.now() + serverTimeOffset);
            display.innerText =
                `${String(now.getDate()).padStart(2,'0')}/${String(now.getMonth()+1).padStart(2,'0')}/${now.getFullYear()} ${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}:${String(now.getSeconds()).padStart(2,'0')}`;
        };
        fetchSystemDate();
        setInterval(updateClockDisplay, 1000);

        // Alert auto-hide with progress
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            document.querySelectorAll('.alert-box').forEach(alert => {
                const progress = alert.querySelector('.alert-progress');
                if (progress) {
                    progress.style.transition = 'width 3s linear';
                    setTimeout(() => progress.style.width = '0%', 50);
                    setTimeout(() => {
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 500);
                    }, 3000);
                }
            });
        });
    </script>
    @yield('scripts')
</body>

</html>
