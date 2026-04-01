@extends('layouts.accounting')

@section('title', 'Dashboard')

@section('content')
<div class="flex flex-col gap-8">

    {{-- Hero Header --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-primary to-primary-light p-8 text-white shadow-xl" style="border-radius: 1.5rem !important;">
        <div class="absolute inset-0 opacity-10 pointer-events-none">
            <svg viewBox="0 0 400 200" class="w-full h-full"><circle cx="350" cy="50" r="120" fill="white"/><circle cx="50" cy="180" r="80" fill="white"/></svg>
        </div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-300 mb-1">Tableau de bord</p>
                <h1 class="text-3xl font-black">Bienvenue, {{ $user->name }} 👋</h1>
                <p class="text-slate-300 mt-1 text-sm">
                    @if($user->role == 'admin') 🔑 Administateur @elseif($user->role == 'comptable') 📊 Comptable @else 👤 Utilisateur @endif
                </p>
            </div>
            @if($user->entreprise)
            <div class="px-2 py-1 text-center min-w-[180px]">
                <p class="text-[10px] uppercase font-bold tracking-widest text-slate-300 mb-0.5">Entreprise</p>
                <p class="font-bold text-white">{{ $user->entreprise->name }}</p>
                <p class="text-[10px] text-blue-300 mt-0.5 font-mono">ID: {{ $user->entreprise->code }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Bandeau Alerte : pas d'entreprise --}}
    @if(!$user->entreprise)
    <div class="animate-fade-up">
        <div class="bg-primary/5 dark:bg-primary/10 border border-slate-300 dark:border-blue-900/50 rounded-[2.5rem] p-8">
            <div class="flex flex-col lg:flex-row lg:items-center gap-8">
                <div class="flex items-start gap-6 flex-1">
                    <div class="w-16 h-16 bg-primary/10 text-primary dark:text-primary rounded-2xl flex items-center justify-center flex-shrink-0 animate-float">
                        <i data-lucide="building-2" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white">Aucune entreprise associée</h3>
                        <p class="text-slate-500 dark:text-slate-300/60 text-sm mt-1 leading-relaxed max-w-xl">
                            Démarez votre expérience complète en associant votre compte à une structure existante ou en créant la vôtre dès maintenant.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 flex-shrink-0">
                    <form action="{{ route('accounting.entreprise.join') }}" method="POST" class="flex gap-2">
                        @csrf
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-primary/50 group-focus-within:text-primary transition-colors">
                                <i data-lucide="hash" class="w-4 h-4"></i>
                            </span>
                            <input type="text" name="code" placeholder="CODE..." required
                                   class="pl-10 pr-4 py-3 border border-slate-200 dark:border-blue-900/50 rounded-2xl bg-white dark:bg-[#0a0f1e] text-slate-800 dark:text-white font-mono text-sm uppercase focus:outline-none focus:border-primary transition-all w-48"
                                   oninput="this.value=this.value.toUpperCase()">
                        </div>
                        <button type="submit"
                                class="px-6 py-3 bg-primary text-white font-black rounded-2xl hover:bg-primary-light transition-all text-xs uppercase tracking-widest flex items-center gap-2 shadow-lg shadow-primary/20">
                            Rejoindre
                        </button>
                    </form>
                    <a href="{{ url('/entreprise-setup?action=create') }}"
                       class="px-6 py-3 bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white font-black rounded-2xl hover:bg-slate-50 dark:hover:bg-white/10 transition-all text-xs uppercase tracking-widest flex items-center gap-2 text-center justify-center">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i> Créer
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Modules --}}
    <div>
        <p class="text-xs uppercase font-bold tracking-widest text-slate-400 mb-4">Modules disponibles</p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <a href="{{ route('accounting.journal.index') }}" class="group relative bg-white dark:bg-[#161615] border border-border rounded-3xl p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <i data-lucide="book-open" class="w-32 h-32 -mr-8 -mt-8 rotate-12"></i>
                </div>
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-primary/10 text-primary rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i data-lucide="book-open" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Journal</h3>
                    <p class="text-sm text-slate-500 leading-relaxed mb-6">Saisie et historique complet des écritures comptables.</p>
                    <div class="flex items-center text-primary font-bold text-sm gap-2 uppercase tracking-widest">
                        Accéder <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

            <a href="{{ route('accounting.ledger') }}" class="group relative bg-white dark:bg-[#161615] border border-border rounded-3xl p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <i data-lucide="bar-chart-2" class="w-32 h-32 -mr-8 -mt-8 rotate-12"></i>
                </div>
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-indigo-500/10 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i data-lucide="bar-chart-2" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Grand Livre</h3>
                    <p class="text-sm text-slate-500 leading-relaxed mb-6">Consultation par compte avec soldes progressifs.</p>
                    <div class="flex items-center text-indigo-600 font-bold text-sm gap-2 uppercase tracking-widest">
                        Accéder <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

            <a href="{{ route('accounting.balance') }}" class="group relative bg-white dark:bg-[#161615] border border-border rounded-3xl p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <i data-lucide="scale" class="w-32 h-32 -mr-8 -mt-8 rotate-12"></i>
                </div>
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-green-500/10 text-green-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i data-lucide="scale" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Balance</h3>
                    <p class="text-sm text-slate-500 leading-relaxed mb-6">Vérification de l'équilibre comptable par classe.</p>
                    <div class="flex items-center text-green-600 font-bold text-sm gap-2 uppercase tracking-widest">
                        Accéder <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

            <a href="{{ route('accounting.bilan') }}" class="group relative bg-white dark:bg-[#161615] border border-border rounded-3xl p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-primary/10 text-primary rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i data-lucide="briefcase" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Bilan</h3>
                    <p class="text-sm text-slate-500 leading-relaxed mb-6">Actif, Passif et situation patrimoniale de l'entreprise.</p>
                    <div class="flex items-center text-primary font-bold text-sm gap-2 uppercase tracking-widest">
                        Accéder <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

            <a href="{{ route('accounting.resultat') }}" class="group relative bg-white dark:bg-[#161615] border border-border rounded-3xl p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-emerald-500/10 text-emerald-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i data-lucide="trending-up" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Résultat</h3>
                    <p class="text-sm text-slate-500 leading-relaxed mb-6">Compte de résultat, charges et produits de la période.</p>
                    <div class="flex items-center text-emerald-600 font-bold text-sm gap-2 uppercase tracking-widest">
                        Accéder <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

            <a href="{{ route('accounting.journals-settings.index') }}" class="group relative bg-[#004A99] rounded-3xl p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden border-none text-white">
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-white/20 text-white rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i data-lucide="settings" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Paramétrage Journaux</h3>
                    <p class="text-sm text-white/70 leading-relaxed mb-6">Ajouter, modifier ou supprimer vos journaux comptables.</p>
                    <div class="flex items-center text-white font-bold text-sm gap-2 uppercase tracking-widest">
                        Gérer <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

            <a href="{{ route('accounting.help') }}" class="group relative bg-white dark:bg-[#161615] border border-border rounded-3xl p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-slate-500/10 text-slate-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i data-lucide="help-circle" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Guide & Aide</h3>
                    <p class="text-sm text-slate-500 leading-relaxed mb-6">Documentation et assistance pour utiliser Comptafriq.</p>
                    <div class="flex items-center text-slate-500 font-bold text-sm gap-2 uppercase tracking-widest">
                        Consulter <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
lucide.createIcons();
</script>
@endsection
