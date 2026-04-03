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
            <div onclick="showEnterpriseModal()" class="flex items-center gap-6 bg-white/10 backdrop-blur-md px-6 py-4 rounded-3xl border border-white/20 shadow-2xl cursor-pointer hover:bg-white/20 transition-all group">
                <div class="hidden sm:flex w-12 h-12 bg-white/20 rounded-2xl items-center justify-center text-white shrink-0 group-hover:scale-110 transition-transform">
                    <i data-lucide="building-2" class="w-6 h-6"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-300 opacity-80">Structure active (cliquez pour changer)</span>
                    <h2 class="text-lg font-black tracking-tight leading-tight">{{ $user->entreprise->name }}</h2>
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
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Bandeau Alerte : pas d'entreprise --}}
    <div class="animate-fade-up">
        <div class="bg-primary/5 dark:bg-primary/10 border border-slate-300 dark:border-primary/50 rounded-[2.5rem] p-8">
            <div class="flex flex-col lg:flex-row lg:items-center gap-8">
                <div class="flex items-start gap-6 flex-1">
                    <div class="w-16 h-16 bg-primary/10 text-primary dark:text-primary rounded-2xl flex items-center justify-center flex-shrink-0">
                        <i data-lucide="building-2" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-text-main">
                            @if(!$user->entreprise) 
                                Aucune entreprise associée 
                            @else 
                                Gérer vos structures 
                            @endif
                        </h3>
                        <p class="text-text-secondary text-sm mt-1 leading-relaxed max-w-xl">
                            Démarez votre expérience complète en associant votre compte à une structure existante ou en créant la vôtre.
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
                                   class="pl-10 pr-4 py-3 border border-slate-200 dark:border-primary/50 rounded-2xl bg-white dark:bg-[#0a0f1e] text-slate-800 dark:text-white font-mono text-sm uppercase focus:outline-none focus:border-primary transition-all w-48"
                                   oninput="this.value=this.value.toUpperCase()">
                        </div>
                        <button type="submit"
                                class="px-6 py-3 bg-primary text-white font-black rounded-2xl hover:bg-primary-light transition-all text-xs uppercase tracking-widest flex items-center gap-2 shadow-lg shadow-primary/20">
                            Rejoindre
                        </button>
                    </form>
                    <a href="{{ url('/entreprise-setup?action=create') }}"
                       class="px-6 py-3 bg-card-bg border border-border text-text-main font-black rounded-2xl hover:bg-slate-50 dark:hover:bg-white/10 transition-all text-xs uppercase tracking-widest flex items-center gap-2 text-center justify-center">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i> Créer
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Sélection Entreprise --}}
    @if($user->entreprises->count() > 0)
    <div id="enterprise-selection-modal" class="{{ session()->has('active_entreprise_id') ? 'hidden' : '' }} fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade">
        <div class="bg-card-bg border border-border rounded-[2.5rem] w-full max-w-2xl overflow-hidden shadow-2xl animate-scale-up">
            <div class="p-8 border-b border-border bg-slate-50 dark:bg-white/5">
                <h2 class="text-2xl font-black text-text-main">Sélectionnez votre espace de travail</h2>
                <p class="text-text-secondary text-sm mt-2">Choisissez l'entreprise sur laquelle vous souhaitez travailler aujourd'hui.</p>
            </div>
            
            <div class="p-8 max-h-[60vh] overflow-y-auto">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($user->entreprises as $entreprise)
                    <form action="{{ route('accounting.entreprise.switch') }}" method="POST">
                        @csrf
                        <input type="hidden" name="entreprise_id" value="{{ $entreprise->id }}">
                        <button type="submit" class="w-full group text-left p-6 rounded-3xl border {{ ($user->entreprise && $user->entreprise->id == $entreprise->id) ? 'border-primary bg-primary/5' : 'border-border' }} hover:border-primary hover:bg-primary/5 transition-all duration-300 flex items-start gap-4">
                            <div class="w-12 h-12 {{ ($user->entreprise && $user->entreprise->id == $entreprise->id) ? 'bg-primary text-white' : 'bg-primary/10 text-primary' }} rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i data-lucide="building-2" class="w-6 h-6"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-black text-text-main group-hover:text-primary transition-colors">{{ $entreprise->name }}</h4>
                                <p class="text-xs text-text-secondary mt-1 uppercase font-mono tracking-tighter opacity-70">Code: {{ $entreprise->code }}</p>
                                <span class="inline-block mt-2 px-2 py-1 bg-slate-100 dark:bg-white/10 rounded text-[10px] font-bold uppercase text-slate-500">
                                    {{ $entreprise->pivot->role }}
                                </span>
                            </div>
                        </button>
                    </form>
                    @endforeach
                </div>
            </div>

            <div class="p-8 bg-slate-50 dark:bg-white/5 border-t border-border flex justify-end">
                <button onclick="document.getElementById('enterprise-selection-modal').classList.add('hidden')" class="text-text-secondary text-xs uppercase font-black hover:text-text-main transition-colors">
                    Fermer
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Liste des entreprises de l'utilisateur (pour switcher rapidement) --}}
    @if($user->entreprises->count() > 1)
    <div>
        <p class="text-xs uppercase font-bold tracking-widest text-text-secondary mb-4">Vos autres structures</p>
        <div class="flex flex-wrap gap-4">
            @foreach($user->entreprises as $entreprise)
                @if($entreprise->id != ($user->entreprise->id ?? 0))
                <form action="{{ route('accounting.entreprise.switch') }}" method="POST">
                    @csrf
                    <input type="hidden" name="entreprise_id" value="{{ $entreprise->id }}">
                    <button type="submit" class="flex items-center gap-3 px-5 py-3 bg-card-bg border border-border rounded-2xl hover:border-primary hover:text-primary transition-all shadow-sm">
                        <i data-lucide="building-2" class="w-4 h-4"></i>
                        <span class="text-sm font-bold">{{ $entreprise->name }}</span>
                    </button>
                </form>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- Modules --}}
    <div>
        <p class="text-xs uppercase font-bold tracking-widest text-text-secondary mb-4">Modules disponibles</p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <a href="{{ route('accounting.journal.index') }}" class="group relative bg-card-bg border border-border rounded-3xl p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <i data-lucide="book-open" class="w-32 h-32 -mr-8 -mt-8 rotate-12"></i>
                </div>
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-primary/10 text-primary rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i data-lucide="book-open" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-text-main mb-2">Journal</h3>
                    <p class="text-sm text-text-secondary leading-relaxed mb-6">Saisie et historique complet des écritures comptables.</p>
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
                    <p class="text-sm text-slate-700 leading-relaxed mb-6">Consultation par compte avec soldes progressifs.</p>
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
                    <p class="text-sm text-slate-700 leading-relaxed mb-6">Vérification de l'équilibre comptable par classe.</p>
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
                    <p class="text-sm text-slate-700 leading-relaxed mb-6">Actif, Passif et situation patrimoniale de l'entreprise.</p>
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
                    <p class="text-sm text-slate-700 leading-relaxed mb-6">Compte de résultat, charges et produits de la période.</p>
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
                    <p class="text-sm text-slate-700 leading-relaxed mb-6">Documentation et assistance pour utiliser Comptafriq.</p>
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
<style>
@keyframes fade {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes scale-up {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.animate-fade {
    animation: fade 0.3s ease-out forwards;
}
.animate-scale-up {
    animation: scale-up 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
<script>
lucide.createIcons();

function showEnterpriseModal() {
    const modal = document.getElementById('enterprise-selection-modal');
    if (modal) {
        modal.classList.remove('hidden');
    } else {
        // Si le modal n'est pas dans le DOM (pas généré car active_id présent), 
        // on pourrait le charger ou juste utiliser la liste "vos autres structures"
    }
}

function copyToClipboard(text, btnOrId) {
    let btn = (typeof btnOrId === 'string') ? document.getElementById(btnOrId) : btnOrId;
    if (!btn || btn.classList.contains('copying')) return;

    const icon = btn.querySelector('i');
    if (icon && !icon.hasAttribute('data-original')) {
        icon.setAttribute('data-original', icon.getAttribute('data-lucide') || 'copy');
    }

    const showSuccess = () => {
        btn.classList.add('copying', 'scale-110');
        if (icon) {
            icon.setAttribute('data-lucide', 'check');
            if(typeof lucide !== 'undefined') lucide.createIcons();
        }
        
        setTimeout(() => {
            if (icon) {
                icon.setAttribute('data-lucide', icon.getAttribute('data-original'));
                if(typeof lucide !== 'undefined') lucide.createIcons();
            }
            btn.classList.remove('copying', 'scale-110');
        }, 1500);
    };

    const fallbackCopy = (t) => {
        const textArea = document.createElement("textarea");
        textArea.value = t;
        textArea.style.position = "fixed";
        textArea.style.left = "-9999px";
        textArea.style.top = "0";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            const successful = document.execCommand('copy');
            if (successful) showSuccess();
        } catch (err) {
            console.error('Fallback copy fail', err);
        }
        document.body.removeChild(textArea);
    };

    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(showSuccess, () => fallbackCopy(text));
    } else {
        fallbackCopy(text);
    }
}
</script>
@endsection
