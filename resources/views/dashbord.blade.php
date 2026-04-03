@extends('layouts.accounting')

@section('title', 'Dashboard')

@section('content')
<div class="flex flex-col gap-8 text-black">

    {{-- Quick Actions Top Bar --}}
    <div class="flex justify-end">
        <a href="{{ route('profile') }}" class="bg-white text-primary font-black px-6 py-3 rounded-[1rem] flex items-center gap-3 hover:scale-105 active:scale-95 transition-all shadow-sm border border-border shrink-0">
            <i data-lucide="user" class="w-4 h-4"></i>
            <span class="uppercase tracking-widest text-xs">Mon Profil</span>
        </a>
    </div>

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
            <div class="flex items-center gap-4">
                @if($user->entreprise)
                <div onclick="showEnterpriseModal()" class="flex items-center gap-6 bg-white/10 backdrop-blur-md px-6 py-4 rounded-3xl border border-white/20 shadow-2xl cursor-pointer hover:bg-white/20 transition-all group shrink-0">
                    <div class="hidden sm:flex w-12 h-12 bg-white/20 rounded-2xl items-center justify-center text-white shrink-0 group-hover:scale-110 transition-transform">
                        <i data-lucide="building-2" class="w-6 h-6"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-300 opacity-80">Structure active (cliquez pour changer)</span>
                        <h2 class="text-lg font-black tracking-tight leading-tight">{{ $user->entreprise->name }}</h2>
                        @if($user->role == 'admin')
                        <div class="flex items-center gap-3 mt-1">
                            <code id="company-code" 
                                   onclick="event.stopPropagation(); copyToClipboard('{{ $user->entreprise->code }}', 'copy-btn-{{ $user->entreprise->id }}')"
                                   class="text-[11px] font-mono bg-black/20 px-2 py-0.5 rounded border border-white/10 text-white leading-none cursor-pointer hover:bg-black/40 transition-colors">
                                Code/ID: {{ $user->entreprise->code }}
                            </code>
                            <button id="copy-btn-{{ $user->entreprise->id }}"
                                    onclick="event.stopPropagation(); copyToClipboard('{{ $user->entreprise->code }}', this)" 
                                    class="p-1 hover:bg-white/20 rounded transition-all text-slate-300 hover:text-white" 
                                    title="Copier le code">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modules Grid --}}
    <div>
        <p class="text-xs uppercase font-bold tracking-widest text-text-secondary mb-6">Gestion & Pilotage</p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

            {{-- 1. Paramétrage Journaux (Priorité Haute) --}}
            <a href="{{ route('accounting.journals-settings.index') }}" class="group relative bg-[#004A99] rounded-[2rem] p-8 shadow-sm hover:shadow-2xl hover:-translate-y-1 transition-all duration-500 overflow-hidden border-none text-white lg:col-span-1">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <i data-lucide="settings-2" class="w-32 h-32 -mr-8 -mt-8 rotate-12"></i>
                </div>
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-white/20 text-white rounded-2xl flex items-center justify-center mb-6 group-hover:rotate-12 transition-transform">
                        <i data-lucide="settings" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-black mb-2 uppercase tracking-tight">Paramètres Généraux</h3>
                    <p class="text-sm text-white/80 leading-relaxed mb-6 font-medium">Configurez vos journaux et la structure comptable de votre entreprise.</p>
                    <div class="flex items-center text-white font-black text-[10px] gap-2 uppercase tracking-[0.2em]">
                        Gérer la structure <i data-lucide="chevron-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

            {{-- 2. Boîte Noire (Audit - Pour Admins) --}}
            @if(Auth::user()->role === 'admin')
            <a href="{{ route('accounting.traceabilite.index') }}" class="group relative bg-rose-500 rounded-[2rem] p-8 shadow-sm hover:shadow-2xl hover:-translate-y-1 transition-all duration-500 overflow-hidden border-none text-white">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <i data-lucide="shield-alert" class="w-32 h-32 -mr-8 -mt-8 rotate-12"></i>
                </div>
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-white/20 text-white rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i data-lucide="shield-check" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-black mb-2 uppercase tracking-tight italic">Boîte Noire</h3>
                    <p class="text-sm text-white/80 leading-relaxed mb-6 font-medium">Traçabilité totale et récupération des données effacées par erreur.</p>
                    <div class="flex items-center text-white font-black text-[10px] gap-2 uppercase tracking-[0.2em]">
                        Ouvrir la sécurité <i data-lucide="terminal" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>
            @endif

            {{-- 3. Journal --}}
            <a href="{{ route('accounting.journal.index') }}" class="group relative bg-card-bg border border-border rounded-[2rem] p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-primary/10 text-primary rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i data-lucide="book-open" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-text-main mb-2">Journal de Saisie</h3>
                    <p class="text-sm text-text-secondary leading-relaxed mb-6">Enregistrez et consultez vos écritures au quotidien.</p>
                    <div class="flex items-center text-primary font-bold text-sm gap-2 uppercase tracking-widest">
                        Accéder <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

            {{-- 4. Grand Livre --}}
            <a href="{{ route('accounting.ledger') }}" class="group relative bg-white dark:bg-[#161615] border border-border rounded-[2rem] p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-indigo-500/10 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i data-lucide="bar-chart-2" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Grand Livre</h3>
                    <p class="text-sm text-slate-700 leading-relaxed mb-6">Détails des comptes et historiques par numéro.</p>
                    <div class="flex items-center text-indigo-600 font-bold text-sm gap-2 uppercase tracking-widest">
                        Accéder <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

            {{-- 5. Balance --}}
            <a href="{{ route('accounting.balance') }}" class="group relative bg-white dark:bg-[#161615] border border-border rounded-[2rem] p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-emerald-500/10 text-emerald-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i data-lucide="scale" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Balance des Comptes</h3>
                    <p class="text-sm text-slate-700 leading-relaxed mb-6">Vérifiez l'équilibre de votre comptabilité.</p>
                    <div class="flex items-center text-emerald-600 font-bold text-sm gap-2 uppercase tracking-widest">
                        Accéder <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

            {{-- 6. Bilan & Résultats --}}
            <a href="{{ route('accounting.bilan') }}" class="group relative bg-slate-900 text-white rounded-[2rem] p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden border-none col-span-1 md:col-span-2 lg:col-span-1">
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-white/10 text-white rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i data-lucide="file-text" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">États Financiers</h3>
                    <p class="text-sm text-slate-400 leading-relaxed mb-6">Consultez votre Bilan et votre Compte de Résultat.</p>
                    <div class="flex items-center text-white font-bold text-sm gap-2 uppercase tracking-widest">
                        Consulter <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

        </div>
    </div>

    {{-- Bandeau Alerte : pas d'entreprise --}}
    @if(!$user->entreprise)
    <div class="animate-fade-up">
        <div class="bg-primary/5 dark:bg-primary/10 border border-slate-300 dark:border-primary/50 rounded-[2.5rem] p-8">
            <div class="flex flex-col lg:flex-row lg:items-center gap-8">
                <div class="flex items-start gap-6 flex-1">
                    <div class="w-16 h-16 bg-primary/10 text-primary rounded-2xl flex items-center justify-center flex-shrink-0">
                        <i data-lucide="alert-circle" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-text-main">Aucune entreprise associée</h3>
                        <p class="text-text-secondary text-sm mt-1 leading-relaxed max-w-xl">
                            Démarez votre expérience complète en associant votre compte à une structure existante ou en créant la vôtre.
                        </p>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 flex-shrink-0">
                    <form action="{{ route('accounting.entreprise.join') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="code" placeholder="CODE..." required class="pl-4 pr-4 py-3 border border-slate-200 rounded-2xl bg-white text-sm uppercase focus:outline-none focus:border-primary transition-all w-32 font-bold">
                        <button type="submit" class="px-6 py-3 bg-primary text-white font-black rounded-2xl text-xs uppercase tracking-widest">Rejoindre</button>
                    </form>
                    <a href="{{ url('/entreprise-setup?action=create') }}" class="px-6 py-3 bg-white border border-border text-text-main font-black rounded-2xl text-xs uppercase tracking-widest text-center">Créer</a>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

{{-- Scripts et Modals inchangés --}}
@if($user->entreprises->count() > 0)
<div id="enterprise-selection-modal" class="{{ session()->has('active_entreprise_id') ? 'hidden' : '' }} fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="bg-card-bg border border-border rounded-[2.5rem] w-full max-w-2xl overflow-hidden shadow-2xl">
        <div class="p-8 border-b border-border bg-slate-50">
            <h2 class="text-2xl font-black text-text-main italic uppercase tracking-tighter">SÉLECTIONNEZ VOTRE ESPACE</h2>
        </div>
        <div class="p-8 max-h-[60vh] overflow-y-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($user->entreprises as $entreprise)
                <form action="{{ route('accounting.entreprise.switch') }}" method="POST">
                    @csrf
                    <input type="hidden" name="entreprise_id" value="{{ $entreprise->id }}">
                    <button type="submit" class="w-full group text-left p-6 rounded-3xl border {{ ($user->entreprise && $user->entreprise->id == $entreprise->id) ? 'border-primary bg-primary/5' : 'border-border' }} hover:border-primary transition-all flex items-start gap-4">
                        <div class="w-12 h-12 bg-primary/10 text-primary rounded-2xl flex items-center justify-center">
                            <i data-lucide="building-2" class="w-6 h-6"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-black text-text-main uppercase text-sm italic">{{ $entreprise->name }}</h4>
                            @if($user->role == 'admin')
                            <p class="text-xs text-text-secondary mt-1 uppercase font-mono tracking-tighter opacity-70">Code: {{ $entreprise->code }}</p>
                            @endif
                            <span class="inline-block mt-2 px-2 py-1 bg-slate-100 rounded text-[10px] font-bold uppercase text-slate-500">{{ $entreprise->pivot->role }}</span>
                        </div>
                    </button>
                </form>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
    lucide.createIcons();
    function showEnterpriseModal() { document.getElementById('enterprise-selection-modal').classList.remove('hidden'); }
    function copyToClipboard(text, btnOrId) { 
        navigator.clipboard.writeText(text); 
        alert('Code copié !');
    }
</script>
@endsection
